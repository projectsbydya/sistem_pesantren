@extends('layouts.tenant')

@section('title', 'Data Kamar')
@section('page-title', 'Data Kamar')
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Kamar</span>
@endsection

@section('content')

{{-- Statistics Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                <i class="fa-solid fa-bed text-emerald-600 dark:text-emerald-400 text-sm"></i>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Total Kamar</p>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $statistics['total_kamar'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center">
                <i class="fa-solid fa-users text-blue-600 dark:text-blue-400 text-sm"></i>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Terisi</p>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $statistics['total_terisi'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-500/10 flex items-center justify-center">
                <i class="fa-solid fa-door-open text-amber-600 dark:text-amber-400 text-sm"></i>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Tersedia</p>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $statistics['total_tersedia'] }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-500/10 flex items-center justify-center">
                <i class="fa-solid fa-chart-pie text-purple-600 dark:text-purple-400 text-sm"></i>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Okupansi</p>
                <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $statistics['tingkat_okupansi'] }}%</p>
            </div>
        </div>
    </div>
</div>

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Data Kamar</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            Kelola data kamar dan penempatan santri
        </p>
    </div>
    @can('create', App\Models\Kamar::class)
        <x-btn href="{{ tenant_route('dashboard.kepesantrenan.kamar.create') }}" variant="primary" icon="fa-plus">
            Tambah Kamar
        </x-btn>
    @endcan
</div>

{{-- Search & Filters --}}
<form method="GET" action="{{ tenant_route('dashboard.kepesantrenan.kamar.index') }}" class="flex flex-col sm:flex-row gap-3 mb-5">
    <div class="flex-1">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, lokasi, atau deskripsi..."
                   class="w-full pl-9 pr-4 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                          focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors">
        </div>
    </div>
    <div class="flex gap-2">
        <select name="status" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                       text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
            <option value="">Semua Status</option>
            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="penuh" {{ request('status') === 'penuh' ? 'selected' : '' }}>Penuh</option>
            <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
        </select>
        <button type="submit" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
            <i class="fa-solid fa-filter text-xs"></i>
        </button>
        @if(request('search') || request('status'))
            <a href="{{ tenant_route('dashboard.kepesantrenan.kamar.index') }}" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors" title="Reset filter">
                <i class="fa-solid fa-xmark text-xs"></i>
            </a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    @if($kamar->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-16 px-6">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-bed text-gray-400 dark:text-gray-500 text-xl"></i>
            </div>
            <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada data kamar</h3>
            <p class="text-[13px] text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                Mulai dengan menambahkan data kamar pertama untuk pesantren ini.
            </p>
            @can('create', App\Models\Kamar::class)
                <x-btn href="{{ tenant_route('dashboard.kepesantrenan.kamar.create') }}" variant="primary" size="sm" icon="fa-plus">
                    Tambah Kamar
                </x-btn>
            @endcan
        </div>
    @else
        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Kamar</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Lokasi</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kapasitas</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Terisi</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sisa</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kamar as $k)
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[12px] font-bold shrink-0">
                                    <i class="fa-solid fa-bed"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100 truncate">{{ $k->name }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 truncate">{{ Str::limit($k->description, 30) }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-[13px] text-gray-600 dark:text-gray-400">{{ $k->lokasi ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-[13px] text-gray-600 dark:text-gray-400">{{ $k->kapasitas }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-[13px] font-medium {{ $k->terisi > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500' }}">
                                {{ $k->terisi ?? 0 }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $sisa = $k->kapasitas - ($k->terisi ?? 0);
                                $sisaClass = $sisa === 0 ? 'text-red-600 dark:text-red-400' : ($sisa <= 2 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400');
                            @endphp
                            <span class="text-[13px] font-medium {{ $sisaClass }}">{{ $sisa }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusVariant = match($k->status) {
                                    'aktif' => 'success',
                                    'penuh' => 'warning',
                                    'nonaktif' => 'default',
                                    default => 'default',
                                };
                                $statusLabel = match($k->status) {
                                    'aktif' => 'Aktif',
                                    'penuh' => 'Penuh',
                                    'nonaktif' => 'Nonaktif',
                                    default => $k->status,
                                };
                            @endphp
                            <x-badge variant="{{ $statusVariant }}" size="sm" dot>
                                {{ $statusLabel }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ tenant_route('dashboard.kepesantrenan.kamar.show', ['kamar' => $k->id]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                   title="Lihat">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                </a>
                                @can('update', $k)
                                    <a href="{{ tenant_route('dashboard.kepesantrenan.kamar.edit', ['kamar' => $k->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors"
                                       title="Edit">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </a>
                                @endcan
                                @can('delete', $k)
                                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.kamar.destroy', ['kamar' => $k->id]) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus kamar {{ $k->name }}? Kamar yang memiliki penghuni tidak dapat dihapus.')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-md transition-colors"
                                                title="Hapus">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <p class="text-[12px] text-gray-400 dark:text-gray-500">
                Menampilkan {{ $kamar->firstItem() ?? 0 }} - {{ $kamar->lastItem() ?? 0 }} dari {{ $kamar->total() }} kamar
            </p>
            <div class="flex gap-1">
                {{ $kamar->links('pagination::simple-tailwind') }}
            </div>
        </div>
    @endif
</div>

@endsection
