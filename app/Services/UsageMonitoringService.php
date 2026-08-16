<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\UsageLimitExceededException;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\UsageLog;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsageMonitoringService
{
    /**
     * Get metric to plan column mapping from config.
     */
    private function getMetricToPlanColumn(): array
    {
        $metrics = config('usage.metrics', []);
        return collect($metrics)
            ->mapWithKeys(fn ($config, $metric) => [$metric => $config['plan_column']])
            ->toArray();
    }

    /**
     * Get current usage for a specific metric.
     */
    public function getCurrentUsage(Tenant $tenant, string $metric): int|float
    {
        return match ($metric) {
            'user_count' => $this->getUserCount($tenant),
            'santri_count' => $this->getSantriCount($tenant),
            'branch_count' => $this->getBranchCount($tenant),
            'storage_usage_mb' => $this->getStorageUsage($tenant),
            default => $this->getLatestLoggedUsage($tenant, $metric),
        };
    }

    /**
     * Get all usage metrics for a tenant.
     */
    public function getAllUsage(Tenant $tenant): array
    {
        $metrics = array_keys($this->getMetricToPlanColumn());
        $usage = [];

        foreach ($metrics as $metric) {
            $usage[$metric] = [
                'current' => $this->getCurrentUsage($tenant, $metric),
                'limit' => $this->getLimit($tenant, $metric),
                'percentage' => $this->getUsagePercentage($tenant, $metric),
                'is_unlimited' => $this->isUnlimited($tenant, $metric),
            ];
        }

        return $usage;
    }

    /**
     * Get the plan limit for a metric.
     */
    public function getLimit(Tenant $tenant, string $metric): int|float|null
    {
        $plan = $this->getTenantPlan($tenant);

        if (!$plan) {
            return null;
        }

        $mapping = $this->getMetricToPlanColumn();
        $column = $mapping[$metric] ?? null;

        if (!$column) {
            return null;
        }

        $value = $plan->$column;

        // 0 means unlimited in this system
        return $value === 0 ? null : $value;
    }

    /**
     * Get usage percentage (0-100 or >100 if exceeded).
     */
    public function getUsagePercentage(Tenant $tenant, string $metric): float|null
    {
        $limit = $this->getLimit($tenant, $metric);

        // NULL limit means unlimited
        if ($limit === null) {
            return null;
        }

        $current = $this->getCurrentUsage($tenant, $metric);

        return $limit > 0
            ? round(($current / $limit) * 100, 2)
            : 0.0;
    }

    /**
     * Check if tenant can use a specific amount of a metric.
     */
    public function canUse(Tenant $tenant, string $metric, int|float $increment = 1): bool
    {
        $limit = $this->getLimit($tenant, $metric);

        // NULL limit means unlimited
        if ($limit === null) {
            return true;
        }

        $current = $this->getCurrentUsage($tenant, $metric);

        return ($current + $increment) <= $limit;
    }

    /**
     * Enforce limit - throws exception if limit would be exceeded.
     *
     * @throws UsageLimitExceededException
     */
    public function enforceLimit(Tenant $tenant, string $metric, int|float $increment = 1): void
    {
        $limit = $this->getLimit($tenant, $metric);

        // NULL limit means unlimited
        if ($limit === null) {
            return;
        }

        $current = $this->getCurrentUsage($tenant, $metric);

        if (($current + $increment) > $limit) {
            throw new UsageLimitExceededException(
                metric: $metric,
                limit: $limit,
                currentUsage: $current,
                attemptedIncrement: $increment,
            );
        }
    }

    /**
     * Record usage to the logs.
     */
    public function recordUsage(
        Tenant $tenant,
        string $metric,
        int|float $value,
        array $metadata = []
    ): void {
        UsageLog::create([
            'tenant_id' => $tenant->id,
            'metric' => $metric,
            'value' => $value,
            'recorded_at' => now(),
            'metadata' => $metadata ?: null,
        ]);

        Log::debug('Usage recorded', [
            'tenant_id' => $tenant->id,
            'metric' => $metric,
            'value' => $value,
        ]);
    }

    /**
     * Check if metric is unlimited for tenant.
     */
    public function isUnlimited(Tenant $tenant, string $metric): bool
    {
        return $this->getLimit($tenant, $metric) === null;
    }

    /**
     * Get tenants approaching their limits (>80%).
     *
     * @return Collection<int, Tenant>
     */
    public function getTenantsApproachingLimits(?float $threshold = null): Collection
    {
        $threshold ??= config('usage.approaching_threshold', 80.0);
        $tenants = Tenant::where('is_active', true)->get();

        return $tenants->filter(function (Tenant $tenant) use ($threshold) {
            $metrics = array_keys($this->getMetricToPlanColumn());

            foreach ($metrics as $metric) {
                $percentage = $this->getUsagePercentage($tenant, $metric);

                if ($percentage !== null && $percentage >= $threshold) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * Get usage report for Super Admin.
     */
    public function getSuperAdminUsageReport(): array
    {
        $tenants = Tenant::all();
        $report = [];

        foreach ($tenants as $tenant) {
            $usage = $this->getAllUsage($tenant);
            $plan = $this->getTenantPlan($tenant);

            $report[] = [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'plan_name' => $plan?->name ?? 'No Plan',
                'usage' => $usage,
                'approaching_limits' => $this->isTenantApproachingAnyLimit($tenant),
            ];
        }

        return $report;
    }

    /**
     * Get tenant plan from active subscription.
     */
    private function getTenantPlan(Tenant $tenant): ?Plan
    {
        return $tenant->currentPlan();
    }

    /**
     * Get user count for tenant.
     */
    private function getUserCount(Tenant $tenant): int
    {
        return User::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->count();
    }

    /**
     * Get santri count for tenant.
     */
    private function getSantriCount(Tenant $tenant): int
    {
        return Santri::where('tenant_id', $tenant->id)
            ->where('status', config('usage.santri_status.active', 'aktif'))
            ->count();
    }

    /**
     * Get branch count for tenant.
     */
    private function getBranchCount(Tenant $tenant): int
    {
        // Placeholder: base branch count until branches table is implemented
        return 1;
    }

    /**
     * Get storage usage for tenant.
     */
    private function getStorageUsage(Tenant $tenant): float
    {
        // Placeholder: future implementation will calculate from file storage
        // For now, return the latest logged value or 0
        $storageMetric = collect(config('usage.metrics', []))
            ->keys()
            ->first(fn ($m) => str_contains($m, 'storage')) ?? 'storage_usage_mb';
        return $this->getLatestLoggedUsage($tenant, $storageMetric);
    }

    /**
     * Get latest logged usage for a metric.
     */
    private function getLatestLoggedUsage(Tenant $tenant, string $metric): int|float
    {
        $latest = UsageLog::where('tenant_id', $tenant->id)
            ->where('metric', $metric)
            ->latest('recorded_at')
            ->first();

        return $latest?->value ?? 0;
    }

    /**
     * Check if tenant is approaching any limit.
     */
    private function isTenantApproachingAnyLimit(Tenant $tenant, ?float $threshold = null): bool
    {
        $threshold ??= config('usage.approaching_threshold', 80.0);
        $metrics = array_keys($this->getMetricToPlanColumn());

        foreach ($metrics as $metric) {
            $percentage = $this->getUsagePercentage($tenant, $metric);

            if ($percentage !== null && $percentage >= $threshold) {
                return true;
            }
        }

        return false;
    }
}
