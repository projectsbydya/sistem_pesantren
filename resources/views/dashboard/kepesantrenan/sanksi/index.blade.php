@extends('layouts.tenant')

@section('title', 'Data Sanksi')
@section('page-title', 'Data Sanksi')
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Sanksi</span>
@endsection

@section('content')

{{-- Statistics Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Total Sanksi" value="{{ $statistics['total_sanksi'] ?? 0 }}" icon="fa-gavel" color="emerald" />
    <x-stat-card title="Aktif" value="{{ $statistics['by_status']['aktif'] ?? 0 }}" icon="fa-clock" color="amber" />
    <x-stat-card title="Selesai" value="{{ $statistics['by_status']['selesai'] ?? 0 }}" icon="fa-check" color="rose" />
    <x-stat-card title="Dibatalkan" value="{{ $statistics['by_status']['dibatalkan'] ?? 0 }}" icon="fa-ban" color="purple" />
</div>

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Data Sanksi</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            Kelola sanksi yang diberikan kepada santri
        </p>
    </div>
</div>

{{-- Search & Filters --}}
<form method="GET" action="{{ tenant_route('dashboard.kepesantrenan.sanksi.index') }}" class="flex flex-col lg:flex-row gap-3 mb-5">
    <div class="flex-1">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama santri, NIS, atau deskripsi..."
                   class="w-full pl-9 pr-4 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                          focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors">
        </div>
    </div>
    <div class="flex gap-2 flex-wrap">
        <select name="jenis" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                       text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
            <option value="">Semua Jenis</option>
            <option value="peringatan" {{ request('jenis') === 'peringatan' ? 'selected' : '' }}>Peringatan</option>
            <option value="tugas" {{ request('jenis') === 'tugas' ? 'selected' : '' }}>Tugas</option>
            <option value="skorsing" {{ request('jenis') === 'skorsing' ? 'selected' : '' }}>Skorsing</option>
            <option value="dikembalikan" {{ request('jenis') === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
        </select>
        <select name="status" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                       text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
            <option value="">Semua Status</option>
            <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
            <option value="selesai" {{ request('status') === 'selesai' ? 'selected' : '' }}>Selesai</option>
            <option value="dibatalkan" {{ request('status') === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300">
        <input type="date" name="to" value="{{ request('to') }}" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300">
        <button type="submit" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
            <i class="fa-solid fa-filter text-xs"></i>
        </button>
        @if(request('search') || request('jenis') || request('status') || request('from') || request('to'))
            <a href="{{ tenant_route('dashboard.kepesantrenan.sanksi.index') }}" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors" title="Reset filter">
                <i class="fa-solid fa-xmark text-xs"></i>
            </a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    @if($sanksi->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-16 px-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-gavel text-gray-400 dark:text-gray-500 text-xl"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada data sanksi</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                Sanksi akan tercatat saat diberikan melalui halaman pelanggaran.
            </p>
        </div>
    @else
        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Santri</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Periode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sanksi as $s)
                    @php
                        $jenisLabel = match($s->jenis) {
                            'peringatan' => 'Peringatan',
                            'tugas' => 'Tugas',
                            'skorsing' => 'Skorsing',
                            'dikembalikan' => 'Dikembalikan',
                            default => $s->jenis,
                        };
                        $jenisVariant = match($s->jenis) {
                            'peringatan' => 'warning',
                            'tugas' => 'info',
                            'skorsing' => 'danger',
                            'dikembalikan' => 'danger',
                            default => 'default',
                        };
                        $statusVariant = match($s->status) {
                            'aktif' => 'warning',
                            'selesai' => 'success',
                            'dibatalkan' => 'default',
                            default => 'default',
                        };
                        $statusLabel = match($s->status) {
                            'aktif' => 'Aktif',
                            'selesai' => 'Selesai',
                            'dibatalkan' => 'Dibatalkan',
                            default => $s->status,
                        };
                    @endphp
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[12px] font-bold shrink-0">
                                    {{ strtoupper(substr($s->pelanggaran->santri->name ?? 'S', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100 truncate">{{ $s->pelanggaran->santri->name ?? 'Unknown' }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-mono truncate">{{ $s->pelanggaran->santri->nis ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $jenisVariant }}" size="sm">{{ $jenisLabel }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">
                                {{ $s->tanggal_mulai?->format('d M Y') ?? '-' }}
                                @if($s->tanggal_selesai)
                                    <span class="text-gray-400">- {{ $s->tanggal_selesai?->format('d M Y') }}</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $statusVariant }}" size="sm" dot>{{ $statusLabel }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[13px] text-gray-700 dark:text-gray-300 truncate max-w-[150px] block" title="{{ $s->deskripsi }}">{{ $s->deskripsi }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ tenant_route('dashboard.kepesantrenan.sanksi.show', ['sanksi' => $s->id]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                   title="Lihat">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                </a>
                                @can('update', $s)
                                    <a href="{{ tenant_route('dashboard.kepesantrenan.sanksi.edit', ['sanksi' => $s->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors"
                                       title="Edit">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </a>
                                @endcan
                                @can('delete', $s)
                                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.sanksi.destroy', ['sanksi' => $s->id]) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus sanksi ini?')" class="inline">
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
                Menampilkan {{ $sanksi->firstItem() ?? 0 }} - {{ $sanksi->lastItem() ?? 0 }} dari {{ $sanksi->total() }} sanksi
            </p>
            <div class="flex gap-1">
                {{ $sanksi->links('pagination::simple-tailwind') }}
            </div>
        </div>
    @endif
</div>

@endsection
