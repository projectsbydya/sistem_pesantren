<?php

use App\Helpers\TenantUrlHelper;
use App\Services\TenantService;
use Illuminate\Support\Facades\URL;

/**
 * Generate an absolute URL on the main domain (no subdomain).
 *
 * Uses config('app.app_domain') and config('app.scheme') — no hardcoded domains.
 * Designed for logout form actions, cross-domain redirects, and Blade links.
 *
 * Usage in Blade: action="{{ main_domain_url('/logout') }}"
 *                 href="{{ main_domain_url('/login') }}"
 *
 * @param string $path  Path to append (e.g. '/login', '/logout')
 * @return string Absolute URL e.g. http://pesantren.test/login
 */
if (!function_exists('main_domain_url')) {
    function main_domain_url(string $path = ''): string
    {
        return TenantUrlHelper::mainDomainUrl($path);
    }
}

/**
 * Generate a tenant-aware route URL.
 *
 * Automatically selects subdomain vs session-based route variant:
 * - Subdomain context (URL::defaults has 'tenant') → uses route name as-is with {tenant} injected
 * - Session context (no URL default) → uses route name + '.session' suffix
 *
 * Usage: tenant_route('dashboard.santri.index')
 *        tenant_route('dashboard.santri.edit', ['id' => 5])
 *        tenant_route('dashboard.santri.edit', 5)
 */
if (!function_exists('tenant_route')) {
    function tenant_route(string $name, array|int|string $params = [], bool $absolute = false): string
    {
        // A scalar shorthand (e.g. tenant_route('...', 5) or tenant_route('...', 'diniyah'))
        // is mapped to the target route's real parameter name once the route is resolved.
        $scalar = null;
        if (!is_array($params)) {
            $scalar = $params;
            $params = [];
        }

        // Map the scalar shorthand onto the route's first relevant parameter.
        // Prefers 'id' for backward compatibility; otherwise uses the first
        // non-tenant parameter (e.g. 'programSlug' for /akademik/{programSlug}/...).
        $mapScalar = function (string $routeName, array $params) use ($scalar) {
            if ($scalar === null) {
                return $params;
            }

            $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName($routeName);
            if (!$route) {
                $params['id'] = $scalar;
                return $params;
            }

            $names = $route->parameterNames();
            if (in_array('id', $names) && !array_key_exists('id', $params)) {
                $params['id'] = $scalar;
                return $params;
            }

            foreach ($names as $p) {
                if ($p !== 'tenant' && !array_key_exists($p, $params)) {
                    $params[$p] = $scalar;
                    return $params;
                }
            }

            return $params;
        };

        // Detect subdomain context: URL::defaults has 'tenant' (set by ResolveTenant)
        $urlDefaults = URL::getDefaultParameters();
        $tenantSlug = $urlDefaults['tenant'] ?? TenantService::getTenant()?->slug;

        if (!empty($urlDefaults['tenant'])) {
            // Check if the route definition actually has {tenant} parameter
            $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName($name);
            $params = $mapScalar($name, $params);

            if ($route && in_array('tenant', $route->parameterNames())) {
                // Subdomain route: inject {tenant} param for route() to fill the domain segment
                $params['tenant'] = $tenantSlug;
                return route($name, $params, $absolute);
            }

            // Route doesn't expect {tenant} param, use URL::defaults to handle it
            return route($name, $params, $absolute);
        }

        // Session-based access: use .session variant (no {tenant} param needed)
        $sessionName = $name . '.session';
        if (\Illuminate\Support\Facades\Route::has($sessionName)) {
            return route($sessionName, $mapScalar($sessionName, $params), $absolute);
        }

        // Fallback: try original name (may work if route has no {tenant} param)
        return route($name, $mapScalar($name, $params), $absolute);
    }
}
