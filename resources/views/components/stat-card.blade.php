@props([
    'title',
    'value',
    'icon',
    'trend' => null,
    'trendUp' => true,
    'color' => 'emerald',
    'href' => null,
])

@php
$colors = [
    'emerald' => 'from-emerald-500 to-teal-600',
    'blue' => 'from-blue-500 to-indigo-600',
    'amber' => 'from-amber-500 to-orange-600',
    'rose' => 'from-rose-500 to-pink-600',
    'purple' => 'from-purple-500 to-violet-600',
];

$bgGradient = $colors[$color] ?? $colors['emerald'];
@endphp

@if($href)
    <a href="{{ $href }}" class="block">
@endif
<div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6 hover:shadow-md transition-all duration-200 {{ $href ? 'cursor-pointer' : '' }}">
    <div class="flex items-start justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $title }}</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $value }}</p>
            
            @if($trend !== null)
                <div class="mt-2 flex items-center gap-1 text-sm">
                    <span class="{{ $trendUp ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        <i class="fa-solid {{ $trendUp ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                        {{ $trend }}%
                    </span>
                    <span class="text-gray-400 dark:text-gray-500">vs bulan lalu</span>
                </div>
            @endif
        </div>
        
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $bgGradient }} flex items-center justify-center text-white shadow-lg">
            <i class="fa-solid {{ $icon }} text-lg"></i>
        </div>
    </div>
</div>
@if($href)
    </a>
@endif
