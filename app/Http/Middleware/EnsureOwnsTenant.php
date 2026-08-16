<?php

namespace App\Http\Middleware;

use App\Models\Santri;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure user can only access their own tenant data
 * Also validates tenant exists and is active
 */
class EnsureOwnsTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admin must NEVER access operational tenant routes.
        // Clear any injected tenant session to prevent bypass, then redirect.
        if ($user->isSuperAdmin()) {
            session()->forget('tenant_id');
            return redirect()->route('dashboard.super-admin.index');
        }

        // Get current tenant from session or user
        $tenantId = session('tenant_id') ?? $user->tenant_id;

        if (!$tenantId) {
            abort(403, 'Tidak ada tenant yang aktif.');
        }

        // Validate user owns this tenant
        if ((int) $user->tenant_id !== (int) $tenantId) {
            abort(403, 'Anda tidak memiliki akses ke tenant ini.');
        }

        // Validate tenant exists
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            abort(404, 'Tenant tidak ditemukan.');
        }

        // Use centralized validation for active/trial checks
        $validation = \App\Services\TenantService::validateTenantAccess($tenant);

        if (!$validation['canAccess']) {
            abort($validation['status'], $validation['error']);
        }

        return $next($request);
    }
}
