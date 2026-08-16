<?php

namespace App\Http\Middleware;

use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;

/**
 * Resolves and binds the current tenant from the subdomain BEFORE any
 * controller executes. Must run BEFORE auth middleware so that
 * TenantScope is ready when the authenticatable user is loaded.
 *
 * Route::domain('{tenant}.' . config('app.app_domain'))->group(...) binds {tenant}
 * as a route parameter. This middleware picks that up and uses the
 * existing TenantService resolution pipeline.
 *
 * Replaces the previous X-Tenant-ID / session-injection approach for
 * all subdomain-routed requests. Central (non-subdomain) routes still
 * use the session-based ResolveTenantFromSession middleware.
 */
class InitializeTenantFromSubdomain
{
    public function handle(Request $request, Closure $next)
    {
        // Always clear first — prevents cross-request state bleed
        TenantService::clear();

        // The {tenant} route parameter is the subdomain slug
        $slug = $request->route('tenant');

        if (!$slug) {
            // Fallback: try to resolve from host directly (covers API + test scenarios)
            TenantService::resolve($request);
        } else {
            $tenant = \App\Models\Tenant::where('slug', $slug)->first();
            if ($tenant) {
                TenantService::setTenant($tenant);
            }
        }

        $tenant  = TenantService::getTenant();
        $user    = auth()->user();

        if (!$tenant) {
            // Super admin may operate without tenant context (cross-tenant tools)
            if ($user?->is_super_admin) {
                return $next($request);
            }

            $message = 'Anda tidak memiliki akses ke tenant ini.';

            // Unauthenticated request with no tenant
            if (!$user) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Unauthenticated.'], 401);
                }
                return redirect()->route('login');
            }

            // Authenticated user, no valid tenant → 403
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }
            abort(403, $message);
        }

        $validation = TenantService::validateTenantAccess($tenant);
        if (!$validation['canAccess']) {
            abort($validation['status'], $validation['error']);
        }

        return $next($request);
    }
}
