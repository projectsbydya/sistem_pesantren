<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Exports\CredentialsExport;
use App\Models\Ustadz;
use App\Models\User;
use App\Services\TenantService;
use App\Services\TenantSetupService;
use App\Services\UserProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Subject;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UstadzController extends Controller
{
    protected UserProvisioningService $provisioningService;

    public function __construct(UserProvisioningService $provisioningService)
    {
        $this->provisioningService = $provisioningService;
    }
    /**
     * Display ustadz list for the current tenant.
     */
    public function index()
    {
        $this->authorize('viewAny', Ustadz::class);

        $ustadz = Ustadz::with(['user', 'subjects'])->orderBy('created_at', 'desc')->get();

        return view('dashboard.ustadz.index', compact('ustadz'));
    }

    /**
     * Show form for creating a new ustadz.
     */
    public function create()
    {
        $this->authorize('create', Ustadz::class);

        $subjects = $this->activeSubjects();

        return view('dashboard.ustadz.create', compact('subjects'));
    }

    /**
     * Store a newly created ustadz.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Ustadz::class);

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|unique:users,email',
            'phone'          => 'nullable|string|max:20',
            'subject_ids'    => ['nullable', 'array'],
            'subject_ids.*'  => [
                'integer',
                Rule::exists('subjects', 'id')
                    ->where('tenant_id', tenant_id())
                    ->whereIn('program_id', $this->activeProgramIds()),
            ],
            'bio'            => 'nullable|string',
            'role'           => 'nullable|in:pengajar,wali_kelas,kepala_ponpes,bendahara,admin',
            'jam_per_minggu' => 'nullable|integer|min:0|max:40',
            'performa'       => 'nullable|integer|min:0|max:100',
            'status'         => 'nullable|in:active,inactive,cuti',
        ]);

        $tenant = TenantService::getTenant();
        $tenantId = $tenant->id;

        try {
            $credentials = DB::transaction(function () use ($data, $tenant, $tenantId) {
                // 1. Create User first — user-first ordering ensures user_id is never null
                $userResult = $this->provisioningService->provisionNewUstadz([
                    'name'      => $data['name'],
                    'tenant_id' => $tenant->id,
                    'email'     => $data['email'] ?? null,
                ]);

                // 2. Create Ustadz record with user_id set from the start
                $ustadz = Ustadz::create([
                    'tenant_id'      => $tenant->id,
                    'user_id'        => $userResult['user']->id,
                    'phone'          => $data['phone'] ?? null,
                    'bio'            => $data['bio'] ?? null,
                    'role'           => $data['role'] ?? null,
                    'jam_per_minggu' => $data['jam_per_minggu'] ?? null,
                    'performa'       => $data['performa'] ?? null,
                    'status'         => $data['status'] ?? Ustadz::STATUS_ACTIVE,
                ]);

                $ustadz->subjects()->sync($data['subject_ids'] ?? []);

                return [
                    'name'     => $userResult['user']->name,
                    'email'    => $userResult['user']->email,
                    'password' => $userResult['password'],
                    'role'     => 'Ustadz',
                ];
            });

            session()->put('credentials_data', ['ustadz' => $credentials]);
            session()->put('credentials_ustadz_name', $data['name']);

            // Refresh onboarding progress after ustadz creation
            TenantSetupService::refreshProgress();

            // Redirect back to create form with success message for CTA
            return redirect(tenant_route('dashboard.ustadz.create'))
                ->with('success', "Ustadz {$data['name']} berhasil ditambahkan dengan akun login. Silakan buat penugasan mengajar jika diperlukan.");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan ustadz: ' . $e->getMessage());
        }
    }

    /**
     * Export credentials to CSV
     */
    public function downloadCredentials()
    {
        $this->authorize('downloadCredentials', Ustadz::class);

        $credentials = session('credentials_data');
        $ustadzName  = session('credentials_ustadz_name', 'ustadz');

        if (empty($credentials)) {
            return redirect(tenant_route('dashboard.ustadz.index'))
                ->with('error', 'Data credentials tidak ditemukan atau sudah diunduh.');
        }

        session()->forget(['credentials_data', 'credentials_ustadz_name']);

        $filename = 'akun-login-' . Str::slug($ustadzName) . '-' . now()->format('Ymd-His') . '.xlsx';
        $loginUrl = main_domain_url('/login');

        return Excel::download(new CredentialsExport($credentials, $loginUrl), $filename);
    }

    /**
     * Show ustadz detail.
     */
    public function show($id)
    {
        $ustadz = Ustadz::with(['user', 'subjects'])->findOrFail((int) $id);
        $this->authorize('view', $ustadz);

        return view('dashboard.ustadz.show', compact('ustadz'));
    }

    /**
     * Show form for editing ustadz.
     */
    public function edit($id)
    {
        $ustadz = Ustadz::with(['user', 'subjects'])->findOrFail((int) $id);
        $this->authorize('update', $ustadz);

        $subjects = $this->activeSubjects();

        return view('dashboard.ustadz.edit', compact('ustadz', 'subjects'));
    }

    /**
     * Update ustadz data.
     */
    public function update(Request $request, $id)
    {
        $ustadz = Ustadz::with(['user', 'subjects'])->findOrFail((int) $id);
        $this->authorize('update', $ustadz);

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'phone'          => 'nullable|string|max:20',
            'subject_ids'    => ['nullable', 'array'],
            'subject_ids.*'  => [
                'integer',
                Rule::exists('subjects', 'id')
                    ->where('tenant_id', tenant_id())
                    ->whereIn('program_id', $this->activeProgramIds()),
            ],
            'bio'            => 'nullable|string',
            'role'           => 'nullable|in:pengajar,wali_kelas,kepala_ponpes,bendahara,admin',
            'jam_per_minggu' => 'nullable|integer|min:0|max:40',
            'performa'       => 'nullable|integer|min:0|max:100',
            'status'         => 'nullable|in:active,inactive,cuti',
        ]);

        if ($ustadz->user_id && $ustadz->user) {
            $ustadz->user->update(['name' => $data['name']]);
        }

        $ustadz->update([
            'bio'            => $data['bio'],
            'phone'          => $data['phone'] ?? $ustadz->phone,
            'role'           => $data['role'] ?? $ustadz->role,
            'jam_per_minggu' => $data['jam_per_minggu'],
            'performa'       => $data['performa'],
            'status'         => $data['status'] ?? $ustadz->status,
        ]);

        $ustadz->subjects()->sync($data['subject_ids'] ?? []);

        // Refresh onboarding progress after ustadz update
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.ustadz.index'))
            ->with('success', 'Data ustadz berhasil diperbarui.');
    }

    /**
     * Delete ustadz record.
     */
    public function destroy($id)
    {
        $ustadz = Ustadz::with('user')->findOrFail((int) $id);
        $this->authorize('delete', $ustadz);

        $name = $ustadz->user?->name ?? 'Ustadz';

        if ($ustadz->user) {
            $ustadz->user->update(['is_active' => false]);
        }

        $ustadz->delete();

        // Refresh onboarding progress after ustadz deletion
        TenantSetupService::refreshProgress();

        return redirect(tenant_route('dashboard.ustadz.index'))
            ->with('success', "Ustadz {$name} berhasil dihapus.");
    }

    /**
     * Active program IDs for the current tenant.
     */
    private function activeProgramIds(): array
    {
        $tenant = TenantService::getTenant();

        return $tenant?->activePrograms()->pluck('programs.id')->all() ?? [];
    }

    /**
     * Active subjects for the current tenant and its active programs.
     */
    private function activeSubjects()
    {
        $activeProgramIds = $this->activeProgramIds();

        if (empty($activeProgramIds)) {
            return collect();
        }

        return Subject::whereIn('program_id', $activeProgramIds)
            ->orderBy('name')
            ->get();
    }
}
