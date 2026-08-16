@php
$user = Auth::user();
$currentTenant = App\Services\TenantService::getTenant();
@endphp

<header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
    <!-- Left: Page Title -->
    <div class="flex items-center gap-4">
        <h1 class="text-xl font-semibold text-gray-800">
            @yield('page-title', 'Dashboard')
        </h1>
    </div>

    <!-- Right: User Info & Actions -->
    <div class="flex items-center gap-4">
        <!-- Tenant Badge -->
        @if($currentTenant)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                {{ $currentTenant->name }}
            </span>
        @elseif($user->isSuperAdmin())
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Super Admin Mode
            </span>
        @endif

        <!-- Notifications -->
        <x-notification-bell />

        <!-- User Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-3 text-sm text-gray-700 hover:text-gray-900">
                <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-white font-medium">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="hidden md:block text-left">
                    <p class="font-medium">{{ $user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $user->getRoleLabel() }}</p>
                </div>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Profil Saya
                </a>
                <div class="border-t border-gray-100 my-1"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
