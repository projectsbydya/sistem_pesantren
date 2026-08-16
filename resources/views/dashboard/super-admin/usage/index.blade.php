@extends('layouts.dashboard')

@section('title', 'Usage Monitoring')
@section('page-title', 'Usage Monitoring')

@php
    $totalTenants = $tenants->total();
    $metricsConfig = config('usage.metrics', []);
@endphp

@section('content')
<div class="space-y-6">
    <x-super-admin.page-header
        title="Usage Monitoring"
        subtitle="Pantau penggunaan resource seluruh tenant.">
        <x-slot:actions>
            <x-btn href="{{ route('dashboard.super-admin.usage.approaching') }}" icon="fa-exclamation-triangle" variant="warning" size="sm">Approaching Limits</x-btn>
        </x-slot:actions>
    </x-super-admin.page-header>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <x-stat-card title="Total Tenant" value="{{ $totalTenants }}" icon="fa-building" color="blue"/>
        <x-stat-card title="Mendekati Limit" value="{{ $approachingCount }}" icon="fa-exclamation-triangle" color="{{ $approachingCount > 0 ? 'amber' : 'emerald' }}"/>
        <x-stat-card title="Threshold" value="{{ config('usage.approaching_threshold', 80) }}%" icon="fa-tachometer-alt" color="purple"/>
    </div>

    <!-- Approaching Limits Alert -->
    @if($approachingCount > 0)
        <x-alert type="warning" title="Peringatan Penggunaan">
            {{ $approachingCount }} tenant mendekati batas penggunaan resource (≥ {{ config('usage.approaching_threshold', 80) }}%).
            Pertimbangkan untuk menghubungi tenant atau mengupgrade plan mereka.
        </x-alert>
    @endif

    <!-- Tenants Usage Table -->
    <x-card title="Penggunaan Per Tenant">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-medium text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 rounded-l-lg">Tenant</th>
                        <th class="px-5 py-3">Plan</th>
                        @foreach($metricsConfig as $key => $metric)
                            <th class="px-5 py-3">{{ $metric['label'] }}</th>
                        @endforeach
                        <th class="px-5 py-3 rounded-r-lg text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tenants as $tenant)
                        @php
                            $planName = $tenant->subscriptions->first()?->plan?->name ?? config('usage.labels.no_plan', 'No Plan');
                            $hasPlan = $tenant->subscriptions->first()?->plan !== null;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                        <span class="font-bold text-emerald-600 text-sm">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium text-gray-900">{{ $tenant->name }}</span>
                                        <p class="text-xs text-gray-500">{{ $tenant->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <x-badge variant="{{ $hasPlan ? 'info' : 'default' }}" size="sm">{{ $planName }}</x-badge>
                            </td>
                            @foreach($metricsConfig as $key => $metric)
                                @php
                                    $current = $tenant->usage_summary[$key]['current'] ?? 0;
                                    $limit = $tenant->usage_summary[$key]['limit'] ?? null;
                                    $percentage = $tenant->usage_summary[$key]['percentage'] ?? null;
                                    $isUnlimited = $tenant->usage_summary[$key]['is_unlimited'] ?? true;

                                    if ($percentage !== null) {
                                        if ($percentage >= 100) {
                                            $barColor = 'bg-red-500';
                                            $textColor = 'text-red-700';
                                        } elseif ($percentage >= config('usage.approaching_threshold', 80)) {
                                            $barColor = 'bg-amber-500';
                                            $textColor = 'text-amber-700';
                                        } else {
                                            $barColor = 'bg-emerald-500';
                                            $textColor = 'text-gray-700';
                                        }
                                        $barWidth = min($percentage, 100);
                                    } else {
                                        $barColor = 'bg-blue-400';
                                        $textColor = 'text-gray-700';
                                        $barWidth = 0;
                                    }
                                @endphp
                                <td class="px-5 py-4">
                                    <div class="min-w-[120px]">
                                        <div class="flex items-baseline justify-between mb-1">
                                            <span class="text-sm font-medium {{ $textColor }}">{{ number_format($current) }}</span>
                                            <span class="text-xs text-gray-500">/ {{ $limit !== null ? number_format($limit) : '∞' }}</span>
                                        </div>
                                        @if(!$isUnlimited && $percentage !== null)
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="{{ $barColor }} h-1.5 rounded-full transition-all" style="width: {{ $barWidth }}%"></div>
                                            </div>
                                        @else
                                            <div class="w-full bg-gray-100 rounded-full h-1.5">
                                                <div class="bg-blue-300 h-1.5 rounded-full" style="width: 30%"></div>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                            <td class="px-5 py-4 text-center">
                                <x-btn href="{{ route('dashboard.super-admin.usage.show', $tenant) }}" icon="fa-eye" variant="outline" size="xs">Detail</x-btn>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + count($metricsConfig) }}" class="px-5 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-chart-bar text-3xl text-gray-300"></i>
                                    <p>Belum ada tenant terdaftar.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($tenants->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $tenants->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
