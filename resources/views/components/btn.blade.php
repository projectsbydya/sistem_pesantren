@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left',
])

@php
$baseClasses = 'inline-flex items-center justify-center gap-1.5 font-medium rounded-lg transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900';

$variants = [
    'primary' => 'bg-emerald-600 hover:bg-emerald-700 text-white focus:ring-emerald-500 disabled:opacity-50',
    'secondary' => 'bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-300 focus:ring-gray-500 disabled:opacity-50',
    'danger' => 'bg-red-600 hover:bg-red-700 text-white focus:ring-red-500 disabled:opacity-50',
    'warning' => 'bg-amber-500 hover:bg-amber-600 text-white focus:ring-amber-500 disabled:opacity-50',
    'info' => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500 disabled:opacity-50',
    'ghost' => 'bg-transparent hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-600 dark:text-gray-400 focus:ring-gray-500 disabled:opacity-50',
    'outline' => 'bg-transparent border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 focus:ring-gray-500 disabled:opacity-50',
];

$sizes = [
    'xs' => 'px-2 py-1 text-[11px]',
    'sm' => 'px-3 py-1.5 text-[12px]',
    'md' => 'px-4 py-2 text-[13px]',
    'lg' => 'px-5 py-2.5 text-sm',
];

$classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'left')
            <i class="fa-solid {{ $icon }}"></i>
        @endif
        {{ $slot }}
        @if($icon && $iconPosition === 'right')
            <i class="fa-solid {{ $icon }}"></i>
        @endif
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon && $iconPosition === 'left')
            <i class="fa-solid {{ $icon }}"></i>
        @endif
        {{ $slot }}
        @if($icon && $iconPosition === 'right')
            <i class="fa-solid {{ $icon }}"></i>
        @endif
    </button>
@endif
