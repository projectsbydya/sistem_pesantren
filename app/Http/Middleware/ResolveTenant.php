<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Single, canonical tenant resolution middleware.
 *
 * Clean SaaS architecture: tenant is always resolved from the HTTP host first.
 * The subdomain IS the tenant identifier — no route parameter injection needed.
 * Route::domain('{tenant}...') is kept only for URL matching; the {tenant} param
 * is NEVER used for resolution and controllers NEVER receive it.
 *
 * Resolution priority (first match wins):
 *   1. HTTP host subdomain  — {slug}.pesantren.test (canonical)
 *   2. Custom domain match  — tenant.domain column
 *   3. X-Tenant-ID header   — API clients and test helpers
 *   4. Session tenant_id    — session-based routes and test suite
 *   5. Authenticated user's own tenant_id — fresh-session fallback
 *
 * HTTP responses on failure:
 *   404 — subdomain slug not found in DB
 *   403 — tenant exists but inactive/expired OR user doesn't own it
 *   401 — no tenant resolved AND user is unauthenticated
 *   pass-through — super admin without tenant (cross-tenant tooling)
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next)
    {
        // Always clear first — prevents static state bleed between requests
        TenantService::clear();

        $tenant = $this->resolve($request);

        if ($tenant !== null) {
            TenantService::setTenant($tenant);

            // Set URL defaults so route() calls auto-fill {tenant} in subdomain route helpers
            URL::defaults(['tenant' => $tenant->slug]);

            // Strip {tenant} from route parameter bag.
            // Route::domain('{tenant}...') captures the subdomain as a route param, which
            // Laravel injects as the FIRST positional argument into every controller method.
            // Stripping it here ensures show($id) receives the numeric ID, not the slug.
            if ($request->route()?->hasParameter('tenant')) {
                $request->route()->forgetParameter('tenant');
            }

            // Validate active + trial status (403 if suspended/expired)
            $validation = TenantService::validateTenantAccess($tenant);
            if (!$validation['canAccess']) {
                return $this->deny($request, $validation['error'], $validation['status']);
            }

            return $next($request);
        }

        // ---- No tenant resolved ----
        $user = auth()->user();

        // Super admin may operate without tenant context (cross-tenant tooling)
        if ($user?->is_super_admin) {
            return $next($request);
        }

        // Unauthenticated with no tenant → 401
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Authenticated user, no resolvable tenant → 403
        return $this->deny($request, 'Anda tidak memiliki akses ke tenant ini.', 403);
    }

    // -------------------------------------------------------------------------

    /**
     * Resolve tenant using the priority chain.
     * Returns null when no tenant can be found — caller decides the error response.
     */
    private function resolve(Request $request): ?Tenant
    {
        // 1 & 2. HTTP host — canonical SaaS source of truth.
        //   {slug}.pesantren.test  → subdomain match
        //   custom.sch.id          → custom domain match
        $host = $this->parseHost($request);
        if ($host) {
            $appDomain = config('app.app_domain'); // e.g. pesantren.test

            // Subdomain: {slug}.pesantren.test
            if (str_ends_with($host, '.' . $appDomain)) {
                $slug = substr($host, 0, -(strlen($appDomain) + 1));
                if ($slug && $slug !== 'www') {
                    $tenant = Tenant::where('slug', $slug)->first();
                    if ($tenant) {
                        return $tenant;
                    }
                    // Sub-domain of our app but slug unknown → hard 404
                    abort(404, "Pesantren '{$slug}' tidak ditemukan.");
                }
            }

            // Custom domain: exact match on tenant.domain column
            $tenant = Tenant::where('domain', $host)->first();
            if ($tenant) {
                return $tenant;
            }

            // Test environment fallback: {slug}.localhost
            if (app()->environment('testing') && str_ends_with($host, '.localhost')) {
                $slug = substr($host, 0, -10); // Remove '.localhost'
                if ($slug && $slug !== 'www') {
                    $tenant = Tenant::where('slug', $slug)->first();
                    if ($tenant) {
                        return $tenant;
                    }
                }
            }

            // NO FALLBACKS - if host doesn't match our domain patterns, return null
        }

        // 3. X-Tenant-ID header — API clients and test helpers
        //    Non-existent ID → null (caller issues 403, not 404)
        $headerId = $request->header('X-Tenant-ID');
        if ($headerId && is_numeric($headerId)) {
            return Tenant::find((int) $headerId);
        }

        // 4. Session — web UI after login (session-based routes, test suite)
        $sessionId = session('tenant_id');
        if ($sessionId && is_numeric($sessionId)) {
            $tenant = Tenant::find((int) $sessionId);
            if ($tenant) {
                return $tenant;
            }
            // Stale session value — clear and fall through
            session()->forget('tenant_id');
        }

        // 5. Authenticated user's own tenant_id — fresh-session fallback
        $user = auth()->user();
        if ($user && $user->tenant_id) {
            $tenant = Tenant::find($user->tenant_id);
            if ($tenant) {
                session(['tenant_id' => $tenant->id]);
                return $tenant;
            }
        }

        return null;
    }

    /**
     * Extract the bare hostname from the request, stripping port numbers.
     */
    private function parseHost(Request $request): ?string
    {
        $raw  = $request->server('HTTP_HOST') ?: $request->getHost();
        $host = strtolower(preg_replace('/:\d+$/', '', trim($raw)));

        if (!$host || strlen($host) > 253) {
            return null;
        }

        if (!preg_match('/^[a-z0-9.\-]+$/', $host)) {
            return null;
        }

        return $host;
    }

    private function deny(Request $request, string $message, int $status)
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }
        abort($status, $message);
    }
}
