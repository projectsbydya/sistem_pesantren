<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Kelas;
use App\Models\Parents;
use App\Models\Program;
use App\Models\SantriProgram;
use App\Exports\CredentialsExport;
use App\Models\Santri;
use App\Models\User;
use App\Services\ProgramValidationService;
use App\Services\TenantService;
use App\Services\TenantSetupService;
use App\Services\UserProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SantriController extends Controller
{
    protected UserProvisioningService $provisioningService;

    public function __construct(UserProvisioningService $provisioningService)
    {
        $this->provisioningService = $provisioningService;
    }

    /**
     * Display santri list filtered by user relations:
     * - Parent: Only their children via parent relationship
     * - Student: Only themselves via santri relationship
     * - Ustadz/Admin: All santri in tenant (TenantScope applies)
     */
    public function index()
    {
        $this->authorize('viewAny', Santri::class);

        $user = auth()->user();

        // Relation-based query filtering: USER -> RELATION -> Santri
        $santri = Santri::with('user')
            ->when($user->parent, function ($query) use ($user) {
                // Parent: only their children
                return $query->forParent($user->parent->id);
            })
            ->when($user->santri, function ($query) use ($user) {
                // Student: only themselves
                return $query->where('id', $user->santri->id);
            })
            ->orderBy('name')
            ->get();

        return view('dashboard.santri.index', compact('santri'));
    }

    public function create()
    {
        $this->authorize('create', Santri::class);

        $tenant = TenantService::getTenant();

        // Get tenant programs and their classes (no hardcoded diniyah)
        $tenantPrograms = $tenant?->activePrograms()->with('kelas')->get() ?? collect([]);
        $kamarList = Kamar::orderBy('name')->get();

        return view('dashboard.santri.create', compact('tenantPrograms', 'kamarList'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Santri::class);

        $tenant = TenantService::getTenant();
        $tenantPrograms = $tenant?->activePrograms()->get() ?? collect([]);
        $tenantProgramSlugs = $tenantPrograms->pluck('slug')->toArray();
        $tenantProgramIds = $tenantPrograms->keyBy('slug')->map->id->toArray();

        $selectedProgramSlugs = $request->input('programs', []);

        $data = $request->validate([
            'nis'              => ['required', 'string', 'max:50', Rule::unique('santri', 'nis')->where('tenant_id', $tenant->id)],
            'name'             => 'required|string|max:255',
            'gender'           => 'required|in:L,P',
            'birth_date'       => 'nullable|date',
            'address'          => 'nullable|string',
            'status'           => 'nullable|string|max:50',
            'school_level'     => 'nullable|string|max:100',
            'school_name'      => 'nullable|string|max:255',
            'programs'         => 'nullable|array',
            'programs.*'       => ['nullable', Rule::in($tenantProgramSlugs)],
            'kelas_ids'        => 'nullable|array',
            'kelas_ids.*'      => 'nullable|integer|exists:kelas,id',
            'kamar_id'         => 'nullable|integer|exists:kamar,id',
            'is_mondok'        => 'nullable|boolean',
            'parent_name'         => 'nullable|string|max:255',
            'parent_nik'          => 'nullable|string|size:16',
            'parent_relationship' => 'nullable|in:father,mother,guardian',
            'parent_phone'        => 'nullable|string|max:20',
            'reuse_existing_parent' => 'nullable|boolean',
        ]);

        $tenant = TenantService::getTenant();

        try {
            $credentials = DB::transaction(function () use ($data, $tenant) {
                $result = [];

                // 1. Provision Santri user FIRST — user_id never null after commit
                $santriUserResult = $this->provisioningService->provisionNewSantri([
                    'name'      => $data['name'],
                    'nis'       => $data['nis'],
                    'tenant_id' => $tenant->id,
                ]);

                // 2. Create Santri record
                $isMondok = !empty($data['is_mondok']);
                $santri = Santri::create([
                    'nis'          => $data['nis'],
                    'name'         => $data['name'],
                    'gender'       => $data['gender'],
                    'birth_date'   => $data['birth_date'] ?? null,
                    'address'      => $data['address'] ?? null,
                    'status'       => $data['status'] ?? 'active',
                    'school_level' => $data['school_level'] ?? null,
                    'school_name'  => $data['school_name'] ?? null,
                    'kamar_id'     => $isMondok ? ($data['kamar_id'] ?? null) : null,
                    'is_mondok'    => $isMondok,
                    'user_id'      => $santriUserResult['user']->id,
                ]);

                // 3. Create SantriProgram entries (program → kelas per program)
                foreach ($data['programs'] ?? [] as $programSlug) {
                    $programId = $tenantProgramIds[$programSlug] ?? null;
                    if (!$programId) continue;

                    $kelasId = $data['kelas_ids'][$programSlug] ?? null;
                    SantriProgram::create([
                        'tenant_id'  => $tenant->id,
                        'santri_id'  => $santri->id,
                        'program_id' => $programId,
                        'kelas_id'   => $kelasId,
                        'status'     => 'aktif',
                        'started_at' => now(),
                    ]);
                }

                $result['santri'] = [
                    'name'     => $data['name'],
                    'email'    => $santriUserResult['user']->email,
                    'password' => $santriUserResult['password'],
                    'role'     => 'Santri',
                ];

                // 3. Handle Parent: deduplication by NIK or Phone with tenant isolation
                if (!empty($data['parent_name'])) {
                    $relationship = $data['parent_relationship'] ?? Parents::RELATIONSHIP_FATHER;
                    $parent = null;
                    $isReusedParent = false;

                    // Rule 1: If NIK provided, search ONLY by NIK (no fallback to phone)
                    if (!empty($data['parent_nik'])) {
                        $parent = Parents::where('tenant_id', $tenant->id)
                            ->where('nik', $data['parent_nik'])
                            ->first();
                        // If not found, $parent stays null → create new parent
                    }
                    // Rule 2: If NIK empty, search ONLY by Phone (mutually exclusive)
                    elseif (!empty($data['parent_phone'])) {
                        $parent = Parents::where('tenant_id', $tenant->id)
                            ->where('phone', $data['parent_phone'])
                            ->first();

                        // If found by phone, require confirmation to reuse
                        if ($parent && empty($data['reuse_existing_parent'])) {
                            throw new \RuntimeException('PARENT_EXISTS_BY_PHONE:' . $parent->id);
                        }
                    }

                    if ($parent) {
                        // Reuse existing parent
                        $isReusedParent = true;

                        // Update parent info if provided (optional enhancement)
                        $updateData = [];
                        if (empty($parent->nik) && !empty($data['parent_nik'])) {
                            $updateData['nik'] = $data['parent_nik'];
                        }
                        if (!empty($updateData)) {
                            $parent->update($updateData);
                        }
                    } else {
                        // Create new parent
                        $parentUserResult = $this->provisioningService->provisionNewParent([
                            'name'      => $data['parent_name'],
                            'tenant_id' => $tenant->id,
                        ]);

                        $parent = Parents::create([
                            'tenant_id'    => $tenant->id,
                            'user_id'      => $parentUserResult['user']->id,
                            'name'         => $data['parent_name'],
                            'nik'          => $data['parent_nik'] ?? null,
                            'phone'        => $data['parent_phone'] ?? null,
                            'email'        => null,
                            'address'      => $data['address'] ?? null,
                            'relationship' => $relationship,
                            'is_active'    => true,
                        ]);
                    }

                    // Link parent ↔ santri using syncWithoutDetaching to prevent duplicate pivot
                    $santri->parents()->syncWithoutDetaching([
                        $parent->id => [
                            'relationship' => $relationship,
                            'is_primary'   => true,
                        ]
                    ]);
                    $santri->update(['parent_id' => $parent->id]);

                    $relationshipLabel = match ($relationship) {
                        'father'   => 'Ayah',
                        'mother'   => 'Ibu',
                        'guardian' => 'Wali',
                        default    => 'Orang Tua',
                    };

                    // Only include credentials for NEW parents
                    if (!$isReusedParent && isset($parentUserResult)) {
                        $result['parent'] = [
                            'name'         => $data['parent_name'],
                            'email'        => $parentUserResult['user']->email,
                            'password'     => $parentUserResult['password'],
                            'role'         => 'Orang Tua (' . $relationshipLabel . ')',
                            'santri_name'  => $data['name'],
                        ];
                    } else {
                        // For reused parents, note the existing account
                        $result['parent'] = [
                            'name'         => $parent->name,
                            'email'        => $parent->user?->email ?? '-',
                            'password'     => '(akun existing)',
                            'role'         => 'Orang Tua (' . $relationshipLabel . ') - Reused',
                            'santri_name'  => $data['name'],
                        ];
                    }
                }

                return $result;
            });

            session()->put('credentials_data', $credentials);
            session()->put('credentials_santri_name', $data['name']);

            // Refresh onboarding progress after santri creation
            TenantSetupService::refreshProgress();

            return redirect(tenant_route('dashboard.santri.index'))
                ->with('success', "Santri {$data['name']} berhasil ditambahkan dengan akun login.")
                ->with('show_credentials_download', true);

        } catch (\Exception $e) {
            $message = $e->getMessage();

            // Handle parent exists by phone - needs confirmation
            if (str_starts_with($message, 'PARENT_EXISTS_BY_PHONE:')) {
                $parentId = (int) substr($message, strlen('PARENT_EXISTS_BY_PHONE:'));
                $existingParent = Parents::find($parentId);

                return back()
                    ->withInput()
                    ->with('warning_parent_exists', [
                        'parent_id' => $parentId,
                        'parent_name' => $existingParent?->name,
                        'parent_phone' => $existingParent?->phone,
                        'message' => 'Wali dengan nomor HP ini sudah terdaftar. Centang "Gunakan wali existing" untuk melanjutkan.',
                    ]);
            }

            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan santri: ' . $message);
        }
    }

    /**
     * Export credentials to CSV — contains santri + parent accounts
     */
    public function downloadCredentials()
    {
        $this->authorize('downloadCredentials', Santri::class);
        $credentials = session('credentials_data');
        $santriName  = session('credentials_santri_name', 'santri');

        if (empty($credentials)) {
            return redirect(tenant_route('dashboard.santri.index'))
                ->with('error', 'Data credentials tidak ditemukan atau sudah diunduh.');
        }

        session()->forget(['credentials_data', 'credentials_santri_name']);

        $filename = 'akun-login-' . Str::slug($santriName) . '-' . now()->format('Ymd-His') . '.xlsx';
        $loginUrl = main_domain_url('/login');

        return Excel::download(new CredentialsExport($credentials, $loginUrl), $filename);
    }

    public function show($id)
    {
        $santri = Santri::with([
            'parents.user',
            'primaryParent.user',
            'user',
            'programs.program',
            'programs.kelas',
            'kamar',
        ])->findOrFail((int) $id);
        $this->authorize('view', $santri);

        return view('dashboard.santri.show', compact('santri'));
    }

    public function edit($id)
    {
        $santri = Santri::with('programs.program', 'programs.kelas', 'user')->findOrFail((int) $id);
        $this->authorize('update', $santri);

        $tenant = TenantService::getTenant();

        // Get tenant programs and their classes (no hardcoded diniyah)
        $tenantPrograms = $tenant?->activePrograms()->with('kelas')->get() ?? collect([]);
        $kamarList = Kamar::orderBy('name')->get();

        return view('dashboard.santri.edit', compact('santri', 'tenantPrograms', 'kamarList'));
    }

    public function update(Request $request, $id)
    {
        $santri   = Santri::findOrFail((int) $id);
        $this->authorize('update', $santri);

        $tenant = TenantService::getTenant();
        $tenantPrograms = $tenant?->activePrograms()->get() ?? collect([]);
        $tenantProgramSlugs = $tenantPrograms->pluck('slug')->toArray();
        $tenantProgramIds = $tenantPrograms->keyBy('slug')->map->id->toArray();

        $data = $request->validate([
            'nis'              => 'required|string|max:50',
            'name'             => 'required|string|max:255',
            'gender'           => 'required|in:L,P',
            'birth_date'       => 'nullable|date',
            'address'          => 'nullable|string',
            'status'           => 'nullable|string|max:50',
            'school_level'     => 'nullable|string|max:100',
            'school_name'      => 'nullable|string|max:255',
            'programs'         => 'nullable|array',
            'programs.*'       => ['nullable', Rule::in($tenantProgramSlugs)],
            'kelas_ids'        => 'nullable|array',
            'kelas_ids.*'      => 'nullable|integer|exists:kelas,id',
            'kamar_id'         => 'nullable|integer|exists:kamar,id',
            'is_mondok'        => 'nullable|boolean',
        ]);

        $selectedProgramSlugs = $data['programs'] ?? [];
        $isMondok = !empty($data['is_mondok']);

        $santri->update([
            'nis'          => $data['nis'],
            'name'         => $data['name'],
            'gender'       => $data['gender'],
            'birth_date'   => $data['birth_date'] ?? null,
            'address'      => $data['address'] ?? null,
            'status'       => $data['status'] ?? 'active',
            'school_level' => $data['school_level'] ?? null,
            'school_name'  => $data['school_name'] ?? null,
            'is_mondok'    => $isMondok,
            'kamar_id'     => $isMondok ? ($data['kamar_id'] ?? null) : null,
        ]);

        // Build map of existing program slugs to IDs
        $existingProgramsMap = $santri->programs()
            ->with('program')
            ->get()
            ->keyBy(fn($sp) => $sp->program?->slug)
            ->map->id
            ->toArray();

        // Remove deselected programs
        foreach (array_diff(array_keys($existingProgramsMap), $selectedProgramSlugs) as $removedSlug) {
            $santri->programs()->where('id', $existingProgramsMap[$removedSlug])->delete();
        }

        // Add/update selected programs
        foreach ($selectedProgramSlugs as $programSlug) {
            $programId = $tenantProgramIds[$programSlug] ?? null;
            if (!$programId) continue;

            $kelasId = $data['kelas_ids'][$programSlug] ?? null;
            $santri->programs()->updateOrCreate(
                ['program_id' => $programId],
                ['kelas_id' => $kelasId, 'status' => 'aktif']
            );
        }

        // Refresh onboarding progress after santri update
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.santri.index'))
            ->with('success', 'Data santri berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $santri = Santri::with('parents')->findOrFail((int) $id);
        $this->authorize('delete', $santri);

        DB::transaction(function () use ($santri) {
            // Collect parents BEFORE detaching
            $parents = $santri->parents()->withoutGlobalScopes()->get();

            // Detach pivot rows for this santri
            $santri->parents()->detach();

            // For each parent: if they have no remaining santri, delete them (+ deactivate user)
            foreach ($parents as $parent) {
                $remaining = $parent->santri()->withoutGlobalScopes()->count();
                if ($remaining === 0) {
                    if ($parent->user) {
                        $parent->user->update(['is_active' => false]);
                    }
                    $parent->delete();
                }
            }

            $santri->delete();
        });

        // Refresh onboarding progress after santri deletion
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.santri.index'))
            ->with('success', 'Santri berhasil dihapus.');
    }
}
