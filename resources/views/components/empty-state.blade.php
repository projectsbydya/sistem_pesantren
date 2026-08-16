@props([
    'title' => 'Data belum tersedia',
    'message' => null,
    'checklist' => [],
    'ctaText' => null,
    'ctaRoute' => null,
    'ctaParams' => [],
    'icon' => 'fa-circle-info',
    'variant' => 'default', // 'default', 'warning', 'success'
])

@php
$variantClasses = match($variant) {
    'warning' => [
        'container' => 'bg-amber-50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-800',
        'iconBg' => 'bg-amber-100 dark:bg-amber-900/30',
        'iconColor' => 'text-amber-600 dark:text-amber-400',
        'titleColor' => 'text-amber-900 dark:text-amber-100',
        'messageColor' => 'text-amber-700 dark:text-amber-300',
    ],
    'success' => [
        'container' => 'bg-emerald-50 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-800',
        'iconBg' => 'bg-emerald-100 dark:bg-emerald-900/30',
        'iconColor' => 'text-emerald-600 dark:text-emerald-400',
        'titleColor' => 'text-emerald-900 dark:text-emerald-100',
        'messageColor' => 'text-emerald-700 dark:text-emerald-300',
    ],
    default => [
        'container' => 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-800',
        'iconBg' => 'bg-gray-100 dark:bg-gray-800',
        'iconColor' => 'text-gray-400 dark:text-gray-500',
        'titleColor' => 'text-gray-700 dark:text-gray-300',
        'messageColor' => 'text-gray-500 dark:text-gray-400',
    ],
};
@endphp

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
    <div class="w-16 h-16 {{ $variantClasses['iconBg'] }} rounded-full flex items-center justify-center mx-auto mb-4">
        <i class="fa-solid {{ $icon }} {{ $variantClasses['iconColor'] }} text-2xl"></i>
    </div>

    <h3 class="text-lg font-semibold {{ $variantClasses['titleColor'] }} mb-2">
        {{ $title }}
    </h3>

    @if($message)
        <p class="text-sm {{ $variantClasses['messageColor'] }} mb-4">
            {{ $message }}
        </p>
    @endif

    {{-- Checklist for dependency tracking --}}
    @if(!empty($checklist))
        <div class="flex flex-wrap gap-2 justify-center mb-6 max-w-md mx-auto">
            @foreach($checklist as $item)
                @php
                    $isDone = $item['done'] ?? false;
                    $itemIcon = $isDone ? 'fa-check-circle text-emerald-500' : 'fa-circle-xmark text-red-500';
                    $itemBg = $isDone ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-gray-100 dark:bg-gray-800';
                    $itemText = $isDone ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400';
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 {{ $itemBg }} {{ $itemText }} rounded-full text-xs font-medium">
                    <i class="fa-solid {{ $itemIcon }}"></i>
                    {{ $item['label'] ?? ucfirst($item) }}
                </span>
            @endforeach
        </div>
    @endif

    @if($ctaRoute)
        <a href="{{ tenant_route($ctaRoute, $ctaParams) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fa-solid fa-plus text-xs"></i>
            {{ $ctaText ?? 'Tambah Data' }}
        </a>
    @endif
</div>
