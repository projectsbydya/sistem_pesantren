<?php

namespace App\Http\Middleware;

use App\Services\OnboardingStepRegistry;
use App\Services\TenantSetupService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RedirectIfOnboardingIncomplete
{
    /**
     * Routes that are allowed during onboarding setup.
     * These are the routes for completing each setup step.
     */
    protected array $setupRoutes = [
        // Onboarding flow
        'dashboard.onboarding.welcome',
        'dashboard.onboarding.programs',
        'dashboard.onboarding.programs.store',
        'dashboard.onboarding.setup-guide',
        'dashboard.onboarding.skip',
        'dashboard.onboarding.complete-step',
        'dashboard.onboarding.progress',
        'dashboard.onboarding.wizard',
        'dashboard.onboarding.wizard.store-kelas',
        'dashboard.onboarding.wizard.store-mapel',
        'dashboard.onboarding.wizard.store-ustadz',
        'dashboard.onboarding.wizard.store-penugasan',
        'dashboard.onboarding.wizard.store-jadwal',
        'dashboard.onboarding.wizard.skip-step',
        'dashboard.onboarding.wizard.ringkasan',
        // Kelas setup
        'dashboard.akademik.kelas.index',
        'dashboard.akademik.kelas.create',
        'dashboard.akademik.kelas.store',
        'dashboard.akademik.kelas.edit',
        'dashboard.akademik.kelas.update',
        'dashboard.akademik.kelas.destroy',
        'dashboard.kelas.create',
        'dashboard.kelas.store',
        // Subject setup
        'dashboard.akademik.subjects.index',
        'dashboard.akademik.subjects.create',
        'dashboard.akademik.subjects.store',
        'dashboard.akademik.subjects.edit',
        'dashboard.akademik.subjects.update',
        'dashboard.akademik.subjects.destroy',
        'dashboard.subjects.create',
        'dashboard.subjects.store',
        // Ustadz setup (Teacher — now required before Teaching Assignment)
        // Both the session-prefixed and subdomain route names are whitelisted
        // since the 'onboarding' middleware is applied to both route groups.
        'dashboard.ustadz.index.session',
        'dashboard.ustadz.create.session',
        'dashboard.ustadz.store.session',
        'dashboard.ustadz.edit.session',
        'dashboard.ustadz.update.session',
        'dashboard.ustadz.index',
        'dashboard.ustadz.create',
        'dashboard.ustadz.store',
        'dashboard.ustadz.edit',
        'dashboard.ustadz.update',
        // Penugasan / Teaching Assignment setup
        'dashboard.akademik.penugasan.index',
        'dashboard.akademik.penugasan.create',
        'dashboard.akademik.penugasan.store',
        'dashboard.akademik.penugasan.edit',
        'dashboard.akademik.penugasan.update',
        // Jadwal setup
        'dashboard.akademik.jadwal.index',
        'dashboard.akademik.jadwal.create',
        'dashboard.akademik.jadwal.store',
        'dashboard.akademik.jadwal.edit',
        'dashboard.akademik.jadwal.update',
        'dashboard.akademik.jadwal.destroy',
        // Note: Santri remains a POST-onboarding step, not required here.
        // Note: Operational routes (absensi, nilai, elearning, materi, raport)
        // require setup_status = 'siap_operasional' and are NOT whitelisted here
    ];

    /**
     * Handle an incoming request.
     *
     * Redirects to appropriate setup step based on onboarding status.
     * Tenant must have setup_status = 'siap_operasional' to access dashboard.
     * Super admins are exempt from this check.
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

        // Skip for setup routes themselves
        if (in_array($currentRoute, $this->setupRoutes)) {
            return $next($request);
        }

        try {
            $tenantId = $user->tenant_id;
            $progress = TenantSetupService::getProgress($tenantId);

            // If already siap operasional, allow access
            if ($progress->setup_status === 'siap_operasional') {
                return $next($request);
            }

            // Auto-update steps based on current system state
            TenantSetupService::autoUpdateSteps($tenantId);
            $progress->refresh();

            // Determine redirect based on next incomplete step
            $redirect = $this->getRedirectForIncompleteStep($progress, $tenantId);

            Log::info('Redirecting user to setup step', [
                'user_id' => $user->id,
                'tenant_id' => $tenantId,
                'route' => $currentRoute,
                'setup_status' => $progress->setup_status,
                'redirect_to' => $redirect['route'],
            ]);

            $params = $redirect['params'] ?? [];
            return redirect()->route($redirect['route'], $params)
                ->with('info', $redirect['message']);

        } catch (\Exception $e) {
            Log::warning('Failed to check onboarding status: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'tenant_id' => $user->tenant_id,
            ]);
            // If we can't check, let the request through to avoid lockout
            return $next($request);
        }
    }

    /**
     * Determine redirect target based on the next incomplete required setup step.
     *
     * Order comes entirely from OnboardingStepRegistry (config/onboarding.php):
     * Program → Kelas → Mata Pelajaran → Ustadz → Penugasan Mengajar → Jadwal.
     * Santri remains a POST-onboarding step — it does NOT block access.
     */
    private function getRedirectForIncompleteStep($progress, int $tenantId): array
    {
        // Program selection is the foundation and precedes the wizard flow.
        if (!TenantSetupService::hasSelectedPrograms($tenantId)) {
            return [
                'route'   => 'dashboard.onboarding.welcome',
                'message' => 'Silakan pilih program untuk pesantren Anda terlebih dahulu.',
            ];
        }

        $programSlug = TenantSetupService::getFirstActiveProgram($tenantId)?->slug;
        $actualProgress = TenantSetupService::getActualProgress($tenantId);

        $nextKey = OnboardingStepRegistry::firstIncompleteKey($actualProgress, $programSlug);

        if ($nextKey) {
            $def = OnboardingStepRegistry::find($nextKey, $programSlug);

            return [
                'route'   => 'dashboard.onboarding.wizard',
                'message' => 'Silakan lengkapi langkah "' . ($def['title'] ?? $def['label']) . '" untuk melanjutkan.',
                'params'  => ['step' => $nextKey],
            ];
        }

        // All required steps complete — allow access.
        return [
            'route'   => 'dashboard.index',
            'message' => 'Setup pesantren selesai. Selamat datang!',
        ];
    }
}
