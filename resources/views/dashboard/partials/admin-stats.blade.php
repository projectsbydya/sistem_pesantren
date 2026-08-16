@php
$currentTenant = App\Services\TenantService::getTenant();
$santriCount = App\Models\Santri::count();
$parentCount = App\Models\Parents::count();
$activeSantri = App\Models\Santri::where('status', 'active')->count();
@endphp

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <!-- Total Santri -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Santri</p>
                <p class="text-2xl font-bold text-gray-900">{{ $santriCount }}</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
        </div>
        <div class="mt-4">
            <span class="text-sm text-emerald-600 font-medium">{{ $activeSantri }} aktif</span>
        </div>
    </div>

    <!-- Total Parents -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">Total Orang Tua</p>
                <p class="text-2xl font-bold text-gray-900">{{ $parentCount }}</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm font-medium text-gray-600 mb-4">Aksi Cepat</p>
        <div class="space-y-2">
            <a href="{{ route('dashboard.santri.create') }}" class="flex items-center gap-2 text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Tambah Santri
            </a>
            <a href="{{ route('dashboard.parent.create') }}" class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                Tambah Orang Tua
            </a>
        </div>
    </div>
</div>

<!-- Recent Santri -->
<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900">Santri Terbaru</h3>
        <a href="{{ route('dashboard.santri.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
            Lihat Semua
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 font-medium">
                <tr>
                    <th class="px-6 py-3">NIS</th>
                    <th class="px-6 py-3">Nama</th>
                    <th class="px-6 py-3">Jenis Kelamin</th>
                    <th class="px-6 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse(App\Models\Santri::latest()->take(5)->get() as $santri)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $santri->nis }}</td>
                        <td class="px-6 py-4">{{ $santri->name }}</td>
                        <td class="px-6 py-4">{{ $santri->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $santri->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ $santri->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            Belum ada data santri
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
