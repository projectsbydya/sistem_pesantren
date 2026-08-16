@php
$tenantCount = App\Models\Tenant::count();
$activeTenantCount = App\Models\Tenant::where('is_active', true)->count();
$trialTenantCount = App\Models\Tenant::where('is_trial', true)->count();
$userCount = App\Models\User::where('is_super_admin', false)->count();
@endphp

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Tenants -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Tenant</p>
                <p class="text-2xl font-bold text-gray-900">{{ $tenantCount }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-2 text-sm">
            <span class="text-emerald-600 font-medium">{{ $activeTenantCount }} aktif</span>
            <span class="text-gray-400">•</span>
            <span class="text-amber-600 font-medium">{{ $trialTenantCount }} trial</span>
        </div>
    </div>

    <!-- Users -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total User</p>
                <p class="text-2xl font-bold text-gray-900">{{ $userCount }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
        </div>
        <p class="mt-4 text-sm text-gray-500">Admin, Santri, dan Orang Tua</p>
    </div>

    

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm font-medium text-gray-600 mb-4">Aksi Cepat</p>
        <div class="space-y-2">
            <a href="{{ route('dashboard.super-admin.tenants.create') }}" class="flex items-center gap-2 text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Buat Tenant Baru
            </a>
            <a href="{{ route('dashboard.super-admin.tenants.index') }}" class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                Manajemen User
            </a>
        </div>
    </div>
</div>

<!-- Recent Tenants -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Tenant Terbaru</h3>
        <a href="{{ route('dashboard.super-admin.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
            Lihat Semua
        </a>
    </div>
    <div class="divide-y divide-gray-200">
        @forelse(App\Models\Tenant::latest()->take(5)->get() as $tenant)
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                        <span class="text-lg font-bold text-gray-600">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <p class="font-medium text-gray-900">{{ $tenant->name }}</p>
                        <p class="text-sm text-gray-500">{{ $tenant->slug }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($tenant->is_trial)
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Trial</span>
                    @endif
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $tenant->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ $tenant->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        @empty
            <div class="px-6 py-8 text-center text-gray-500">
                Belum ada tenant
            </div>
        @endforelse
    </div>
</div>
