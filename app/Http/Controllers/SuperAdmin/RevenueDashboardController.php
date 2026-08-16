<?php

declare(strict_types=1);

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\RevenueAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * Revenue Dashboard Controller for Super Admin.
 *
 * Provides centralized SaaS revenue, trial, churn, and payment analytics.
 *
 * @package App\Http\Controllers\SuperAdmin
 */
class RevenueDashboardController extends Controller
{
    private RevenueAnalyticsService $analyticsService;

    public function __construct(RevenueAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the revenue analytics dashboard.
     *
     * Shows comprehensive SaaS metrics including MRR, ARR, trial conversion,
     * churn rate, and payment success rate.
     *
     * @return View
     */
    public function index(): View
    {
        // Get all dashboard metrics
        $metrics = $this->analyticsService->getDashboardMetrics();

        // Get churn metrics for current month
        $churnMetrics = $this->analyticsService->getChurnMetrics(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        // Get payment metrics for current month
        $paymentMetrics = $this->analyticsService->getPaymentMetrics(
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        );

        // Get active tenant count
        $activeTenants = Tenant::where('is_active', true)->count();

        // Format currency for display (values are in cents/IDR)
        $formattedMetrics = [
            'mrr' => $this->formatCurrency($metrics['mrr']),
            'arr' => $this->formatCurrency($metrics['arr']),
            'total_revenue' => $this->formatCurrency($metrics['total_revenue']),
            'revenue_this_month' => $this->formatCurrency($metrics['revenue_this_month']),
            'revenue_last_month' => $this->formatCurrency($metrics['revenue_last_month']),
            'revenue_growth_percent' => $metrics['revenue_growth_percent'],
            'active_subscriptions' => number_format($metrics['active_subscriptions']),
            'active_tenants' => number_format($activeTenants),
            'trial_metrics' => $metrics['trial_metrics'],
            'churn_metrics' => $churnMetrics,
            'payment_metrics' => $paymentMetrics,
            'revenue_by_month' => $metrics['revenue_by_month'],
        ];

        return view('dashboard.super-admin.revenue.index', [
            'metrics' => $formattedMetrics,
            'rawMetrics' => $metrics,
        ]);
    }

    /**
     * Return metrics as JSON for AJAX requests or API consumers.
     *
     * Supports date range filtering via query parameters:
     * - start_date (Y-m-d format)
     * - end_date (Y-m-d format)
     *
     * @return JsonResponse
     */
    public function metrics(): JsonResponse
    {
        $startDate = request('start_date')
            ? Carbon::parse(request('start_date'))
            : Carbon::now()->startOfMonth();

        $endDate = request('end_date')
            ? Carbon::parse(request('end_date'))
            : Carbon::now()->endOfMonth();

        // Validate date range
        if ($startDate->gt($endDate)) {
            return response()->json([
                'error' => 'Start date must be before end date.',
            ], 422);
        }

        // Get all metrics
        $dashboardMetrics = $this->analyticsService->getDashboardMetrics();
        $churnMetrics = $this->analyticsService->getChurnMetrics($startDate, $endDate);
        $paymentMetrics = $this->analyticsService->getPaymentMetrics($startDate, $endDate);

        return response()->json([
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'revenue' => [
                'mrr' => $dashboardMetrics['mrr'],
                'arr' => $dashboardMetrics['arr'],
                'total_revenue' => $dashboardMetrics['total_revenue'],
                'revenue_this_month' => $dashboardMetrics['revenue_this_month'],
                'revenue_last_month' => $dashboardMetrics['revenue_last_month'],
                'revenue_growth_percent' => $dashboardMetrics['revenue_growth_percent'],
                'revenue_by_month' => $dashboardMetrics['revenue_by_month'],
            ],
            'subscriptions' => [
                'active_paid' => $dashboardMetrics['active_subscriptions'],
            ],
            'trials' => $dashboardMetrics['trial_metrics'],
            'churn' => $churnMetrics,
            'payments' => $paymentMetrics,
        ]);
    }

    /**
     * Format currency value for display.
     *
     * Converts raw amount to formatted IDR string.
     *
     * @param float $amount
     * @return string
     */
    private function formatCurrency(float $amount): string
    {
        if ($amount >= 1000000000) {
            return 'Rp ' . number_format($amount / 1000000000, 2) . ' Miliar';
        }

        if ($amount >= 1000000) {
            return 'Rp ' . number_format($amount / 1000000, 2) . ' Juta';
        }

        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
