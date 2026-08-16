@props([
    'title',
    'subtitle' => null,
    'backUrl' => null,
    'backLabel' => 'Kembali',
])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center justify-between gap-4']) }}>
    <div class="flex items-start sm:items-center gap-3 sm:gap-4">
        @if($backUrl)
            <a href="{{ $backUrl }}"
               class="inline-flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-lg bg-white border border-gray-200 text-gray-500 hover:text-gray-700 hover:border-gray-300 hover:bg-gray-50 transition-colors flex-shrink-0"
               title="{{ $backLabel }}">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
        @endif
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-gray-900">{{ $title }}</h2>
            @if($subtitle)
                <p class="text-sm text-gray-500 mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if(isset($actions))
        <div class="flex items-center gap-2 flex-wrap">
            {{ $actions }}
        </div>
    @endif
</div>
