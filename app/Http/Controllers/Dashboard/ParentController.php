<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Exports\CredentialsExport;
use App\Models\Parents;
use App\Models\Santri;
use App\Services\TenantService;
use App\Services\UserProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParentController extends Controller
{
    protected UserProvisioningService $provisioningService;

    public function __construct(UserProvisioningService $provisioningService)
    {
        $this->provisioningService = $provisioningService;
    }

    /**
     * Display parent list for the current tenant.
     */
    public function index()
    {
        $this->authorize('viewAny', Parents::class);
        $user = auth()->user();
        $tenant = TenantService::getTenant();

        // Relation-based filtering: USER -> Parent -> Parents
        $parents = Parents::when($user->parent, function ($query) use ($user) {
            // Parent: can only see themselves
            return $query->where('id', $user->parent->id);
        })->with(['santri', 'user'])
            ->orderBy('name')
            ->get();

        return view('dashboard.parent.index', compact('parents'));
    }

    /**
     * Show form for creating a new parent.
     */
    public function create()
    {
        $this->authorize('create', Parents::class);
        $santris = Santri::orderBy('name')->get();
        return view('dashboard.parent.create', compact('santris'));
    }

    /**
     * Store a newly created parent.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Parents::class);
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|unique:parents,email',
            'address'      => 'nullable|string',
            'relationship' => 'required|in:father,mother,guardian',
            'santri_ids'   => 'nullable|array',
            'santri_ids.*' => 'exists:santri,id',
            'is_primary'   => 'nullable|boolean',
        ]);

        $tenant = TenantService::getTenant();

        try {
            $credentials = DB::transaction(function () use ($data, $tenant) {
                // 1. Provision Parent user FIRST — user_id never null after commit
                $userResult = $this->provisioningService->provisionNewParent([
                    'name'      => $data['name'],
                    'tenant_id' => $tenant->id,
                    'email'     => $data['email'] ?? null,
                ]);

                // 2. Create Parent record with user_id already set
                $parent = Parents::create([
                    'tenant_id'    => $tenant->id,
                    'name'         => $data['name'],
                    'phone'        => $data['phone'] ?? null,
                    'email'        => $data['email'] ?? null,
                    'address'      => $data['address'] ?? null,
                    'relationship' => $data['relationship'],
                    'is_active'    => true,
                    'user_id'      => $userResult['user']->id,
                ]);

                // 3. Link to santri if provided
                if (!empty($data['santri_ids'])) {
                    foreach ($data['santri_ids'] as $santriId) {
                        $parent->santri()->attach($santriId, [
                            'relationship' => $data['relationship'],
                            'is_primary'   => $data['is_primary'] ?? false,
                        ]);

                        if ($data['is_primary'] ?? false) {
                            Santri::where('id', $santriId)->update(['parent_id' => $parent->id]);
                        }
                    }
                }

                return [
                    'name'     => $data['name'],
                    'email'    => $userResult['user']->email,
                    'password' => $userResult['password'],
                    'role'     => 'Orang Tua',
                ];
            });

            session()->put('credentials_data', ['parent' => $credentials]);
            session()->put('credentials_parent_name', $data['name']);

            return redirect(tenant_route('dashboard.parent.index'))
                ->with('success', "Orang tua {$data['name']} berhasil ditambahkan dengan akun login.")
                ->with('show_credentials_download', true);

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menambahkan orang tua: ' . $e->getMessage());
        }
    }

    /**
     * Display parent details.
     */
    public function show($id)
    {
        $parent = Parents::with(['santri', 'user'])->findOrFail((int) $id);
        $this->authorize('view', $parent);

        return view('dashboard.parent.show', compact('parent'));
    }

    /**
     * Show form for editing parent.
     */
    public function edit($id)
    {
        $parent = Parents::with('santri', 'user')->findOrFail((int) $id);
        $this->authorize('update', $parent);
        $santris = Santri::orderBy('name')->get();
        $linkedSantriIds = $parent->santri->pluck('id')->toArray();

        return view('dashboard.parent.edit', compact('parent', 'santris', 'linkedSantriIds'));
    }

    /**
     * Update parent data.
     */
    public function update(Request $request, $id)
    {
        $parent = Parents::findOrFail((int) $id);
        $this->authorize('update', $parent);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:20',
            'email'        => 'nullable|email|unique:parents,email,' . $parent->id,
            'address'      => 'nullable|string',
            'relationship' => 'required|in:father,mother,guardian',
            'santri_ids'   => 'nullable|array',
            'santri_ids.*' => 'exists:santri,id',
            'is_primary'   => 'nullable|boolean',
            'is_active'    => 'nullable|boolean',
        ]);

        try {
            DB::transaction(function () use ($data, $parent) {
                // Update parent
                $parent->update([
                    'name'         => $data['name'],
                    'phone'        => $data['phone'] ?? null,
                    'email'        => $data['email'] ?? null,
                    'address'      => $data['address'] ?? null,
                    'relationship' => $data['relationship'],
                    'is_active'    => $data['is_active'] ?? true,
                ]);

                // Sync santri relationships
                $syncData = [];
                if (!empty($data['santri_ids'])) {
                    foreach ($data['santri_ids'] as $santriId) {
                        $syncData[$santriId] = [
                            'relationship' => $data['relationship'],
                            'is_primary'   => $data['is_primary'] ?? false,
                        ];

                        // Update santri's primary parent if this is primary
                        if ($data['is_primary'] ?? false) {
                            Santri::where('id', $santriId)->update(['parent_id' => $parent->id]);
                        }
                    }
                }
                $parent->santri()->sync($syncData);
            });

            return redirect(tenant_route('dashboard.parent.index'))
                ->with('success', 'Data orang tua berhasil diperbarui.');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Delete parent record.
     */
    public function destroy($id)
    {
        $parent = Parents::findOrFail((int) $id);
        $this->authorize('delete', $parent);
        $name = $parent->name;

        // Check if parent has linked user
        if ($parent->user) {
            // Soft delete user or handle appropriately
            $parent->user->update(['is_active' => false]);
        }

        $parent->delete();

        return redirect(tenant_route('dashboard.parent.index'))
            ->with('success', "Orang tua {$name} berhasil dihapus.");
    }

    /**
     * Export credentials to CSV
     */
    public function downloadCredentials()
    {
        $credentials = session('credentials_data');
        $parentName  = session('credentials_parent_name', 'orang-tua');

        if (empty($credentials)) {
            return redirect(tenant_route('dashboard.parent.index'))
                ->with('error', 'Data credentials tidak ditemukan atau sudah diunduh.');
        }

        session()->forget(['credentials_data', 'credentials_parent_name']);

        $filename = 'akun-login-' . Str::slug($parentName) . '-' . now()->format('Ymd-His') . '.xlsx';
        $loginUrl = main_domain_url('/login');

        return Excel::download(new CredentialsExport($credentials, $loginUrl), $filename);
    }
}
