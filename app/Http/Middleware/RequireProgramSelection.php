<?php

namespace App\Http\Middleware;

use App\Services\TenantSetupService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Progressive Setup Middleware
 *
 * Only requires program selection to access dashboard.
 * Individual features validate their own dependencies.
 * setup_status is informational only, not blocking.
 */
class RequireProgramSelection
{
    /**
     * Routes that are allowed without program selection.
     */
    protected array $exemptRoutes = [
        // Onboarding flow
        'dashboard.onboarding.welcome',
        'dashboard.onboarding.programs',
        'dashboard.onboarding.programs.store',
        'dashboard.onboarding.setup-guide',
        'dashboard.onboarding.skip',
        'dashboard.onboarding.complete-step',
        'dashboard.onboarding.progress',
        // Profile and settings
        'dashboard.profile',
        'dashboard.profile.update',
        'password.change',
        'password.update-first',
    ];

    /**
     * Handle an incoming request.
     *
     * Only blocks if tenant has not selected any program.
     * setup_status is NOT used for blocking.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Skip for super admins
        if ($user && $user->is_super_admin) {
            return $next($request);
        }

        // Skip for users without tenant
        if (!$user || !$user->tenant_id) {
            return $next($request);
        }

        $currentRoute = $request->route()?->getName();

        // Skip for exempt routes
        if (in_array($currentRoute, $this->exemptRoutes)) {
            return $next($request);
        }

        try {
            $tenantId = $user->tenant_id;

            // ONLY check: has selected programs
            // setup_status is informational only, NOT blocking
            if (!TenantSetupService::hasSelectedPrograms($tenantId)) {
                Log::info('Redirecting to program selection', [
                    'user_id' => $user->id,
                    'tenant_id' => $tenantId,
                    'route' => $currentRoute,
                ]);

                return redirect()
                    ->route('dashboard.onboarding.welcome')
                    ->with('info', 'Silakan pilih program untuk pesantren Anda terlebih dahulu.');
            }

            // All other access is allowed
            // Individual features validate their own dependencies
            return $next($request);

        } catch (\Exception $e) {
            Log::warning('Failed to check program selection: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
            ]);

            // If we can't check, let the request through to avoid lockout
            return $next($request);
        }
    }
}
