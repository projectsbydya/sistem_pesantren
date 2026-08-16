@extends('layouts.tenant')

@section('title', 'Ustadz & Karyawan')

@section('content')
@php $tenant = \App\Services\TenantService::getTenant(); @endphp

{{-- Alert for credential download --}}
@if(session('show_credentials_download'))
    <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">Akun Berhasil Dibuat!</h4>
                <p class="text-sm text-emerald-700 dark:text-emerald-300 mt-1">{{ session('success') }}</p>
                <div class="mt-3">
                    <a href="{{ tenant_route('dashboard.ustadz.credentials.download') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                        <i class="fa-solid fa-file-excel"></i>
                        Download File Akun (.XLSX)
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Ustadz & Karyawan</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">Manajemen tenaga pengajar — {{ $tenant?->name }}</p>
    </div>
    @if(Auth::user()->isAdmin())
        <x-btn href="{{ tenant_route('dashboard.ustadz.create') }}" variant="primary" icon="fa-plus">
            Tambah Ustadz
        </x-btn>
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
    @php
    $totalUstadz  = $ustadz->count();
    $aktifCount   = $ustadz->filter(fn($u) => $u->isActive())->count();
    $pengajarCount = $ustadz->where('role', 'pengajar')->count();
    $waliCount    = $ustadz->where('role', 'wali_kelas')->count();
    $performaAvg  = $ustadz->whereNotNull('performa')->avg('performa');
    $uStats = [
        ['label' => 'Total Ustadz',  'value' => $totalUstadz,          'icon' => 'fa-users',             'color' => 'bg-blue-50 dark:bg-blue-500/10 border-blue-200 dark:border-blue-500/20 text-blue-600 dark:text-blue-400'],
        ['label' => 'Aktif',         'value' => $aktifCount,            'icon' => 'fa-circle-check',      'color' => 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-400'],
        ['label' => 'Pengajar',    'value' => $pengajarCount,         'icon' => 'fa-chalkboard-user',   'color' => 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950 dark:text-indigo-300 dark:border-indigo-800'],
        ['label' => 'Wali Kelas',  'value' => $waliCount,             'icon' => 'fa-user-tie',          'color' => 'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/20 text-amber-600 dark:text-amber-400'],
        ['label' => 'Performa',    'value' => $performaAvg ? round($performaAvg) . '%' : '-', 'icon' => 'fa-chart-line',   'color' => 'bg-purple-50 dark:bg-purple-500/10 border-purple-200 dark:border-purple-500/20 text-purple-600 dark:text-purple-400'],
    ];
    @endphp
    @foreach($uStats as $s)
        <div class="rounded-xl border p-4 {{ $s['color'] }} transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-semibold uppercase tracking-wider opacity-70">{{ $s['label'] }}</span>
                <i class="fa-solid {{ $s['icon'] }}"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $s['value'] }}</p>
        </div>
    @endforeach
</div>

{{-- Filter --}}
<x-card class="mb-5">
    <div class="flex flex-col md:flex-row gap-3">
        <div class="relative flex-1">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" placeholder="Cari nama ustadz atau spesialisasi..."
                   class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        </div>
        <div class="flex flex-wrap gap-2">
            <select class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <option value="">Semua Role</option>
                <option value="pengajar">Pengajar</option>
                <option value="pembina">Pembina</option>
                <option value="admin">Admin Pesantren</option>
            </select>
            <select class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <option value="">Semua Status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>
    </div>
</x-card>

{{-- Table --}}
<x-card title="Daftar Ustadz & Karyawan" :padding="false">
    <x-slot:actions>
        <x-btn variant="ghost" size="sm" icon="fa-download">Export</x-btn>
    </x-slot:actions>
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Nama</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Spesialisasi</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Role</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Jam / Minggu</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider min-w-[140px]">Performa</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Status</th>
                    @if(Auth::user()->isAdmin())
                        <th class="text-right px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($ustadz as $u)
                    @php
                    $role       = $u->role ?? 'pengajar';
                    $jamMengajar= $u->jam_mengajar ?? rand(10, 28);
                    $performa   = $u->performa ?? rand(65, 98);
                    $perfColor  = $performa >= 85 ? 'bg-emerald-500' : ($performa >= 70 ? 'bg-blue-500' : 'bg-amber-400');
                    $roleConfig = [
                        'pengajar' => ['label' => 'Pengajar',        'variant' => 'info'],
                        'pembina'  => ['label' => 'Pembina',         'variant' => 'purple'],
                        'admin'    => ['label' => 'Admin Pesantren', 'variant' => 'default'],
                    ];
                    $rc = $roleConfig[$role] ?? $roleConfig['pengajar'];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 text-white flex items-center justify-center text-sm font-bold shrink-0">
                                    {{ strtoupper(substr($u->user?->name ?? 'U', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $u->user?->name ?? '-' }}</p>
                                    <p class="text-[11px] text-gray-400 font-mono">{{ $u->user?->email ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $u->subjects->pluck('name')->implode(', ') ?: '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <x-badge variant="{{ $rc['variant'] }}" size="sm">{{ $rc['label'] }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $jamMengajar }}</span>
                            <span class="text-[11px] text-gray-400">jam</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                                    <div class="{{ $perfColor }} h-full rounded-full" style="width: {{ $performa }}%"></div>
                                </div>
                                <span class="text-[11px] font-semibold text-gray-700 dark:text-gray-300 shrink-0">{{ $performa }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <x-badge variant="{{ ($u->user?->is_active ?? false) ? 'success' : 'default' }}" size="sm" dot>
                                {{ ($u->user?->is_active ?? false) ? 'Aktif' : 'Nonaktif' }}
                            </x-badge>
                        </td>
                        @if(Auth::user()->isAdmin())
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <x-btn href="{{ tenant_route('dashboard.ustadz.edit', ['id' => $u->id]) }}" variant="ghost" size="xs" icon="fa-pen">Edit</x-btn>
                                    <x-btn href="{{ tenant_route('dashboard.ustadz.show', ['id' => $u->id]) }}" variant="ghost" size="xs" icon="fa-eye">Lihat</x-btn>
                                    <form action="{{ tenant_route('dashboard.ustadz.destroy', ['id' => $u->id]) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus ustadz ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center gap-1 px-2 py-1 text-xs text-red-600 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 rounded">
                                            <i class="fa-solid fa-trash"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <i class="fa-solid fa-chalkboard-user text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
                            <p class="text-[13px] text-gray-500 dark:text-gray-400 mb-3">Data belum tersedia</p>
                            @if(Auth::user()->isAdmin())
                                <x-btn href="{{ tenant_route('dashboard.ustadz.create') }}" variant="outline" size="sm" icon="fa-plus">
                                    Tambah Ustadz Pertama
                                </x-btn>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($ustadz->count() > 0)
        <x-slot:footer>
            <div class="flex items-center justify-between text-[12px] text-gray-500 dark:text-gray-400">
                <span>{{ $ustadz->count() }} ustadz terdaftar</span>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-emerald-500"></span> ≥85% Sangat Baik</div>
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-blue-500"></span> 70–84% Baik</div>
                    <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-amber-400"></span> &lt;70% Perhatian</div>
                </div>
            </div>
        </x-slot:footer>
    @endif
</x-card>

@endsection
