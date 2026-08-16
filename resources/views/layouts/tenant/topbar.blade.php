@php
$tenant = \App\Services\TenantService::getTenant();
$user = Auth::user();
$nav = \App\Services\NavigationGateService::forUser($user);
@endphp

<header class="h-[60px] bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between px-4 sm:px-6 shrink-0 z-30 transition-colors duration-200">
    {{-- Left: Mobile toggle + Page info --}}
    <div class="flex items-center gap-3">
        <button onclick="toggleSidebar()" class="lg:hidden p-2 -ml-1 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
            <i class="fa-solid fa-bars text-sm"></i>
        </button>

        <div>
            <h2 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">@yield('page-title', 'Dashboard')</h2>
            @if(View::hasSection('breadcrumb'))
                <nav class="hidden sm:flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                    <a href="{{ tenant_route('dashboard.santri.index') }}" class="hover:text-gray-600 dark:hover:text-gray-300 transition-colors">Dashboard</a>
                    <i class="fa-solid fa-chevron-right text-[8px]"></i>
                    @yield('breadcrumb')
                </nav>
            @endif
        </div>
    </div>

    {{-- Right: Actions --}}
    <div class="flex items-center gap-1.5">
        {{-- Dark mode toggle --}}
        <button @click="darkMode = !darkMode"
                class="p-2 rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
                :title="darkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'">
            <i class="fa-solid fa-sun text-sm text-amber-500" x-show="darkMode" x-cloak></i>
            <i class="fa-solid fa-moon text-sm text-gray-500 dark:text-gray-400" x-show="!darkMode"></i>
        </button>

        {{-- Notifications --}}
        <x-notification-bell />

        {{-- Separator --}}
        <div class="w-px h-6 bg-gray-200 dark:bg-gray-700 mx-1 hidden sm:block"></div>

        {{-- Quick Add — policy-based visibility --}}
        @if($nav->canCreateSantri() || $nav->canCreateUstadz())
        <div class="relative hidden sm:block" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open"
                    class="flex items-center gap-1.5 px-3 py-1.5 text-[13px] font-medium text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 rounded-lg transition-colors">
                <i class="fa-solid fa-plus text-[10px]"></i>
                Baru
            </button>

            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-black/5 dark:ring-white/10 py-1.5 z-50">
                @if($nav->canCreateSantri())
                    <a href="{{ tenant_route('dashboard.santri.create') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <i class="fa-solid fa-user-plus text-emerald-500 w-4 text-center text-[11px]"></i>
                        Tambah Santri
                    </a>
                @endif
                @if($nav->canCreateUstadz())
                    <a href="{{ tenant_route('dashboard.ustadz.create') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <i class="fa-solid fa-chalkboard-user text-emerald-500 w-4 text-center text-[11px]"></i>
                        Tambah Ustadz
                    </a>
                @endif
                <a href="#" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <i class="fa-solid fa-bullhorn text-emerald-500 w-4 text-center text-[11px]"></i>
                    Pengumuman
                </a>
            </div>
        </div>
        @endif

        {{-- User Menu --}}
        <div class="relative" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open" class="flex items-center gap-2 p-1 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-xs font-bold">
                    {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                </div>
                <span class="hidden md:block text-[13px] font-medium text-gray-700 dark:text-gray-300">{{ $user->name ?? 'User' }}</span>
                <i class="fa-solid fa-chevron-down text-[9px] text-gray-400 hidden md:block"></i>
            </button>

            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-lg ring-1 ring-black/5 dark:ring-white/10 py-1.5 z-50">
                <div class="px-3.5 py-3 border-b border-gray-100 dark:border-gray-700">
                    <p class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">{{ $user->name ?? 'User' }}</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $user->email ?? '' }}</p>
                    <span class="inline-block mt-1.5 px-2 py-0.5 bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 text-[10px] font-semibold rounded capitalize">
                        {{ $user?->role ?? 'User' }}
                    </span>
                </div>

                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <i class="fa-solid fa-user text-gray-400 dark:text-gray-500 w-4 text-center text-[11px]"></i>
                    Profil Saya
                </a>

                <div class="border-t border-gray-100 dark:border-gray-700 mt-1.5 pt-1.5">
                    <form method="POST" action="{{ main_domain_url('/logout') }}" class="block">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 px-3.5 py-2 text-[13px] text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                            <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center text-[11px]"></i>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
