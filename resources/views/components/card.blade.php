@props(['title' => null, 'subtitle' => null, 'class' => '', 'padding' => true, 'shadow' => true, 'border' => true])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-900 rounded-xl overflow-hidden transition-colors duration-200 ' . 
    ($border ? 'border border-gray-200 dark:border-gray-800 ' : '') .
    ($shadow ? 'shadow-sm ' : '') .
    $class]) }}>
    
    @if($title || isset($header))
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div>
                @if($title)
                    <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
                @endif
                @if($subtitle)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $subtitle }}</p>
                @endif
                {{ $header ?? '' }}
            </div>
            @if(isset($actions))
                <div class="flex items-center gap-2">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif
    
    <div class="{{ $padding ? 'p-5' : '' }}">
        {{ $slot }}
    </div>
    
    @if(isset($footer))
        <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800">
            {{ $footer }}
        </div>
    @endif
</div>
