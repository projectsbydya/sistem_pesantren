<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\UsageMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UsageMonitoringController extends Controller
{
    public function __construct(
        private UsageMonitoringService $usageService
    ) {}

    /**
     * Show usage monitoring dashboard.
     */
    public function index(Request $request)
    {
        $this->authorize('viewSuperAdminReport', \App\Models\UsageLog::class);

        $tenants = Tenant::withCount(['users', 'santri'])
            ->with('subscriptions.plan')
            ->latest()
            ->paginate(config('usage.per_page', 20));

        // Enhance tenants with usage data
        $tenants->getCollection()->transform(function (Tenant $tenant) {
            $tenant->usage_summary = $this->usageService->getAllUsage($tenant);
            return $tenant;
        });

        $approaching = $this->usageService->getTenantsApproachingLimits();

        return view('dashboard.super-admin.usage.index', [
            'tenants' => $tenants,
            'approachingCount' => $approaching->count(),
        ]);
    }

    /**
     * Show detailed usage for a specific tenant.
     */
    public function show(Tenant $tenant)
    {
        $this->authorize('viewTenantUsage', [\App\Models\UsageLog::class, $tenant]);

        $usage = $this->usageService->getAllUsage($tenant);

        // Get recent usage logs
        $recentLogs = \App\Models\UsageLog::forTenant($tenant->id)
            ->recent(config('usage.recent_days', 30))
            ->latest('recorded_at')
            ->get();

        return view('dashboard.super-admin.usage.show', [
            'tenant' => $tenant,
            'usage' => $usage,
            'recentLogs' => $recentLogs,
        ]);
    }

    /**
     * Get usage report as JSON (for API/AJAX).
     */
    public function report(): JsonResponse
    {
        $this->authorize('viewSuperAdminReport', \App\Models\UsageLog::class);

        $report = $this->usageService->getSuperAdminUsageReport();

        return response()->json([
            'data' => $report,
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Get tenants approaching limits.
     */
    public function approachingLimits(Request $request): JsonResponse
    {
        $this->authorize('viewSuperAdminReport', \App\Models\UsageLog::class);

        $threshold = (float) $request->input('threshold', 80.0);
        $tenants = $this->usageService->getTenantsApproachingLimits($threshold);

        $data = $tenants->map(function (Tenant $tenant) {
            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'usage' => $this->usageService->getAllUsage($tenant),
            ];
        });

        return response()->json([
            'data' => $data,
            'threshold' => $threshold,
        ]);
    }

    /**
     * Record manual usage (for storage or other manual metrics).
     */
    public function record(Request $request, Tenant $tenant)
    {
        $this->authorize('create', \App\Models\UsageLog::class);

        $validated = $request->validate([
            'metric' => ['required', 'string', 'in:' . implode(',', array_keys(config('usage.metrics', [])))],
            'value' => ['required', 'numeric', 'min:0'],
            'metadata' => ['nullable', 'array'],
        ]);

        $this->usageService->recordUsage(
            $tenant,
            $validated['metric'],
            $validated['value'],
            $validated['metadata'] ?? ['manual' => true, 'recorded_by' => auth()->id()]
        );

        return back()->with('success', 'Usage recorded successfully.');
    }
}
