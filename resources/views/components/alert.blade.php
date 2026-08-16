@props([
    'type' => 'info',
    'title' => null,
    'dismissible' => false,
])

@php
$icons = [
    'success' => 'fa-check-circle',
    'error' => 'fa-exclamation-circle',
    'warning' => 'fa-exclamation-triangle',
    'info' => 'fa-info-circle',
];

$styles = [
    'success' => 'bg-green-50 border-green-200 text-green-800 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-400',
    'error' => 'bg-red-50 border-red-200 text-red-800 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-400',
    'warning' => 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-400',
    'info' => 'bg-blue-50 border-blue-200 text-blue-800 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-400',
];

$icon = $icons[$type] ?? $icons['info'];
$style = $styles[$type] ?? $styles['info'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-lg border p-4 ' . $style . ($dismissible ? ' relative pr-10' : '')]) }} role="alert">
    <div class="flex items-start gap-3">
        <i class="fa-solid {{ $icon }} mt-0.5"></i>
        <div class="flex-1">
            @if($title)
                <h4 class="font-semibold text-sm">{{ $title }}</h4>
            @endif
            <div class="text-sm {{ $title ? 'mt-1' : '' }}">
                {{ $slot }}
            </div>
        </div>
    </div>
    
    @if($dismissible)
        <button onclick="this.parentElement.remove()" class="absolute top-4 right-4 text-current hover:opacity-75">
            <i class="fa-solid fa-times"></i>
        </button>
    @endif
</div>
