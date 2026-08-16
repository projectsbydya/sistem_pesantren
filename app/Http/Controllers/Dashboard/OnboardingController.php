<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\Ustadz;
use App\Models\UstadzKelas;
use App\Services\OnboardingStepRegistry;
use App\Services\TenantService;
use App\Services\TenantSetupService;
use App\Services\UserProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OnboardingController extends Controller
{
    protected UserProvisioningService $provisioningService;

    public function __construct(UserProvisioningService $provisioningService)
    {
        $this->provisioningService = $provisioningService;
    }

    /**
     * Halaman welcome onboarding
     */
    public function welcome()
    {
        $tenant = Tenant::findOrFail(tenant_id());
        $progress = TenantSetupService::getProgress();

        // Jika sudah siap operasional, redirect ke dashboard
        if ($progress->setup_status === 'siap_operasional') {
            return redirect()->route('dashboard.index');
        }

        return view('dashboard.onboarding.welcome', [
            'tenant' => $tenant,
            'progress' => $progress,
        ]);
    }

    /**
     * Halaman antrian setup program (multi-program flow)
     */
    public function programSetupQueue()
    {
        $tenant = Tenant::findOrFail(tenant_id());
        $progress = TenantSetupService::getProgress();
        $tenantPrograms = TenantSetupService::getTenantPrograms();

        if ($tenantPrograms->isEmpty()) {
            return redirect()->route('dashboard.onboarding.programs')
                ->with('warning', 'Pilih program terlebih dahulu sebelum melanjutkan setup.');
        }

        // Get setup progress for each program
        $setupProgress = [];
        $allProgramsCompleted = true;
        
        foreach ($tenantPrograms as $tenantProgram) {
            $programId = $tenantProgram->program_id;
            $programProgress = $this->getProgramSetupProgress($programId);
            $setupProgress[$programId] = $programProgress;
            
            if (!$programProgress['is_complete']) {
                $allProgramsCompleted = false;
            }
        }

        return view('dashboard.onboarding.program-setup-queue', [
            'tenant' => $tenant,
            'progress' => $progress,
            'tenantPrograms' => $tenantPrograms,
            'setupProgress' => $setupProgress,
            'allProgramsCompleted' => $allProgramsCompleted,
        ]);
    }

    /**
     * Mulai setup untuk program tertentu
     */
    public function startProgramSetup(int $programId)
    {
        $tenantProgram = \App\Models\TenantProgram::where('tenant_id', tenant_id())
            ->where('program_id', $programId)
            ->where('is_active', true)
            ->with('program')
            ->firstOrFail();

        // Store current program in session
        session(['onboarding_program_id' => $programId]);

        return redirect()->route('dashboard.onboarding.wizard', ['step' => 'kelas'])
            ->with('success', 'Memulai setup untuk program: ' . $tenantProgram->program->name);
    }

    /**
     * Get setup progress for a specific program.
     * Delegates to TenantSetupService::getProgramProgress() — the single
     * source of truth also used by the dashboard's per-program cards.
     */
    private function getProgramSetupProgress(int $programId): array
    {
        return TenantSetupService::getProgramProgress($programId, tenant_id());
    }

    /**
     * Halaman pemilihan program
     */
    public function programs()
    {
        $tenant = Tenant::findOrFail(tenant_id());
        $progress = TenantSetupService::getProgress();

        $availablePrograms = TenantSetupService::getAvailablePrograms();
        $selectedProgramIds = TenantSetupService::getTenantPrograms()
            ->pluck('program_id')
            ->toArray();

        return view('dashboard.onboarding.programs', [
            'tenant' => $tenant,
            'progress' => $progress,
            'availablePrograms' => $availablePrograms,
            'selectedProgramIds' => $selectedProgramIds,
        ]);
    }

    /**
     * Simpan pilihan program
     */
    public function storePrograms(Request $request)
    {
        $request->validate([
            'programs' => 'required|array|min:1',
            'programs.*' => 'exists:programs,id',
        ], [
            'programs.required' => 'Pilih minimal satu program untuk pesantren Anda.',
            'programs.min' => 'Pilih minimal satu program untuk pesantren Anda.',
        ]);

        try {
            TenantSetupService::saveProgramSelection($request->programs);

            // Check if multiple programs selected
            if (count($request->programs) > 1) {
                return redirect()
                    ->route('dashboard.onboarding.program-setup-queue')
                    ->with('success', 'Program berhasil dipilih. Silakan atur setiap program secara terpisah.');
            }

            return redirect()
                ->route('dashboard.onboarding.wizard', ['step' => 'kelas'])
                ->with('success', 'Program berhasil dipilih. Sekarang tambahkan kelas.');
        } catch (\Exception $e) {
            Log::error('Error saving program selection: ' . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan pilihan program. Silakan coba lagi.');
        }
    }

    /**
     * Halaman guide setup lengkap dengan progress
     */
    public function setupGuide()
    {
        $tenant = Tenant::findOrFail(tenant_id());
        
        // Auto-update steps based on current state
        TenantSetupService::autoUpdateSteps();
        
        $progress = TenantSetupService::getProgress();
        $nextStep = $progress->getNextStep();
        $allSteps = $progress->getAllSteps();

        // Get tenant programs
        $tenantPrograms = TenantSetupService::getTenantPrograms();

        return view('dashboard.onboarding.setup-guide', [
            'tenant' => $tenant,
            'progress' => $progress,
            'nextStep' => $nextStep,
            'allSteps' => $allSteps,
            'tenantPrograms' => $tenantPrograms,
            'percentage' => $progress->progress_percentage,
        ]);
    }

    /**
     * Skip onboarding (untuk user yang sudah paham)
     */
    public function skip(Request $request)
    {
        // Hanya bisa skip jika sudah memilih minimal 1 program
        if (!TenantSetupService::hasSelectedPrograms()) {
            return redirect()
                ->route('dashboard.onboarding.programs')
                ->with('warning', 'Anda harus memilih minimal satu program terlebih dahulu.');
        }

        // Force complete all steps via service (escape hatch for advanced users)
        TenantSetupService::forceCompleteAllSteps();

        return redirect()
            ->route('dashboard.index')
            ->with('info', 'Setup dilewati. Anda dapat mengatur data pesantren kapan saja melalui menu yang tersedia.');
    }

    /**
     * Refresh progress and return current status (replaces manual step marking)
     */
    public function completeStep(Request $request, string $step)
    {
        try {
            // Refresh progress based on actual data state
            TenantSetupService::refreshProgress();
            $progress = TenantSetupService::getProgress();

            return response()->json([
                'success' => true,
                'message' => 'Progress berhasil diperbarui.',
                'progress_percentage' => $progress->progress_percentage,
                'setup_status' => $progress->setup_status,
                'is_siap_operasional' => $progress->setup_status === 'siap_operasional',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current progress JSON (untuk AJAX)
     */
    public function progressJson()
    {
        try {
            TenantSetupService::autoUpdateSteps();
            $progress = TenantSetupService::getProgress();

            return response()->json([
                'success' => true,
                'data' => [
                    'percentage' => $progress->progress_percentage,
                    'status' => $progress->setup_status,
                    'is_siap_operasional' => $progress->setup_status === 'siap_operasional',
                    'next_step' => $progress->getNextStep(),
                    'steps' => [
                        'program_selected'        => $progress->step_program_selected,
                        'kelas_template_applied'  => $progress->step_kelas_template_applied,
                        'subjects_template_applied' => $progress->step_subjects_template_applied,
                        'first_ustadz_added'      => $progress->step_first_ustadz_added,
                        'first_santri_added'      => $progress->step_first_santri_added,
                        'jadwal_setup'            => $progress->step_jadwal_setup,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Wizard setup — multi-step form driven entirely by OnboardingStepRegistry
     * (config/onboarding.php). Step order/labels/routes are NOT hardcoded here:
     * default flow is Kelas → Mapel → Ustadz → Penugasan → Jadwal → Ringkasan.
     *
     * Users can never land on a step whose prerequisites aren't met yet — if
     * ?step= points to a locked step, we redirect to the first incomplete
     * required step instead (e.g. Jadwal is locked until Penugasan exists).
     */
    public function wizard(Request $request)
    {
        if (!TenantSetupService::hasSelectedPrograms()) {
            return redirect()->route('dashboard.onboarding.programs')
                ->with('warning', 'Pilih program terlebih dahulu sebelum melanjutkan setup.');
        }

        TenantSetupService::autoUpdateSteps();

        $tenantId = tenant_id();

        $tenantPrograms = TenantSetupService::getTenantPrograms();
        $actualProgress = TenantSetupService::getActualProgress($tenantId);

        // Get current program from session (for multi-program flow)
        $currentProgramId = session('onboarding_program_id');
        $currentProgram = null;
        if ($currentProgramId) {
            $currentProgram = $tenantPrograms->where('program_id', $currentProgramId)->first()?->program;
        }

        // If no current program, use first one
        if (!$currentProgram && $tenantPrograms->isNotEmpty()) {
            $currentProgram = $tenantPrograms->first()->program;
            session(['onboarding_program_id' => $currentProgram->id]);
        }

        $programSlug = $currentProgram?->slug;
        $wizardSteps = OnboardingStepRegistry::flow($programSlug);

        $requestedStep = $request->input('step', OnboardingStepRegistry::landingKey($actualProgress, $programSlug));

        // Guard: never let a user land on a step whose prerequisites are missing.
        if (!OnboardingStepRegistry::isUnlocked($requestedStep, $actualProgress, $programSlug)) {
            $step = OnboardingStepRegistry::firstIncompleteKey($actualProgress, $programSlug)
                ?? OnboardingStepRegistry::keys($programSlug)[0];

            return redirect()->route('dashboard.onboarding.wizard', ['step' => $step])
                ->with('warning', 'Selesaikan langkah sebelumnya terlebih dahulu sebelum melanjutkan.');
        }

        $step = $requestedStep;

        // Filter by current program if in multi-program flow
        if ($currentProgram) {
            $kelasList    = Kelas::where('tenant_id', $tenantId)->where('program_id', $currentProgram->id)->with('program')->orderBy('name')->get();
            $subjectsList = Subject::where('tenant_id', $tenantId)->where('program_id', $currentProgram->id)->with('program')->orderBy('name')->get();
            $penugasanList = \App\Models\UstadzKelas::where('tenant_id', $tenantId)->where('program_id', $currentProgram->id)
                ->with(['ustadz.user', 'kelas', 'subject'])
                ->orderBy('created_at', 'desc')
                ->get();
            $jadwalList   = \App\Models\Schedule::where('tenant_id', $tenantId)->where('program_id', $currentProgram->id)
                ->with(['kelas', 'subject', 'ustadz.user'])
                ->orderBy('jam_mulai')
                ->get();
        } else {
            $kelasList    = Kelas::where('tenant_id', $tenantId)->with('program')->orderBy('name')->get();
            $subjectsList = Subject::where('tenant_id', $tenantId)->with('program')->orderBy('name')->get();
            $penugasanList = \App\Models\UstadzKelas::where('tenant_id', $tenantId)
                ->with(['ustadz.user', 'kelas', 'subject'])
                ->orderBy('created_at', 'desc')
                ->get();
            $jadwalList   = \App\Models\Schedule::where('tenant_id', $tenantId)
                ->with(['kelas', 'subject', 'ustadz.user'])
                ->orderBy('jam_mulai')
                ->get();
        }
        $ustadzList   = \App\Models\Ustadz::where('tenant_id', $tenantId)->with('user')->orderBy('created_at', 'desc')->get();

        // Get first program for redirects
        $firstProgram = $tenantPrograms->first()?->program;

        return view('dashboard.onboarding.wizard', [
            'step'           => $step,
            'wizardSteps'    => $wizardSteps,
            'previousStep'   => OnboardingStepRegistry::previousKey($step, $programSlug),
            'nextStep'       => OnboardingStepRegistry::nextKey($step, $programSlug),
            'tenantPrograms' => $tenantPrograms,
            'actualProgress' => $actualProgress,
            'kelasList'      => $kelasList,
            'subjectsList'   => $subjectsList,
            'ustadzList'     => $ustadzList,
            'penugasanList'  => $penugasanList,
            'jadwalList'     => $jadwalList,
            'firstProgram'   => $firstProgram,
            'currentProgram' => $currentProgram,
            'hariList'       => \App\Models\Schedule::HARI,
        ]);
    }

    /**
     * Bulk-create kelas during onboarding wizard.
     * Accepts kelas[].name + kelas[].description per program.
     * Skips empty rows silently.
     */
    public function storeKelas(Request $request)
    {
        $request->validate([
            'kelas'              => 'required|array|min:1',
            'kelas.*.name'       => 'required|string|max:100',
            'kelas.*.program_id' => 'required|integer|exists:programs,id',
        ], [
            'kelas.required'         => 'Tambahkan minimal 1 kelas.',
            'kelas.*.name.required'  => 'Nama kelas wajib diisi.',
            'kelas.*.program_id.required' => 'Program wajib dipilih.',
        ]);

        $tenantId = (int) auth()->user()->tenant_id;
        $created  = 0;

        $allowedProgramIds = \App\Models\TenantProgram::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->pluck('program_id')
            ->toArray();

        DB::transaction(function () use ($request, $tenantId, $allowedProgramIds, &$created) {
            foreach ($request->input('kelas', []) as $row) {
                $name      = trim($row['name'] ?? '');
                $programId = (int) ($row['program_id'] ?? 0);

                if ($name === '' || !in_array($programId, $allowedProgramIds, true)) {
                    continue;
                }

                $alreadyExists = Kelas::where('tenant_id', $tenantId)
                    ->where('program_id', $programId)
                    ->where('name', $name)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                Kelas::create([
                    'name'        => $name,
                    'description' => trim($row['description'] ?? '') ?: null,
                    'program_id'  => $programId,
                ]);

                $created++;
            }
        });

        return $this->afterStepSaved(
            'kelas',
            $created,
            "{$created} kelas berhasil ditambahkan.",
            'Tidak ada kelas baru yang ditambahkan (mungkin sudah ada).'
        );
    }

    /**
     * Bulk-create mata pelajaran during onboarding wizard.
     */
    public function storeMapel(Request $request)
    {
        $request->validate([
            'mapel'              => 'required|array|min:1',
            'mapel.*.name'       => 'required|string|max:255',
            'mapel.*.program_id' => 'required|integer|exists:programs,id',
        ], [
            'mapel.required'         => 'Tambahkan minimal 1 mata pelajaran.',
            'mapel.*.name.required'  => 'Nama mata pelajaran wajib diisi.',
            'mapel.*.program_id.required' => 'Program wajib dipilih.',
        ]);

        $tenantId = (int) auth()->user()->tenant_id;
        $created  = 0;
        $skipped  = 0;

        $allowedProgramIds = \App\Models\TenantProgram::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->pluck('program_id')
            ->toArray();

        DB::transaction(function () use ($request, $tenantId, $allowedProgramIds, &$created, &$skipped) {
            foreach ($request->input('mapel', []) as $row) {
                $name      = trim($row['name'] ?? '');
                $programId = (int) ($row['program_id'] ?? 0);

                if ($name === '' || !in_array($programId, $allowedProgramIds, true)) {
                    continue;
                }

                $alreadyExists = Subject::where('tenant_id', $tenantId)
                    ->where('name', $name)
                    ->exists();

                if ($alreadyExists) {
                    $skipped++;
                    continue;
                }

                Subject::create([
                    'name'        => $name,
                    'code'        => trim($row['code'] ?? '') ?: null,
                    'description' => trim($row['description'] ?? '') ?: null,
                    'tenant_id'   => $tenantId,
                    'program_id'  => $programId,
                ]);

                $created++;
            }
        });

        return $this->afterStepSaved(
            'mapel',
            $created,
            "{$created} mata pelajaran berhasil ditambahkan." . ($skipped > 0 ? " {$skipped} dilewati (sudah ada)." : ''),
            'Tidak ada mata pelajaran baru yang ditambahkan.'
        );
    }

    /**
     * Create ustadz (teacher) during onboarding wizard.
     * A Teacher must exist before a Teaching Assignment can be created —
     * enforced by OnboardingStepRegistry / the wizard's unlock guard, not by
     * a runtime error here.
     */
    public function storeUstadz(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
        ], [
            'name.required' => 'Nama ustadz wajib diisi.',
        ]);

        $tenant = TenantService::getTenant();

        try {
            DB::transaction(function () use ($data, $tenant) {
                $userResult = $this->provisioningService->provisionNewUstadz([
                    'name'      => $data['name'],
                    'tenant_id' => $tenant->id,
                    'email'     => $data['email'] ?? null,
                ]);

                Ustadz::create([
                    'tenant_id' => $tenant->id,
                    'user_id'   => $userResult['user']->id,
                    'phone'     => $data['phone'] ?? null,
                    'status'    => Ustadz::STATUS_ACTIVE,
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Onboarding wizard: gagal menambahkan ustadz: ' . $e->getMessage());

            return redirect()->route('dashboard.onboarding.wizard', ['step' => 'ustadz'])
                ->with('error', 'Gagal menambahkan ustadz: ' . $e->getMessage())
                ->withInput();
        }

        return $this->afterStepSaved(
            'ustadz',
            1,
            "Ustadz {$data['name']} berhasil ditambahkan.",
            ''
        );
    }

    /**
     * Bulk-create teaching assignments (Ustadz ↔ Kelas ↔ Subject) during the
     * onboarding wizard. This step is only reachable once a Teacher exists
     * (see OnboardingStepRegistry), and must itself be complete before the
     * Jadwal step becomes reachable.
     */
    public function storePenugasan(Request $request)
    {
        $request->validate([
            'penugasan'              => 'required|array|min:1',
            'penugasan.*.ustadz_id'  => 'required|integer|exists:ustadz,id',
            'penugasan.*.kelas_id'   => 'required|integer|exists:kelas,id',
            'penugasan.*.subject_id' => 'required|integer|exists:subjects,id',
        ], [
            'penugasan.required'                 => 'Tambahkan minimal 1 penugasan mengajar.',
            'penugasan.*.ustadz_id.required'      => 'Ustadz wajib dipilih.',
            'penugasan.*.kelas_id.required'       => 'Kelas wajib dipilih.',
            'penugasan.*.subject_id.required'     => 'Mata pelajaran wajib dipilih.',
        ]);

        $tenantId = (int) auth()->user()->tenant_id;
        $created  = 0;
        $errors   = [];

        DB::transaction(function () use ($request, $tenantId, &$created, &$errors) {
            foreach ($request->input('penugasan', []) as $index => $row) {
                $ustadzId  = (int) ($row['ustadz_id'] ?? 0);
                $kelasId   = (int) ($row['kelas_id'] ?? 0);
                $subjectId = (int) ($row['subject_id'] ?? 0);

                if (!$ustadzId || !$kelasId || !$subjectId) {
                    continue;
                }

                $ustadz = Ustadz::where('tenant_id', $tenantId)->find($ustadzId);
                $kelas  = Kelas::where('tenant_id', $tenantId)->find($kelasId);

                if (!$ustadz || !$kelas) {
                    $errors[] = 'Baris ' . ($index + 1) . ': Ustadz atau kelas tidak ditemukan.';
                    continue;
                }

                $validation = UstadzKelas::validateProgramOwnership($kelas->program_id, $kelasId, $subjectId, $tenantId);
                if (!$validation['valid']) {
                    $errors[] = 'Baris ' . ($index + 1) . ': ' . implode(' ', $validation['errors']);
                    continue;
                }

                $exists = UstadzKelas::where('tenant_id', $tenantId)
                    ->where('ustadz_id', $ustadzId)
                    ->where('kelas_id', $kelasId)
                    ->where('subject_id', $subjectId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                UstadzKelas::create([
                    'tenant_id'  => $tenantId,
                    'program_id' => $kelas->program_id,
                    'ustadz_id'  => $ustadzId,
                    'kelas_id'   => $kelasId,
                    'subject_id' => $subjectId,
                ]);

                $created++;
            }
        });

        if ($created === 0 && count($errors) > 0) {
            return redirect()->route('dashboard.onboarding.wizard', ['step' => 'penugasan'])
                ->with('error', 'Gagal membuat penugasan: ' . implode('; ', $errors))
                ->withInput();
        }

        $msg = "{$created} penugasan mengajar berhasil ditambahkan.";
        if (count($errors) > 0) {
            $msg .= ' Beberapa gagal: ' . implode('; ', $errors);
        }

        return $this->afterStepSaved(
            'penugasan',
            $created,
            $msg,
            'Tidak ada penugasan baru yang ditambahkan (mungkin sudah ada).'
        );
    }

    /**
     * Bulk-create jadwal during onboarding wizard.
     * Every row must reference an EXISTING teaching assignment (UstadzKelas)
     * for the chosen kelas + subject — by the time a user reaches this step,
     * the Penugasan step guarantees at least one assignment exists, so we
     * never fall back to guessing/auto-creating one with an arbitrary ustadz.
     */
    public function storeJadwal(Request $request)
    {
        $jadwalData = $request->input('jadwal', []);

        // Custom validation for each row (Laravel wildcard 'after' rule doesn't work properly)
        $errors = [];
        foreach ($jadwalData as $index => $row) {
            $rowNum = $index + 1;

            if (empty($row['ustadz_kelas_id'])) {
                $errors["jadwal.{$index}.ustadz_kelas_id"] = "Baris {$rowNum}: Penugasan mengajar (kelas + mapel + ustadz) wajib dipilih.";
            }
            if (empty($row['hari'])) {
                $errors["jadwal.{$index}.hari"] = "Baris {$rowNum}: Hari wajib dipilih.";
            }
            if (empty($row['jam_mulai'])) {
                $errors["jadwal.{$index}.jam_mulai"] = "Baris {$rowNum}: Jam mulai wajib diisi.";
            }
            if (empty($row['jam_selesai'])) {
                $errors["jadwal.{$index}.jam_selesai"] = "Baris {$rowNum}: Jam selesai wajib diisi.";
            }
            // Validate jam_selesai > jam_mulai
            if (!empty($row['jam_mulai']) && !empty($row['jam_selesai'])) {
                if ($row['jam_selesai'] <= $row['jam_mulai']) {
                    $errors["jadwal.{$index}.jam_selesai"] = "Baris {$rowNum}: Jam selesai harus setelah jam mulai.";
                }
            }
        }

        if (count($errors) > 0 || empty($jadwalData)) {
            return redirect()->route('dashboard.onboarding.wizard', ['step' => 'jadwal'])
                ->withErrors($errors)
                ->with('error', empty($jadwalData) ? 'Tambahkan minimal 1 jadwal.' : 'Validasi gagal. Periksa input jadwal.')
                ->withInput();
        }

        $tenantId = (int) auth()->user()->tenant_id;
        $created = 0;
        $errors = [];

        DB::transaction(function () use ($request, $tenantId, &$created, &$errors) {
            foreach ($request->input('jadwal', []) as $index => $row) {
                $ustadzKelasId = (int) ($row['ustadz_kelas_id'] ?? 0);
                $hari = $row['hari'] ?? '';
                $jamMulai = $row['jam_mulai'] ?? '';
                $jamSelesai = $row['jam_selesai'] ?? '';

                if (!$ustadzKelasId || !$hari || !$jamMulai || !$jamSelesai) {
                    continue;
                }

                // Every schedule MUST reference an existing teaching assignment —
                // by design the Penugasan step already guarantees at least one
                // exists before Jadwal is reachable, so this is a data-integrity
                // check, not a "please go create prerequisites" error path.
                $ustadzKelas = UstadzKelas::where('tenant_id', $tenantId)
                    ->with(['kelas', 'subject'])
                    ->find($ustadzKelasId);

                if (!$ustadzKelas || !$ustadzKelas->kelas || !$ustadzKelas->subject) {
                    $errors[] = "Baris " . ($index + 1) . ": Penugasan mengajar tidak ditemukan.";
                    continue;
                }

                $kelas = $ustadzKelas->kelas;
                $subject = $ustadzKelas->subject;

                // Check for conflicts on the same teaching assignment
                $conflict = \App\Models\Schedule::where('tenant_id', $tenantId)
                    ->where('ustadz_kelas_id', $ustadzKelasId)
                    ->where('hari', $hari)
                    ->where(function ($q) use ($jamMulai, $jamSelesai) {
                        $q->where('jam_mulai', '<', $jamSelesai)
                          ->where('jam_selesai', '>', $jamMulai);
                    })
                    ->first();

                if ($conflict) {
                    $errors[] = "Baris " . ($index + 1) . ": Jadwal bentrok dengan \"{$conflict->mata_pelajaran}\" ({$conflict->jam_mulai}–{$conflict->jam_selesai}).";
                    continue;
                }

                \App\Models\Schedule::create([
                    'tenant_id'       => $tenantId,
                    'program_id'      => $kelas->program_id,
                    'ustadz_kelas_id' => $ustadzKelas->id,
                    'kelas_id'        => $kelas->id,
                    'mata_pelajaran'  => $subject->name,
                    'kelas'           => $kelas->name,
                    'hari'            => $hari,
                    'jam_mulai'       => $jamMulai,
                    'jam_selesai'     => $jamSelesai,
                ]);

                $created++;
            }
        });

        if ($created === 0 && count($errors) > 0) {
            return redirect()->route('dashboard.onboarding.wizard', ['step' => 'jadwal'])
                ->with('error', 'Gagal membuat jadwal: ' . implode('; ', $errors));
        }

        $msg = "{$created} jadwal berhasil ditambahkan.";
        if (count($errors) > 0) {
            $msg .= ' Beberapa gagal: ' . implode('; ', $errors);
        }

        return $this->afterStepSaved('jadwal', $created, $msg, 'Tidak ada jadwal baru yang ditambahkan.');
    }

    /**
     * Skip a wizard step — move to the next step without saving.
     * Required steps (per OnboardingStepRegistry) can only be "skipped" once
     * their data already exists; the step order itself comes from the
     * registry, never a hardcoded map here.
     */
    public function skipStep(Request $request)
    {
        $step = $request->input('step', '');
        $programSlug = $this->currentProgramSlug();

        if (!OnboardingStepRegistry::find($step, $programSlug)) {
            return redirect()->route('dashboard.onboarding.wizard');
        }

        $actualProgress = TenantSetupService::getActualProgress(tenant_id());
        $def = OnboardingStepRegistry::find($step, $programSlug);

        if (($def['required'] ?? false) && !OnboardingStepRegistry::isStepComplete($step, $actualProgress, $programSlug)) {
            return redirect()->route('dashboard.onboarding.wizard', ['step' => $step])
                ->with('warning', 'Langkah ini wajib diselesaikan sebelum bisa dilanjutkan.');
        }

        $nextKey = OnboardingStepRegistry::nextKey($step, $programSlug) ?? $step;

        return redirect()->route('dashboard.onboarding.wizard', ['step' => $nextKey]);
    }

    /**
     * Resolve the program slug currently active in the onboarding session,
     * falling back to the tenant's first active program.
     */
    private function currentProgramSlug(): ?string
    {
        $currentProgramId = session('onboarding_program_id');

        if ($currentProgramId) {
            return Program::find($currentProgramId)?->slug;
        }

        return TenantSetupService::getFirstActiveProgram(tenant_id())?->slug;
    }

    /**
     * Shared "what happens after a wizard step is saved" flow:
     *  - refresh onboarding progress from live data
     *  - if the CURRENT program's setup is now fully complete (multi-program
     *    flow), return to the program queue
     *  - otherwise advance to the next step per OnboardingStepRegistry, or
     *    stay on the current step if nothing was actually created
     */
    private function afterStepSaved(string $stepKey, int $created, string $successMessage, string $emptyMessage)
    {
        TenantSetupService::refreshProgress();

        $currentProgramId = session('onboarding_program_id');
        if ($currentProgramId) {
            $programProgress = $this->getProgramSetupProgress($currentProgramId);

            if ($programProgress['is_complete']) {
                session()->forget('onboarding_program_id');

                return redirect()->route('dashboard.onboarding.program-setup-queue')
                    ->with('success', 'Program berhasil disetup lengkap!');
            }
        }

        if ($created === 0) {
            return redirect()->route('dashboard.onboarding.wizard', ['step' => $stepKey])
                ->with('warning', $emptyMessage);
        }

        $nextKey = OnboardingStepRegistry::nextKey($stepKey, $this->currentProgramSlug()) ?? $stepKey;

        return redirect()->route('dashboard.onboarding.wizard', ['step' => $nextKey])
            ->with('success', $successMessage);
    }
}
