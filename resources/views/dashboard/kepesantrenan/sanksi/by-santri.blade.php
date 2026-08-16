@extends('layouts.tenant')

@section('title', 'Riwayat Sanksi')
@section('page-title', 'Riwayat Sanksi')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.sanksi.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Sanksi</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Riwayat Santri</span>
@endsection

@section('content')

@php
$statistics = [
    'total' => $sanksi->count(),
    'aktif' => $sanksi->where('status', 'aktif')->count(),
    'selesai' => $sanksi->where('status', 'selesai')->count(),
    'dibatalkan' => $sanksi->where('status', 'dibatalkan')->count(),
];
@endphp

{{-- Santri Header --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-bold shrink-0">
                {{ strtoupper(substr($santri->name ?? 'S', 0, 1)) }}
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $santri->name ?? 'Unknown' }}</h1>
                <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5 font-mono">{{ $santri->nis ?? '-' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ tenant_route('dashboard.santri.show', ['id' => $santriId]) }}"
               class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                      hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <i class="fa-solid fa-user mr-1.5"></i>
                Lihat Profil Santri
            </a>
        </div>
    </div>
</div>

{{-- Statistics Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Total Sanksi" value="{{ $statistics['total'] }}" icon="fa-gavel" color="emerald" />
    <x-stat-card title="Aktif" value="{{ $statistics['aktif'] }}" icon="fa-clock" color="amber" />
    <x-stat-card title="Selesai" value="{{ $statistics['selesai'] }}" icon="fa-check" color="rose" />
    <x-stat-card title="Dibatalkan" value="{{ $statistics['dibatalkan'] }}" icon="fa-ban" color="purple" />
</div>

{{-- Riwayat Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">Riwayat Sanksi</h2>
    </div>

    @if($sanksi->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-16 px-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-gavel text-gray-400 dark:text-gray-500 text-xl"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada data sanksi</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                Santri ini belum memiliki riwayat sanksi.
            </p>
        </div>
    @else
        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Periode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diberikan Oleh</th>
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
                            <span class="text-[13px] text-gray-700 dark:text-gray-300 truncate max-w-[200px] block" title="{{ $s->deskripsi }}">{{ $s->deskripsi }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">{{ $s->diberikanOleh->name ?? '-' }}</span>
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
    @endif
</div>

@endsection
