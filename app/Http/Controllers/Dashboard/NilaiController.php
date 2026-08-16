<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Santri;
use App\Models\Schedule;
use App\Models\Subject;
use App\Services\AssessmentTypeService;
use App\Services\FeatureDependencyService;
use App\Services\NilaiService;
use App\Services\ProgramAccessService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NilaiController extends Controller
{
    public function __construct(
        private NilaiService $nilaiService,
        private AssessmentTypeService $assessmentTypeService,
    ) {}

    /**
     * Pick kelas → subject list scoped to the current user's assignments.
     */
    public function index(Request $request, string $programSlug)
    {
        $this->authorize('viewAny', Nilai::class);

        // Check dependencies - show warning if missing
        $dependencyCheck = FeatureDependencyService::validateInputNilai();
        if (!$dependencyCheck['can_access']) {
            return view('dashboard.nilai.index', [
                'warning' => $dependencyCheck,
                'kelasList' => collect(),
                'programSlug' => $programSlug,
                'programId' => null,
                'assessmentTypes' => [],
                'typeColors' => [],
            ]);
        }

        // Get program by slug, scoped to the current tenant
        $program = ProgramAccessService::getBySlug($programSlug) ?? abort(404);
        $programId = $program->id;

        $activeTypes = $this->assessmentTypeService->getActiveTypes($programId);
        $assessmentTypes = $activeTypes->mapWithKeys(fn ($type) => [$type->code => $type->label])->all();
        $assessmentMeta = $activeTypes->mapWithKeys(fn ($type) => [$type->code => $type]);

        $user = auth()->user();

        // Read-only users (Santri/Parent) should view their own data, not the input dashboard
        if (! $user->can('create', Nilai::class)) {
            if ($user->santri) {
                return redirect(tenant_route('dashboard.akademik.nilai.show', [
                    'programSlug' => $programSlug,
                    'santriId'    => $user->santri->id,
                ]));
            }

            if ($user->parent) {
                $firstChild = $user->parent->santri()->first();
                if ($firstChild) {
                    return redirect(tenant_route('dashboard.akademik.nilai.show', [
                        'programSlug' => $programSlug,
                        'santriId'    => $firstChild->id,
                    ]));
                }
            }
        }

        $kelasList = $this->nilaiService->accessibleKelas($user, $programId);

        return view('dashboard.nilai.index', compact(
            'kelasList', 'programSlug', 'programId', 'assessmentTypes', 'assessmentMeta'
        ));
    }

    /**
     * Bulk input form: santri list for a kelas+subject+tanggal+assessment_type.
     */
    public function input(Request $request, string $programSlug)
    {
        $program   = ProgramAccessService::getBySlug($programSlug) ?? abort(404);
        $programId = $program->id;

        $activeTypes     = $this->assessmentTypeService->getActiveTypes($programId);
        $activeCodes     = $this->assessmentTypeService->getActiveCodes($programId);
        $assessmentTypes = $activeTypes->mapWithKeys(fn ($type) => [$type->code => $type->label])->all();
        $assessmentMeta  = $activeTypes->mapWithKeys(fn ($type) => [$type->code => $type]);
        $defaultType     = $this->assessmentTypeService->getDefaultTypeCode($programId);

        $request->validate([
            'kelas_id'        => ['required', 'integer', Rule::exists('kelas', 'id')->where('tenant_id', tenant_id())->where('program_id', $programId)],
            'subject_id'      => ['required', 'integer', Rule::exists('subjects', 'id')->where('tenant_id', tenant_id())->where('program_id', $programId)],
            'tanggal'         => ['required', 'date'],
            'assessment_type' => ['nullable', 'string', 'in:' . $activeCodes],
        ]);

        $kelas          = Kelas::findOrFail((int) $request->kelas_id);
        $subject        = Subject::findOrFail((int) $request->subject_id);
        $tanggal        = $request->tanggal;
        $assessmentType = $request->get('assessment_type', $defaultType);

        $this->authorize('recordFor', [Nilai::class, $kelas, $subject, $programId]);

        $santriList = Santri::where('kelas_id', $kelas->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $existing = Nilai::where('program_id', $programId)
            ->where('kelas_id', $kelas->id)
            ->where('subject_id', $subject->id)
            ->where('tanggal', $tanggal)
            ->where('assessment_type', $assessmentType)
            ->get()
            ->keyBy('santri_id');

        return view('dashboard.nilai.input', compact(
            'kelas', 'subject', 'tanggal', 'programSlug', 'programId',
            'santriList', 'existing', 'assessmentType', 'assessmentTypes', 'assessmentMeta'
        ));
    }

    /**
     * Bulk store/update via NilaiService.
     */
    public function storeBulk(Request $request, string $programSlug)
    {
        $program   = ProgramAccessService::getBySlug($programSlug) ?? abort(404);
        $programId = $program->id;

        $activeCodes = $this->assessmentTypeService->getActiveCodes($programId);

        $validated = $request->validate([
            'kelas_id'              => ['required', 'integer', Rule::exists('kelas', 'id')->where('tenant_id', tenant_id())->where('program_id', $programId)],
            'subject_id'            => ['required', 'integer', Rule::exists('subjects', 'id')->where('tenant_id', tenant_id())->where('program_id', $programId)],
            'tanggal'               => ['required', 'date'],
            'assessment_type'       => ['required', 'string', 'in:' . $activeCodes],
            'records'               => ['required', 'array', 'min:1'],
            'records.*.santri_id'   => ['required', 'integer', Rule::exists('santri', 'id')->where('tenant_id', tenant_id())->where('kelas_id', request('kelas_id'))],
            'records.*.nilai'       => ['nullable', 'numeric', 'min:0', 'max:100'],
            'records.*.materi'      => ['nullable', 'string', 'max:255'],
            'records.*.catatan'     => ['nullable', 'string', 'max:500'],
        ]);

        $kelas          = Kelas::findOrFail((int) $validated['kelas_id']);
        $subject        = Subject::findOrFail((int) $validated['subject_id']);
        $tanggal        = $validated['tanggal'];
        $assessmentType = $validated['assessment_type'];
        $user           = auth()->user();

        $this->authorize('recordFor', [Nilai::class, $kelas, $subject, $programId]);

        $ustadzKelas = $user->isUstadz()
            ? $this->nilaiService->resolveUstadzKelasForUser($user, $kelas->id, $subject->id, $programId)
            : $this->nilaiService->resolveUstadzKelas($kelas->id, $subject->id, $programId);

        $this->nilaiService->bulkUpsert(
            $validated['records'], $kelas, $subject, $tanggal, $programId, $ustadzKelas, $assessmentType
        );

        return redirect(tenant_route('dashboard.akademik.nilai.index', ['programSlug' => $programSlug]))
            ->with('success', 'Data nilai berhasil disimpan.');
    }

    /**
     * Input nilai directly from a jadwal (schedule) row.
     * Resolves kelas, subject, and program from the schedule — Ustadz never picks them manually.
     */
    public function fromJadwal(Request $request, string $programSlug, int $jadwalId)
    {
        $program   = ProgramAccessService::getBySlug($programSlug) ?? abort(404);
        $programId = $program->id;

        $activeTypes     = $this->assessmentTypeService->getActiveTypes($programId);
        $assessmentTypes = $activeTypes->mapWithKeys(fn ($type) => [$type->code => $type->label])->all();
        $assessmentMeta  = $activeTypes->mapWithKeys(fn ($type) => [$type->code => $type]);
        $defaultType     = $this->assessmentTypeService->getDefaultTypeCode($programId);

        $jadwal = Schedule::with(['ustadzKelas.kelas', 'ustadzKelas.subject', 'program'])->findOrFail($jadwalId);

        if ((int) $jadwal->program_id !== $programId) {
            abort(404);
        }

        $kelas   = $jadwal->ustadzKelas?->kelas ?? Kelas::findOrFail($jadwal->kelas_id);
        $subject = $jadwal->ustadzKelas?->subject ?? Subject::findOrFail($jadwal->subject_id);

        $this->authorize('recordFor', [Nilai::class, $kelas, $subject, $programId]);
        $tanggal = $request->get('tanggal', today()->toDateString());

        $santriList = Santri::where('kelas_id', $kelas->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $assessmentType = $request->get('assessment_type', $defaultType);

        $existing = Nilai::where('program_id', $programId)
            ->where('kelas_id', $kelas->id)
            ->where('subject_id', $subject->id)
            ->where('tanggal', $tanggal)
            ->where('assessment_type', $assessmentType)
            ->get()
            ->keyBy('santri_id');

        return view('dashboard.nilai.input', compact(
            'kelas', 'subject', 'tanggal', 'programSlug', 'programId',
            'santriList', 'existing', 'assessmentType', 'assessmentTypes', 'assessmentMeta'
        ));
    }

    /**
     * Santri progress view.
     */
    public function show(Request $request, string $programSlug, int $santriId)
    {
        // Get program by slug, scoped to the current tenant
        $program = ProgramAccessService::getBySlug($programSlug) ?? abort(404);
        $programId = $program->id;

        $santri = Santri::with('kelas')->findOrFail($santriId);
        $this->authorize('view', $santri);

        $inProgram = $santri->kelas?->program_id === $programId
            || $santri->programs()->where('program_id', $programId)->exists();

        if (! $inProgram) {
            abort(404);
        }

        $records = Nilai::with(['subject', 'kelas', 'ustadzKelas.ustadz.user'])
            ->where('program_id', $programId)
            ->where('santri_id', $santri->id)
            ->orderBy('tanggal', 'desc')
            ->get();

        $bySubject       = $records->groupBy('subject_id');
        $activeTypes     = $this->assessmentTypeService->getActiveTypes($programId);
        $assessmentTypes = $activeTypes->mapWithKeys(fn ($type) => [$type->code => $type->label])->all();
        $assessmentMeta  = $activeTypes->mapWithKeys(fn ($type) => [$type->code => $type]);

        return view('dashboard.nilai.show', compact(
            'santri', 'records', 'bySubject', 'programSlug', 'programId', 'assessmentTypes', 'assessmentMeta'
        ));
    }
}
