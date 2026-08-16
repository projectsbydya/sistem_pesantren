<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Invoice;
use App\Models\SaasPayment;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Revenue Analytics Service
 *
 * Calculates key SaaS financial metrics for multi-tenant applications.
 * Follows fail-closed principles: invalid/missing data returns 0 rather than null.
 *
 * Metrics provided:
 * - MRR (Monthly Recurring Revenue)
 * - ARR (Annual Recurring Revenue)
 * - Total Revenue (lifetime confirmed payments)
 * - Revenue by month (time-series data)
 * - Active subscriptions count
 */
class RevenueAnalyticsService
{
    /**
     * Calculate Monthly Recurring Revenue (MRR).
     *
     * MRR includes:
     * - Active subscriptions with monthly billing cycle (full amount)
     * - Active subscriptions with yearly billing cycle (amount / 12)
     * - Only subscriptions with at least one confirmed payment
     *
     * Fail-closed: Returns 0.00 if no qualifying subscriptions exist.
     */
    public function calculateMrr(?int $tenantId = null): float
    {
        try {
            $query = $this->getBasePaidActiveSubscriptionsQuery($tenantId);

            $subscriptions = $query->get();

            if ($subscriptions->isEmpty()) {
                return 0.00;
            }

            $mrr = $subscriptions->sum(function (Subscription $subscription): float {
                return $this->normalizeToMonthlyAmount($subscription);
            });

            return round($mrr, 2);
        } catch (\Throwable $e) {
            Log::error('MRR calculation failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return 0.00;
        }
    }

    /**
     * Calculate Annual Recurring Revenue (ARR).
     *
     * ARR = MRR × 12
     * Provides a standardized annual view of recurring revenue.
     *
     * Fail-closed: Returns 0.00 if calculation fails.
     */
    public function calculateArr(?int $tenantId = null): float
    {
        return round($this->calculateMrr($tenantId) * 12, 2);
    }

    /**
     * Calculate total confirmed revenue.
     *
     * Sum of all confirmed payment amounts (lifetime).
     * Can be filtered by tenant or date range.
     *
     * Fail-closed: Returns 0.00 if no confirmed payments exist.
     */
    public function calculateTotalRevenue(
        ?int $tenantId = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): float {
        try {
            $query = SaasPayment::query()
                ->where('status', 'confirmed');

            // Apply tenant filter via invoice relationship
            if ($tenantId !== null) {
                $query->whereHas('invoice', function ($q) use ($tenantId): void {
                    $q->where('tenant_id', $tenantId);
                });
            }

            // Apply date range filters
            if ($startDate !== null) {
                $query->where('confirmed_at', '>=', $startDate);
            }

            if ($endDate !== null) {
                $query->where('confirmed_at', '<=', $endDate);
            }

            $total = $query->sum('amount') ?? 0;

            return round((float) $total, 2);
        } catch (\Throwable $e) {
            Log::error('Total revenue calculation failed', [
                'tenant_id' => $tenantId,
                'start_date' => $startDate?->toDateString(),
                'end_date' => $endDate?->toDateString(),
                'error' => $e->getMessage(),
            ]);

            return 0.00;
        }
    }

    /**
     * Calculate revenue grouped by month.
     *
     * Returns a collection of monthly revenue data for charting/reporting.
     * Each entry contains: month (YYYY-MM), revenue, payment_count
     *
     * @return Collection<int, array{month: string, revenue: float, payment_count: int}>
     */
    public function calculateRevenueByMonth(
        ?int $tenantId = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int $months = null
    ): Collection {
        try {
            // Default to last 12 months if no date range provided
            if ($startDate === null && $months !== null) {
                $startDate = now()->subMonths($months - 1)->startOfMonth();
                $endDate = now()->endOfMonth();
            } elseif ($startDate === null) {
                $startDate = now()->subMonths(11)->startOfMonth();
                $endDate = now()->endOfMonth();
            }

            $endDate ??= now()->endOfMonth();

            $query = SaasPayment::query()
                ->select(
                    DB::raw($this->getMonthFormatSql()." as month"),
                    DB::raw('SUM(amount) as revenue'),
                    DB::raw('COUNT(*) as payment_count')
                )
                ->where('status', 'confirmed')
                ->whereNotNull('confirmed_at')
                ->whereBetween('confirmed_at', [$startDate, $endDate]);

            // Apply tenant filter via invoice relationship
            if ($tenantId !== null) {
                $query->whereHas('invoice', function ($q) use ($tenantId): void {
                    $q->where('tenant_id', $tenantId);
                });
            }

            $results = $query
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Fill in missing months with zero revenue
            return $this->fillMissingMonths($results, $startDate, $endDate);
        } catch (\Throwable $e) {
            Log::error('Revenue by month calculation failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * Count active paid subscriptions.
     *
     * Returns the count of subscriptions that:
     * - Have status = 'active'
     * - Have at least one confirmed payment (subscription is paid)
     *
     * Fail-closed: Returns 0 if query fails.
     */
    public function countActivePaidSubscriptions(?int $tenantId = null): int
    {
        try {
            $query = $this->getBasePaidActiveSubscriptionsQuery($tenantId);

            return $query->count();
        } catch (\Throwable $e) {
            Log::error('Active subscription count failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Count total trial subscriptions (including expired and converted).
     *
     * Includes all subscriptions that started as trials, regardless of outcome.
     * Fail-closed: Returns 0 on error.
     */
    public function countTotalTrialSubscriptions(?int $tenantId = null): int
    {
        try {
            $query = Subscription::query()
                ->whereNotNull('trial_ends_at');

            if ($tenantId !== null) {
                $query->where('tenant_id', $tenantId);
            }

            return $query->count();
        } catch (\Throwable $e) {
            Log::error('Total trial subscription count failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Count converted trials (trials that became paid active subscriptions).
     *
     * A trial is "converted" when:
     * - It had a trial_ends_at (was a trial)
     * - Current status is 'active'
     * - Has at least one confirmed payment
     *
     * Fail-closed: Returns 0 on error.
     */
    public function countConvertedTrials(?int $tenantId = null): int
    {
        try {
            $query = Subscription::query()
                ->whereNotNull('trial_ends_at')
                ->where('status', 'active')
                ->whereHas('invoices', function ($q): void {
                    $q->where('status', 'paid')
                      ->whereHas('payments', function ($pq): void {
                          $pq->where('status', 'confirmed');
                      });
                });

            if ($tenantId !== null) {
                $query->where('tenant_id', $tenantId);
            }

            return $query->count();
        } catch (\Throwable $e) {
            Log::error('Converted trial count failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Count expired trials (trials that ended without conversion).
     *
     * A trial is "expired" when:
     * - It had a trial_ends_at (was a trial)
     * - Current status is 'expired' OR status is 'cancelled' with no payment
     * - No confirmed payment exists
     *
     * Fail-closed: Returns 0 on error.
     */
    public function countExpiredTrials(?int $tenantId = null): int
    {
        try {
            $query = Subscription::query()
                ->whereNotNull('trial_ends_at')
                ->whereIn('status', ['expired', 'cancelled'])
                ->whereDoesntHave('invoices', function ($q): void {
                    $q->where('status', 'paid')
                      ->whereHas('payments', function ($pq): void {
                          $pq->where('status', 'confirmed');
                      });
                });

            if ($tenantId !== null) {
                $query->where('tenant_id', $tenantId);
            }

            return $query->count();
        } catch (\Throwable $e) {
            Log::error('Expired trial count failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Calculate trial conversion rate (%).
     *
     * Formula: converted_trials / total_trials * 100
     *
     * Returns null when no trials exist (avoid division by zero).
     * Fail-closed: Returns 0.0 on error.
     */
    public function calculateTrialConversionRate(?int $tenantId = null): ?float
    {
        try {
            $totalTrials = $this->countTotalTrialSubscriptions($tenantId);

            if ($totalTrials === 0) {
                return null;
            }

            $convertedTrials = $this->countConvertedTrials($tenantId);

            return round(($convertedTrials / $totalTrials) * 100, 2);
        } catch (\Throwable $e) {
            Log::error('Trial conversion rate calculation failed', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return 0.0;
        }
    }

    /**
     * Get trial conversion metrics bundle.
     *
     * @return array{
     *     total_trials: int,
     *     converted_trials: int,
     *     expired_trials: int,
     *     conversion_rate: float|null
     * }
     */
    public function getTrialConversionMetrics(?int $tenantId = null): array
    {
        return [
            'total_trials' => $this->countTotalTrialSubscriptions($tenantId),
            'converted_trials' => $this->countConvertedTrials($tenantId),
            'expired_trials' => $this->countExpiredTrials($tenantId),
            'conversion_rate' => $this->calculateTrialConversionRate($tenantId),
        ];
    }

    /**
     * Count cancelled subscriptions within a date range.
     *
     * Only counts subscriptions with status 'cancelled' and non-null cancelled_at
     * within the specified period.
     *
     * Fail-closed: Returns 0 on error.
     */
    public function countCancelledSubscriptions(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int $tenantId = null
    ): int {
        try {
            $query = Subscription::query()
                ->where('status', 'cancelled')
                ->whereNotNull('cancelled_at');

            // Default to current month if no dates provided
            if ($startDate === null && $endDate === null) {
                $startDate = now()->startOfMonth();
                $endDate = now()->endOfMonth();
            }

            if ($startDate !== null) {
                $query->where('cancelled_at', '>=', $startDate);
            }

            if ($endDate !== null) {
                $query->where('cancelled_at', '<=', $endDate);
            }

            if ($tenantId !== null) {
                $query->where('tenant_id', $tenantId);
            }

            return $query->count();
        } catch (\Throwable $e) {
            Log::error('Cancelled subscription count failed', [
                'tenant_id' => $tenantId,
                'start_date' => $startDate?->toDateString(),
                'end_date' => $endDate?->toDateString(),
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Count active subscriptions at a specific point in time.
     *
     * Subscriptions are considered "active at" when:
     * - status was 'active' at that time
     * - starts_at <= pointInTime
     * - ends_at > pointInTime (or null)
     *
     * Used as denominator for churn rate calculation.
     *
     * Fail-closed: Returns 0 on error.
     */
    public function countActiveSubscriptionsAt(Carbon $pointInTime, ?int $tenantId = null): int
    {
        try {
            $query = Subscription::query()
                ->where('status', 'active')
                ->where('starts_at', '<=', $pointInTime)
                ->where(function ($q) use ($pointInTime): void {
                    $q->whereNull('ends_at')
                      ->orWhere('ends_at', '>', $pointInTime);
                });

            if ($tenantId !== null) {
                $query->where('tenant_id', $tenantId);
            }

            return $query->count();
        } catch (\Throwable $e) {
            Log::error('Active subscription at-time count failed', [
                'tenant_id' => $tenantId,
                'point_in_time' => $pointInTime->toDateTimeString(),
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Calculate churn rate for a period (%).
     *
     * Formula: cancelled_subscriptions / active_subscriptions_at_period_start * 100
     *
     * Churn rate measures the percentage of customers who stopped subscribing
     * during the period relative to the starting active customer base.
     *
     * Returns null when no active subscriptions at period start (avoid division by zero).
     * Fail-closed: Returns 0.0 on error.
     */
    public function calculateChurnRate(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int $tenantId = null
    ): ?float {
        try {
            // Default to current month
            $periodStart = $startDate ?? now()->startOfMonth();
            $periodEnd = $endDate ?? now()->endOfMonth();

            // Count active subscriptions at period start (denominator)
            $activeAtStart = $this->countActiveSubscriptionsAt($periodStart, $tenantId);

            if ($activeAtStart === 0) {
                return null; // Cannot calculate rate without baseline
            }

            // Count cancelled subscriptions during period (numerator)
            $cancelled = $this->countCancelledSubscriptions($periodStart, $periodEnd, $tenantId);

            return round(($cancelled / $activeAtStart) * 100, 2);
        } catch (\Throwable $e) {
            Log::error('Churn rate calculation failed', [
                'tenant_id' => $tenantId,
                'start_date' => $startDate?->toDateString(),
                'end_date' => $endDate?->toDateString(),
                'error' => $e->getMessage(),
            ]);

            return 0.0;
        }
    }

    /**
     * Get churn metrics bundle for a period.
     *
     * @return array{
     *     period_start: string,
     *     period_end: string,
     *     active_at_start: int,
     *     cancelled_during_period: int,
     *     churn_rate: float|null
     * }
     */
    public function getChurnMetrics(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int $tenantId = null
    ): array {
        $periodStart = $startDate ?? now()->startOfMonth();
        $periodEnd = $endDate ?? now()->endOfMonth();

        return [
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'active_at_start' => $this->countActiveSubscriptionsAt($periodStart, $tenantId),
            'cancelled_during_period' => $this->countCancelledSubscriptions($periodStart, $periodEnd, $tenantId),
            'churn_rate' => $this->calculateChurnRate($periodStart, $periodEnd, $tenantId),
        ];
    }

    /**
     * Count total payments (all statuses).
     *
     * Includes all payment records regardless of status.
     * Can be filtered by date range and tenant.
     *
     * Fail-closed: Returns 0 on error.
     */
    public function countTotalPayments(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int $tenantId = null
    ): int {
        try {
            $query = SaasPayment::query();

            if ($startDate !== null) {
                $query->where('created_at', '>=', $startDate);
            }

            if ($endDate !== null) {
                $query->where('created_at', '<=', $endDate);
            }

            if ($tenantId !== null) {
                $query->whereHas('invoice', function ($q) use ($tenantId): void {
                    $q->where('tenant_id', $tenantId);
                });
            }

            return $query->count();
        } catch (\Throwable $e) {
            Log::error('Total payment count failed', [
                'tenant_id' => $tenantId,
                'start_date' => $startDate?->toDateString(),
                'end_date' => $endDate?->toDateString(),
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Count successful (confirmed) payments.
     *
     * Only counts payments with status 'confirmed'.
     *
     * Fail-closed: Returns 0 on error.
     */
    public function countSuccessfulPayments(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int $tenantId = null
    ): int {
        try {
            $query = SaasPayment::query()->where('status', 'confirmed');

            if ($startDate !== null) {
                $query->where('confirmed_at', '>=', $startDate);
            }

            if ($endDate !== null) {
                $query->where('confirmed_at', '<=', $endDate);
            }

            if ($tenantId !== null) {
                $query->whereHas('invoice', function ($q) use ($tenantId): void {
                    $q->where('tenant_id', $tenantId);
                });
            }

            return $query->count();
        } catch (\Throwable $e) {
            Log::error('Successful payment count failed', [
                'tenant_id' => $tenantId,
                'start_date' => $startDate?->toDateString(),
                'end_date' => $endDate?->toDateString(),
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Count failed/rejected payments.
     *
     * Only counts payments with status 'rejected'.
     *
     * Fail-closed: Returns 0 on error.
     */
    public function countFailedPayments(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int $tenantId = null
    ): int {
        try {
            $query = SaasPayment::query()->where('status', 'rejected');

            if ($startDate !== null) {
                $query->where('created_at', '>=', $startDate);
            }

            if ($endDate !== null) {
                $query->where('created_at', '<=', $endDate);
            }

            if ($tenantId !== null) {
                $query->whereHas('invoice', function ($q) use ($tenantId): void {
                    $q->where('tenant_id', $tenantId);
                });
            }

            return $query->count();
        } catch (\Throwable $e) {
            Log::error('Failed payment count failed', [
                'tenant_id' => $tenantId,
                'start_date' => $startDate?->toDateString(),
                'end_date' => $endDate?->toDateString(),
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Calculate payment success rate (%).
     *
     * Formula: successful_payments / total_payments * 100
     *
     * Returns null when no payments exist (avoid division by zero).
     * Fail-closed: Returns 0.0 on error.
     */
    public function calculatePaymentSuccessRate(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int $tenantId = null
    ): ?float {
        try {
            $total = $this->countTotalPayments($startDate, $endDate, $tenantId);

            if ($total === 0) {
                return null;
            }

            $successful = $this->countSuccessfulPayments($startDate, $endDate, $tenantId);

            return round(($successful / $total) * 100, 2);
        } catch (\Throwable $e) {
            Log::error('Payment success rate calculation failed', [
                'tenant_id' => $tenantId,
                'start_date' => $startDate?->toDateString(),
                'end_date' => $endDate?->toDateString(),
                'error' => $e->getMessage(),
            ]);

            return 0.0;
        }
    }

    /**
     * Get payment analytics bundle.
     *
     * @return array{
     *     total_payments: int,
     *     successful_payments: int,
     *     failed_payments: int,
     *     pending_payments: int,
     *     success_rate: float|null
     * }
     */
    public function getPaymentMetrics(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?int $tenantId = null
    ): array {
        $total = $this->countTotalPayments($startDate, $endDate, $tenantId);
        $successful = $this->countSuccessfulPayments($startDate, $endDate, $tenantId);
        $failed = $this->countFailedPayments($startDate, $endDate, $tenantId);

        return [
            'total_payments' => $total,
            'successful_payments' => $successful,
            'failed_payments' => $failed,
            'pending_payments' => $total - $successful - $failed,
            'success_rate' => $this->calculatePaymentSuccessRate($startDate, $endDate, $tenantId),
        ];
    }

    /**
     * Get comprehensive revenue analytics dashboard data.
     *
     * Returns all key metrics in a single call for efficiency.
     *
     * @return array{
     *     mrr: float,
     *     arr: float,
     *     total_revenue: float,
     *     revenue_this_month: float,
     *     revenue_last_month: float,
     *     revenue_growth_percent: float|null,
     *     active_subscriptions: int,
     *     revenue_by_month: Collection,
     *     trial_metrics: array
     * }
     */
    public function getDashboardMetrics(?int $tenantId = null): array
    {
        $now = now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();

        $mrr = $this->calculateMrr($tenantId);
        $arr = $this->calculateArr($tenantId);
        $totalRevenue = $this->calculateTotalRevenue($tenantId);
        $activeSubscriptions = $this->countActivePaidSubscriptions($tenantId);

        $revenueThisMonth = $this->calculateTotalRevenue($tenantId, $startOfMonth, $endOfMonth);
        $revenueLastMonth = $this->calculateTotalRevenue($tenantId, $startOfLastMonth, $endOfLastMonth);

        $revenueGrowthPercent = null;
        if ($revenueLastMonth > 0) {
            $revenueGrowthPercent = round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100, 2);
        }

        $revenueByMonth = $this->calculateRevenueByMonth($tenantId, months: 12);

        return [
            'mrr' => $mrr,
            'arr' => $arr,
            'total_revenue' => $totalRevenue,
            'revenue_this_month' => $revenueThisMonth,
            'revenue_last_month' => $revenueLastMonth,
            'revenue_growth_percent' => $revenueGrowthPercent,
            'active_subscriptions' => $activeSubscriptions,
            'revenue_by_month' => $revenueByMonth,
            'trial_metrics' => $this->getTrialConversionMetrics($tenantId),
        ];
    }

    /**
     * Get base query for paid active subscriptions.
     *
     * A subscription is considered "paid and active" when:
     * - status = 'active'
     * - has at least one confirmed payment associated via invoice
     */
    private function getBasePaidActiveSubscriptionsQuery(?int $tenantId = null)
    {
        $query = Subscription::query()
            ->where('status', 'active')
            ->where('amount', '>', 0)
            ->whereHas('invoices', function ($q): void {
                $q->where('status', 'paid')
                  ->whereHas('payments', function ($pq): void {
                      $pq->where('status', 'confirmed');
                  });
            });

        if ($tenantId !== null) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    /**
     * Normalize subscription amount to monthly equivalent.
     *
     * - Monthly billing: return full amount
     * - Yearly billing: return amount / 12
     */
    private function normalizeToMonthlyAmount(Subscription $subscription): float
    {
        $amount = (float) $subscription->amount;

        if ($subscription->billing_cycle === 'yearly') {
            return $amount / 12;
        }

        return $amount;
    }

    /**
     * Get the SQL for formatting month based on database driver.
     */
    private function getMonthFormatSql(): string
    {
        $connection = DB::connection()->getDriverName();

        return match ($connection) {
            'sqlite' => "strftime('%Y-%m', confirmed_at)",
            'pgsql' => "TO_CHAR(confirmed_at, 'YYYY-MM')",
            default => "DATE_FORMAT(confirmed_at, '%Y-%m')", // MySQL, MariaDB
        };
    }

    /**
     * Fill in missing months with zero revenue data.
     *
     * Ensures the time series is complete for charting.
     *
     * @param Collection $results Query results with month, revenue, payment_count
     * @return Collection<int, array{month: string, revenue: float, payment_count: int}>
     */
    private function fillMissingMonths(
        Collection $results,
        Carbon $startDate,
        Carbon $endDate
    ): Collection {
        $months = collect();
        $current = $startDate->copy()->startOfMonth();
        $end = $endDate->copy()->startOfMonth();

        // Create lookup map from results
        $revenueMap = $results->keyBy('month');

        while ($current <= $end) {
            $monthKey = $current->format('Y-m');
            $monthData = $revenueMap->get($monthKey);

            $months->push([
                'month' => $monthKey,
                'revenue' => (float) ($monthData?->revenue ?? 0),
                'payment_count' => (int) ($monthData?->payment_count ?? 0),
            ]);

            $current->addMonth();
        }

        return $months;
    }
}
