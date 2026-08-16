@extends('layouts.dashboard')

@section('title', 'Revenue Analytics Dashboard')
@section('page-title', 'Revenue Analytics')

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-gray-500">Super Admin • SaaS Metrics</p>
            <h1 class="text-2xl font-bold text-gray-900">Revenue Analytics Dashboard</h1>
        </div>
        <a href="{{ route('dashboard.super-admin.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- MRR -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">MRR</p>
                    <p class="text-xl font-bold text-indigo-600 mt-1">{{ $metrics['mrr'] }}</p>
                </div>
                <div class="p-2 bg-indigo-50 rounded-lg">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- ARR -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">ARR</p>
                    <p class="text-xl font-bold text-blue-600 mt-1">{{ $metrics['arr'] }}</p>
                </div>
                <div class="p-2 bg-blue-50 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Trial Conversion Rate -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Trial Conversion</p>
                    <p class="text-xl font-bold {{ ($metrics['trial_metrics']['conversion_rate'] ?? 0) >= 50 ? 'text-emerald-600' : 'text-amber-600' }} mt-1">
                        {{ $metrics['trial_metrics']['conversion_rate'] !== null ? $metrics['trial_metrics']['conversion_rate'] . '%' : 'N/A' }}
                    </p>
                </div>
                <div class="p-2 bg-emerald-50 rounded-lg">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Churn Rate -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Churn Rate</p>
                    <p class="text-xl font-bold {{ ($metrics['churn_metrics']['churn_rate'] ?? 0) <= 5 ? 'text-emerald-600' : (($metrics['churn_metrics']['churn_rate'] ?? 0) <= 10 ? 'text-amber-600' : 'text-rose-600') }} mt-1">
                        {{ $metrics['churn_metrics']['churn_rate'] !== null ? $metrics['churn_metrics']['churn_rate'] . '%' : 'N/A' }}
                    </p>
                </div>
                <div class="p-2 bg-rose-50 rounded-lg">
                    <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Payment Success Rate -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Payment Success</p>
                    <p class="text-xl font-bold {{ ($metrics['payment_metrics']['success_rate'] ?? 0) >= 90 ? 'text-emerald-600' : (($metrics['payment_metrics']['success_rate'] ?? 0) >= 70 ? 'text-amber-600' : 'text-rose-600') }} mt-1">
                        {{ $metrics['payment_metrics']['success_rate'] !== null ? $metrics['payment_metrics']['success_rate'] . '%' : 'N/A' }}
                    </p>
                </div>
                <div class="p-2 bg-green-50 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Tenants -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Active Tenants</p>
                    <p class="text-xl font-bold text-purple-600 mt-1">{{ $metrics['active_tenants'] }}</p>
                </div>
                <div class="p-2 bg-purple-50 rounded-lg">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Trial Conversion -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Trial Conversion</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Total Trials</span>
                    <span class="text-lg font-semibold text-gray-900">{{ number_format($metrics['trial_metrics']['total_trials']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Converted</span>
                    <span class="text-lg font-semibold text-emerald-600">{{ number_format($metrics['trial_metrics']['converted_trials']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Expired</span>
                    <span class="text-lg font-semibold text-amber-600">{{ number_format($metrics['trial_metrics']['expired_trials']) }}</span>
                </div>
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Conversion Rate</span>
                        <span class="text-2xl font-bold {{ $metrics['trial_metrics']['conversion_rate'] >= 50 ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $metrics['trial_metrics']['conversion_rate'] !== null ? $metrics['trial_metrics']['conversion_rate'] . '%' : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Churn Rate -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Monthly Churn</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Active at Start</span>
                    <span class="text-lg font-semibold text-gray-900">{{ number_format($metrics['churn_metrics']['active_at_start']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Cancelled</span>
                    <span class="text-lg font-semibold text-rose-600">{{ number_format($metrics['churn_metrics']['cancelled_during_period']) }}</span>
                </div>
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Churn Rate</span>
                        <span class="text-2xl font-bold {{ ($metrics['churn_metrics']['churn_rate'] ?? 0) <= 5 ? 'text-emerald-600' : (($metrics['churn_metrics']['churn_rate'] ?? 0) <= 10 ? 'text-amber-600' : 'text-rose-600') }}">
                            {{ $metrics['churn_metrics']['churn_rate'] !== null ? $metrics['churn_metrics']['churn_rate'] . '%' : 'N/A' }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Periode: {{ $metrics['churn_metrics']['period_start'] }} - {{ $metrics['churn_metrics']['period_end'] }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Payment Success -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Payment Success Rate</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Total Payments</span>
                    <span class="text-lg font-semibold text-gray-900">{{ number_format($metrics['payment_metrics']['total_payments']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Successful</span>
                    <span class="text-lg font-semibold text-emerald-600">{{ number_format($metrics['payment_metrics']['successful_payments']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Failed</span>
                    <span class="text-lg font-semibold text-rose-600">{{ number_format($metrics['payment_metrics']['failed_payments']) }}</span>
                </div>
                @if($metrics['payment_metrics']['pending_payments'] > 0)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Pending</span>
                    <span class="text-lg font-semibold text-amber-600">{{ number_format($metrics['payment_metrics']['pending_payments']) }}</span>
                </div>
                @endif
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900">Success Rate</span>
                        <span class="text-2xl font-bold {{ ($metrics['payment_metrics']['success_rate'] ?? 0) >= 90 ? 'text-emerald-600' : (($metrics['payment_metrics']['success_rate'] ?? 0) >= 70 ? 'text-amber-600' : 'text-rose-600') }}">
                            {{ $metrics['payment_metrics']['success_rate'] !== null ? $metrics['payment_metrics']['success_rate'] . '%' : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Trend Chart -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend (12 Months)</h3>
        <div class="h-64 relative">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Revenue by Month Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue by Month</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trend</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $revenueByMonth = collect($metrics['revenue_by_month'])->reverse()->take(6)->reverse();
                        $previousRevenue = null;
                    @endphp
                    @foreach($revenueByMonth as $data)
                    @php
                        $trend = null;
                        if ($previousRevenue !== null && $previousRevenue > 0) {
                            $change = (($data['revenue'] - $previousRevenue) / $previousRevenue) * 100;
                            $trend = $change;
                        }
                        $previousRevenue = $data['revenue'];
                    @endphp
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $data['month'] }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">
                            Rp {{ number_format($data['revenue'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            @if($trend !== null)
                                @if($trend > 0)
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-100 rounded-full">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                        </svg>
                                        +{{ number_format($trend, 1) }}%
                                    </span>
                                @elseif($trend < 0)
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-rose-700 bg-rose-100 rounded-full">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
                                        </svg>
                                        {{ number_format($trend, 1) }}%
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-full">
                                        -
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = @json(collect($metrics['revenue_by_month'])->pluck('revenue'));
    const monthLabels = @json(collect($metrics['revenue_by_month'])->pluck('month'));

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Revenue',
                data: revenueData,
                borderColor: 'rgb(79, 70, 229)',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: 'rgb(79, 70, 229)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            }
                            return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
});
</script>
@endpush
