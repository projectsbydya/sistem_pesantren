@extends('layouts.dashboard')

@section('title', "Usage: {$tenant->name}")
@section('page-title', "Usage: {$tenant->name}")

@php
    $metricsConfig = config('usage.metrics', []);
    $recentDays = config('usage.recent_days', 30);

    $metricIcons = [
        'user_count' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>',
        'santri_count' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
        'branch_count' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
        'storage_usage_mb' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>',
    ];
    $metricColors = [
        'user_count' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'ring' => 'ring-blue-500'],
        'santri_count' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'ring' => 'ring-emerald-500'],
        'branch_count' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'ring' => 'ring-purple-500'],
        'storage_usage_mb' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'ring' => 'ring-amber-500'],
    ];
@endphp

@section('content')
<div class="space-y-6">
    <x-super-admin.page-header
        title="{{ $tenant->name }}"
        subtitle="Detail penggunaan resource tenant"
        backUrl="{{ route('dashboard.super-admin.usage.index') }}">
        <x-slot:actions>
            <x-badge variant="{{ $tenant->is_active ? 'success' : 'danger' }}" size="sm">
                {{ $tenant->is_active ? 'Aktif' : 'Nonaktif' }}
            </x-badge>
            @if($tenant->is_trial)
                <x-badge variant="warning" size="sm">Trial</x-badge>
            @endif
        </x-slot:actions>
    </x-super-admin.page-header>

    <!-- Usage Metric Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($usage as $metric => $data)
            @php
                $label = $metricsConfig[$metric]['label'] ?? $metric;
                $current = $data['current'];
                $limit = $data['limit'];
                $percentage = $data['percentage'];
                $isUnlimited = $data['is_unlimited'];
                $colors = $metricColors[$metric] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'ring' => 'ring-gray-500'];
                $icon = $metricIcons[$metric] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>';

                if ($percentage !== null) {
                    if ($percentage >= 100) {
                        $statusColor = 'text-red-600';
                        $statusBg = 'bg-red-50 border-red-200';
                        $barColor = 'bg-red-500';
                        $statusLabel = 'Melebihi Limit';
                    } elseif ($percentage >= 80) {
                        $statusColor = 'text-amber-600';
                        $statusBg = 'bg-amber-50 border-amber-200';
                        $barColor = 'bg-amber-500';
                        $statusLabel = 'Mendekati Limit';
                    } else {
                        $statusColor = 'text-emerald-600';
                        $statusBg = 'bg-white border-gray-200';
                        $barColor = 'bg-emerald-500';
                        $statusLabel = 'Normal';
                    }
                    $barWidth = min($percentage, 100);
                } else {
                    $statusColor = 'text-blue-600';
                    $statusBg = 'bg-white border-gray-200';
                    $barColor = 'bg-blue-400';
                    $statusLabel = $isUnlimited ? 'Unlimited' : 'No Plan';
                    $barWidth = 0;
                }

                $suffix = $metric === 'storage_usage_mb' ? ' MB' : '';
            @endphp
            <div class="rounded-xl shadow-sm border p-5 {{ $statusBg }}">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-10 h-10 rounded-lg {{ $colors['bg'] }} flex items-center justify-center">
                        <svg class="w-5 h-5 {{ $colors['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $icon !!}
                        </svg>
                    </div>
                    <span class="text-xs font-medium px-2 py-1 rounded-full {{ $statusColor }} {{ $percentage !== null && $percentage >= 80 ? ($percentage >= 100 ? 'bg-red-100' : 'bg-amber-100') : 'bg-gray-100' }}">
                        {{ $statusLabel }}
                    </span>
                </div>
                <p class="text-sm font-medium text-gray-600">{{ $label }}</p>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-3xl font-bold text-gray-900">{{ number_format($current) }}</span>
                    <span class="text-sm text-gray-500">{{ $suffix }}</span>
                    <span class="text-sm text-gray-400 ml-1">/ {{ $limit !== null ? number_format($limit) . $suffix : '∞' }}</span>
                </div>
                @if(!$isUnlimited && $percentage !== null)
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-xs {{ $statusColor }} font-medium">{{ $percentage }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ $barWidth }}%"></div>
                        </div>
                    </div>
                @else
                    <div class="mt-3">
                        <span class="text-xs text-blue-600 font-medium">Tanpa batas</span>
                        <div class="w-full bg-gray-100 rounded-full h-2 mt-1">
                            <div class="bg-blue-300 h-2 rounded-full" style="width: 30%"></div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Recent Usage Logs -->
    <x-card title="Log Penggunaan Terbaru" subtitle="{{ $recentDays }} hari terakhir">
        @if($recentLogs->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 rounded-l-lg">Metrik</th>
                            <th class="px-5 py-3">Nilai</th>
                            <th class="px-5 py-3">Waktu</th>
                            <th class="px-5 py-3 rounded-r-lg">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentLogs as $log)
                            @php
                                $logLabel = $metricsConfig[$log->metric]['label'] ?? $log->metric;
                                $logColors = $metricColors[$log->metric] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-600'];
                                $isManual = $log->metadata['manual'] ?? false;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $logColors['bg'] }} {{ $logColors['text'] }}">
                                        {{ $logLabel }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 font-medium text-gray-900">
                                    {{ number_format((float) $log->value) }}{{ $log->metric === 'storage_usage_mb' ? ' MB' : '' }}
                                </td>
                                <td class="px-5 py-3 text-gray-500">
                                    <div>{{ $log->recorded_at->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $log->recorded_at->format('H:i') }}</div>
                                </td>
                                <td class="px-5 py-3">
                                    @if($isManual)
                                        <x-badge variant="warning" size="xs">Manual</x-badge>
                                    @else
                                        <span class="text-xs text-gray-400">Otomatis</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12">
                <i class="fa-solid fa-clipboard-list text-4xl text-gray-300 mb-3"></i>
                <p class="text-sm text-gray-500">Belum ada log penggunaan dalam {{ $recentDays }} hari terakhir.</p>
            </div>
        @endif
    </x-card>
</div>
@endsection
