@props([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
])

@php
$baseClasses = 'inline-flex items-center gap-1.5 font-medium rounded-full transition-colors';

$variants = [
    'default' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    'primary' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
    'success' => 'bg-green-50 text-green-700 dark:bg-green-500/15 dark:text-green-400',
    'warning' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
    'danger' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
    'info' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
    'purple' => 'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-400',
    'pink' => 'bg-pink-50 text-pink-700 dark:bg-pink-500/15 dark:text-pink-400',
    'outline' => 'bg-transparent border border-gray-200 text-gray-500 dark:border-gray-700 dark:text-gray-400',
];

$sizes = [
    'xs' => 'px-1.5 py-0.5 text-[10px]',
    'sm' => 'px-2 py-0.5 text-[11px]',
    'md' => 'px-2.5 py-0.5 text-xs',
    'lg' => 'px-3 py-1 text-xs',
];

$classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['md']);

$dotColors = [
    'default' => 'bg-gray-400',
    'primary' => 'bg-emerald-500',
    'success' => 'bg-green-500',
    'warning' => 'bg-amber-500',
    'danger' => 'bg-red-500',
    'info' => 'bg-blue-500',
    'purple' => 'bg-purple-500',
    'pink' => 'bg-pink-500',
];
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColors[$variant] ?? 'bg-gray-400' }}"></span>
    @endif
    {{ $slot }}
</span>
