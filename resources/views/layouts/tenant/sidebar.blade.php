@php
// Active check: route name prefix must match AND (if item scoped to a program) programSlug must match too
$isActiveItem = function (array $item) use ($currentRoute, $currentProgram): bool {
    $routeMatch = str_starts_with($currentRoute, $item['active']);
    if (! $routeMatch) {
        return false;
    }
    if (isset($item['program'])) {
        return $currentProgram === $item['program'];
    }
    return true;
};

// Check if any item in a dropdown group is active (recursively checks nested children)
$isDropdownActive = function (array $items) use (&$isDropdownActive, $isActiveItem): bool {
    foreach ($items as $item) {
        if ($isActiveItem($item)) {
            return true;
        }
        if (!empty($item['children']) && $isDropdownActive($item['children'])) {
            return true;
        }
    }
    return false;
};
@endphp

{{-- Sidebar: adaptive light/dark mode --}}
<aside id="sidebar"
       class="fixed lg:static inset-y-0 left-0 z-50 w-[260px] h-full shrink-0
              bg-white dark:bg-gray-950 text-gray-900 dark:text-white
              transform -translate-x-full lg:translate-x-0
              transition-transform duration-300 ease-in-out
              flex flex-col border-r border-gray-200 dark:border-white/[0.06]">

    {{-- Brand --}}
    <div class="h-[60px] flex items-center gap-3 px-5 shrink-0 border-b border-gray-100 dark:border-white/[0.06]">
        <div class="w-8 h-8 rounded-lg bg-emerald-600 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-mosque text-white text-xs"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="text-[13px] font-semibold text-gray-900 dark:text-white truncate leading-tight">{{ $tenant?->name ?? 'Pesantren' }}</p>
            <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate leading-tight">{{ $tenant?->slug ?? 'tenant' }}.pesantren</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-5 scrollbar-thin scrollbar-thumb-gray-200 dark:scrollbar-thumb-gray-800">
        @foreach($menuGroups as $group)
            @php
                // Group-level gate: hide entire section if user lacks access
                $groupVisible = $nav->allows($group['gate'] ?? null);
            @endphp
            @if($groupVisible)
            <div>
                @if(!isset($group['dropdown']))
                <p class="px-3 mb-1.5 text-[10px] font-semibold uppercase tracking-[0.08em] text-gray-400 dark:text-gray-500">
                    {{ $group['title'] }}
                </p>
                @endif

                {{-- Single dropdown group (Data Pendidikan, Kepesantrenan) --}}
                @if(isset($group['dropdown']))
                    <ul class="space-y-0.5">
                        @php
                            $visibleItems = array_filter($group['items'], function($item) use ($nav) {
                                return $nav->allows($item['gate'] ?? null);
                            });
                            $ddActive = $isDropdownActive($visibleItems);
                        @endphp
                        @if(count($visibleItems) > 0)
                        <li x-data="{ open: {{ $ddActive ? 'true' : 'false' }} }">
                            <button @click="open = !open"
                                    class="group w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150
                                           {{ $ddActive
                                              ? 'bg-emerald-50 dark:bg-white/10 text-emerald-700 dark:text-white'
                                              : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/[0.06]' }}">
                                <i class="fa-solid {{ $group['icon'] ?? 'fa-circle' }} w-4 text-center text-[12px]
                                   {{ $ddActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"></i>
                                <span class="flex-1 truncate text-left">{{ $group['title'] }}</span>
                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200
                                   {{ $ddActive ? 'text-emerald-500 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500' }}"
                                   :class="open ? 'rotate-180' : ''"></i>
                            </button>
                            <ul x-show="open"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1"
                                class="mt-0.5 ml-3 pl-3 border-l border-gray-200 dark:border-white/[0.08] space-y-0.5">
                                @foreach($visibleItems as $item)
                                    @php
                                        $isActive = $isActiveItem($item);
                                    @endphp
                                    <li>
                                        <a href="{{ $item['href'] }}"
                                           class="group flex items-center gap-3 px-3 py-1.5 rounded-lg text-[12px] font-medium transition-all duration-150
                                                  {{ $isActive
                                                     ? 'bg-emerald-50 dark:bg-white/10 text-emerald-700 dark:text-white'
                                                     : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/[0.06]' }}">
                                            <i class="fa-solid {{ $item['icon'] }} w-3.5 text-center text-[11px]
                                               {{ $isActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"></i>
                                            <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                            @if($isActive)
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                        @endif
                    </ul>

                {{-- Dropdown groups (Akademik programs) --}}
                @elseif(isset($group['dropdowns']))
                    <ul class="space-y-0.5">
                        @foreach($group['dropdowns'] as $dropdown)
                            @php
                                // Filter dropdown items by gate
                                $visibleItems = array_filter($dropdown['items'], function($item) use ($nav) {
                                    return $nav->allows($item['gate'] ?? null);
                                });
                                $ddActive = $isDropdownActive($visibleItems);
                            @endphp
                            @if(count($visibleItems) > 0)
                            <li x-data="{ open: {{ $ddActive ? 'true' : 'false' }} }">
                                <button @click="open = !open"
                                        class="group w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150
                                               {{ $ddActive
                                                  ? 'bg-emerald-50 dark:bg-white/10 text-emerald-700 dark:text-white'
                                                  : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/[0.06]' }}">
                                    <i class="fa-solid {{ $dropdown['icon'] }} w-4 text-center text-[12px]
                                       {{ $ddActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"></i>
                                    <span class="flex-1 truncate text-left">{{ $dropdown['label'] }}</span>
                                    <i class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200
                                       {{ $ddActive ? 'text-emerald-500 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500' }}"
                                       :class="open ? 'rotate-180' : ''"></i>
                                </button>
                                <ul x-show="open"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-1"
                                    class="mt-0.5 ml-3 pl-3 border-l border-gray-200 dark:border-white/[0.08] space-y-0.5">
                                    @foreach($visibleItems as $item)
                                        @php
                                            $hasChildren = !empty($item['children']);
                                            $isActive = $isActiveItem($item) || ($hasChildren && $isDropdownActive($item['children']));
                                        @endphp
                                        <li>
                                            <a href="{{ $item['href'] }}"
                                               class="group flex items-center gap-3 px-3 py-1.5 rounded-lg text-[12px] font-medium transition-all duration-150
                                                      {{ $isActive
                                                         ? 'bg-emerald-50 dark:bg-white/10 text-emerald-700 dark:text-white'
                                                         : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/[0.06]' }}">
                                                <i class="fa-solid {{ $item['icon'] }} w-3.5 text-center text-[11px]
                                                   {{ $isActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"></i>
                                                <span class="flex-1 truncate">{{ $item['label'] }}</span>
                                                @if($isActive)
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                                @endif
                                            </a>
                                            @if($hasChildren)
                                                <ul class="mt-0.5 ml-3 pl-3 border-l border-gray-200 dark:border-white/[0.08] space-y-0.5">
                                                    @foreach($item['children'] as $child)
                                                        @php
                                                            $isChildActive = $isActiveItem($child);
                                                        @endphp
                                                        <li>
                                                            <a href="{{ $child['href'] }}"
                                                               class="group flex items-center gap-3 px-3 py-1.5 rounded-lg text-[12px] font-medium transition-all duration-150
                                                                      {{ $isChildActive
                                                                         ? 'bg-emerald-50 dark:bg-white/10 text-emerald-700 dark:text-white'
                                                                         : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/[0.06]' }}">
                                                                <i class="fa-solid {{ $child['icon'] }} w-3.5 text-center text-[11px]
                                                                   {{ $isChildActive ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300' }}"></i>
                                                                <span class="flex-1 truncate">{{ $child['label'] }}</span>
                                                                @if($isChildActive)
                                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                                                @endif
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                            @endif
                        @endforeach
                    </ul>

                {{-- Regular flat menu items --}}
                @else
                    <ul class="space-y-0.5">
                        @foreach($group['items'] as $item)
                            @php
                                // Policy-based visibility via NavigationGateService
                                $isVisible = $nav->allows($item['gate'] ?? null);
                            @endphp
                            @if($isVisible)
                                @php
                                    $isActive = $isActiveItem($item);
                                    $isSoon = $item['soon'] ?? false;
                                    $isHeader = $item['header'] ?? false;
                                    $isIndent = $item['indent'] ?? false;
                                @endphp
                                <li>
                                    <a href="{{ $item['href'] }}"
                                       @if($isSoon || $isHeader) onclick="return false;" @endif
                                       class="group flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium transition-all duration-150
                                              {{ $isHeader
                                                 ? 'text-gray-400 dark:text-gray-500 font-bold cursor-default'
                                                 : ($isActive
                                                    ? 'bg-emerald-50 dark:bg-white/10 text-emerald-700 dark:text-white'
                                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/[0.06]') }}
                                              {{ ($isSoon || $isHeader) ? 'pointer-events-auto cursor-default' . ($isSoon ? ' opacity-40' : '') : '' }}
                                              {{ $isIndent ? 'ml-6' : '' }}">

                                        <i class="fa-solid {{ $item['icon'] }} w-4 text-center text-[12px]
                                           {{ $isHeader
                                              ? 'text-gray-400 dark:text-gray-600'
                                              : ($isActive
                                                 ? 'text-emerald-600 dark:text-emerald-400'
                                                 : 'text-gray-400 dark:text-gray-500 group-hover:text-gray-600 dark:group-hover:text-gray-300') }}"></i>

                                        <span class="flex-1 truncate">{{ $item['label'] }}</span>

                                        @if($isActive && !$isHeader)
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                        @endif

                                        @if($isSoon)
                                            <span class="text-[9px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">Soon</span>
                                        @endif
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @endif
            </div>
            @endif
        @endforeach
    </nav>
</aside>
