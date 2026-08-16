@php
$currentTenant = App\Services\TenantService::getTenant();
$tenants = App\Models\Tenant::where('is_active', true)->orderBy('name')->get();
@endphp

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" type="button"
            class="w-full flex items-center justify-between gap-2 px-3 py-2 rounded-lg text-sm font-medium bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white transition">
        <span class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            {{ $currentTenant ? 'Ganti Tenant' : 'Pilih Tenant' }}
        </span>
        <svg class="w-4 h-4" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <!-- Dropdown -->
    <div x-show="open" @click.away="open = false"
         class="absolute bottom-full left-0 right-0 mb-2 bg-white rounded-lg shadow-lg border border-gray-200 py-2 max-h-64 overflow-y-auto z-50">

        <div class="px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
            Pilih Pesantren
        </div>

        <!-- All Tenants Option -->
        <form method="POST" action="{{ route('switch-tenant') }}" class="block">
            @csrf
            <input type="hidden" name="tenant_id" value="">
            <button type="submit" class="w-full text-left px-4 py-2 text-sm {{ is_null($currentTenant) ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 {{ is_null($currentTenant) ? 'text-emerald-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Semua Tenant (Super Admin)
                </span>
            </button>
        </form>

        <div class="border-t border-gray-100 my-1"></div>

        <!-- Individual Tenants -->
        @forelse($tenants as $tenant)
            <form method="POST" action="{{ route('switch-tenant') }}" class="block">
                @csrf
                <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                <button type="submit" class="w-full text-left px-4 py-2 text-sm {{ $currentTenant?->id === $tenant->id ? 'bg-emerald-50 text-emerald-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                    <span class="flex items-center gap-2">
                        @if($currentTenant?->id === $tenant->id)
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        @endif
                        {{ $tenant->name }}
                        @if($tenant->is_trial)
                            <span class="text-xs px-1.5 py-0.5 rounded bg-amber-100 text-amber-700">Trial</span>
                        @endif
                    </span>
                </button>
            </form>
        @empty
            <div class="px-4 py-2 text-sm text-gray-500">
                Tidak ada tenant aktif
            </div>
        @endforelse
    </div>
</div>
