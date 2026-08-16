<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Super Admin Tenant Management Controller
 *
 * Handles creation, management, and lifecycle of tenants.
 * Super Admin can create tenants but CANNOT access tenant operational data.
 */
class TenantManagementController extends Controller
{
    /**
     * Store a newly created tenant.
     * Auto-generates secure password and redirects to credential display page.
     */
    public function store(Request $request, TenantProvisioningService $provisioningService)
    {
        // Validate input
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],           // Pesantren name
            'admin_name'     => ['required', 'string', 'max:255'],             // Admin display name
            'trial_days'     => ['nullable', 'integer', 'min:0', 'max:365'],   // Trial period
            'is_active'      => ['nullable', 'boolean'],                     // Active flag
        ]);

        // Auto-generate a unique admin email based on the tenant name.
        $baseSlug = Str::slug($validated['name']);
        $domain   = config('app.app_domain');
        $email    = "{$baseSlug}@{$domain}";
        $counter  = 1;

        while (User::where('email', $email)->exists()) {
            $email = "{$baseSlug}-{$counter}@{$domain}";
            $counter++;
        }

        // Prepare provisioning data
        $provisionData = [
            'name'           => $validated['admin_name'],
            'email'          => $email,
            'pesantren_name' => $validated['name'],
            'plan'           => 'trial',
        ];

        // Override trial days if specified
        if (!empty($validated['trial_days'])) {
            $provisionData['trial_days'] = (int) $validated['trial_days'];
        }

        try {
            // Provision tenant and admin user
            $result = $provisioningService->provision($provisionData);

            // Update tenant active status if explicitly set to false
            if (isset($validated['is_active']) && $validated['is_active'] === false) {
                $result['tenant']->update(['is_active' => false]);
            }

            // Redirect to credential display page (one-time only!)
            return redirect()->route('dashboard.super-admin.tenants.credentials', [
                'tenant' => $result['tenant']->id,
            ])->with([
                'credentials' => [
                    'tenant_name'    => $result['tenant']->name,
                    'admin_email'    => $result['user']->email,
                    'admin_password' => $result['plain_password'], // ONE-TIME DISPLAY
                ],
                'warning' => 'Simpan informasi login ini dengan aman. Password tidak akan ditampilkan lagi!',
            ]);

        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Gagal membuat tenant: ' . $e->getMessage());
        }
    }

    /**
     * Display the credentials page (one-time password display).
     * This page should be shown immediately after tenant creation.
     */
    public function showCredentials(Tenant $tenant)
    {
        // Check if we have credentials in session (one-time display)
        $credentials = session('credentials');

        if (!$credentials) {
            // Credentials already shown, redirect to tenant list
            return redirect()->route('dashboard.super-admin.tenants.index')
                ->with('info', 'Kredensial sudah ditampilkan sebelumnya. Jika perlu reset password, gunakan fitur reset password.');
        }

        return view('dashboard.super-admin.tenants.credentials', [
            'tenant'      => $tenant,
            'credentials' => $credentials,
        ]);
    }

    /**
     * Regenerate password for a tenant admin.
     * Use this when the initial password was lost.
     */
    public function resetAdminPassword(Request $request, Tenant $tenant, TenantProvisioningService $service)
    {
        $adminUser = $tenant->users()->where('role', 'admin')->first();

        if (!$adminUser) {
            return back()->with('error', 'Tenant tidak memiliki admin user.');
        }

        // Generate new password
        $newPassword = $service->generateSecurePassword();

        // Update user: set new password and force change on next login
        $adminUser->update([
            'password' => bcrypt($newPassword),
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);

        // Redirect to credential display page
        return redirect()->route('dashboard.super-admin.tenants.credentials', [
            'tenant' => $tenant->id,
        ])->with([
            'credentials' => [
                'tenant_name'    => $tenant->name,
                'admin_email'    => $adminUser->email,
                'admin_password' => $newPassword,
            ],
            'warning' => 'Password baru telah dibuat. Simpan dengan aman!',
        ]);
    }
}
