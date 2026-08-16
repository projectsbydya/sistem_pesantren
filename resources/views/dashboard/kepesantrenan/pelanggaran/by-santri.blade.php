@extends('layouts.tenant')

@section('title', 'Riwayat Pelanggaran')
@section('page-title', 'Riwayat Pelanggaran')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Pelanggaran</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Riwayat Santri</span>
@endsection

@section('content')

@php
$statistics = [
    'total' => $pelanggaran->count(),
    'pending' => $pelanggaran->where('status', 'pending')->count(),
    'diproses' => $pelanggaran->where('status', 'diproses')->count(),
    'selesai' => $pelanggaran->where('status', 'selesai')->count(),
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
            @can('create', App\Models\Pelanggaran::class)
                <x-btn href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.create') }}" variant="primary" icon="fa-plus">
                    Tambah Pelanggaran
                </x-btn>
            @endcan
        </div>
    </div>
</div>

{{-- Statistics Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Total Pelanggaran" value="{{ $statistics['total'] }}" icon="fa-triangle-exclamation" color="emerald" />
    <x-stat-card title="Pending" value="{{ $statistics['pending'] }}" icon="fa-clock" color="amber" />
    <x-stat-card title="Diproses" value="{{ $statistics['diproses'] }}" icon="fa-spinner" color="blue" />
    <x-stat-card title="Selesai" value="{{ $statistics['selesai'] }}" icon="fa-check" color="rose" />
</div>

{{-- Riwayat Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">Riwayat Pelanggaran</h2>
    </div>

    @if($pelanggaran->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-16 px-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-triangle-exclamation text-gray-400 dark:text-gray-500 text-xl"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada data pelanggaran</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                Santri ini belum memiliki riwayat pelanggaran.
            </p>
            @can('create', App\Models\Pelanggaran::class)
                <x-btn href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.create') }}" variant="primary" size="sm" icon="fa-plus">
                    Tambah Pelanggaran
                </x-btn>
            @endcan
        </div>
    @else
        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pelapor</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pelanggaran as $p)
                    @php
                        $jenisVariant = match($p->jenis) {
                            'ringan' => 'default',
                            'sedang' => 'warning',
                            'berat' => 'danger',
                            default => 'default',
                        };
                        $jenisLabel = match($p->jenis) {
                            'ringan' => 'Ringan',
                            'sedang' => 'Sedang',
                            'berat' => 'Berat',
                            default => $p->jenis,
                        };
                        $statusVariant = match($p->status) {
                            'pending' => 'warning',
                            'diproses' => 'info',
                            'selesai' => 'success',
                            default => 'default',
                        };
                        $statusLabel = match($p->status) {
                            'pending' => 'Pending',
                            'diproses' => 'Diproses',
                            'selesai' => 'Selesai',
                            default => $p->status,
                        };
                    @endphp
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group">
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">{{ $p->tanggal?->format('d M Y') ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $jenisVariant }}" size="sm">{{ $jenisLabel }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{ $p->kategori }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[13px] text-gray-700 dark:text-gray-300 truncate max-w-[200px] block" title="{{ $p->deskripsi }}">{{ $p->deskripsi }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $statusVariant }}" size="sm" dot>{{ $statusLabel }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">{{ $p->pelapor->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.show', ['pelanggaran' => $p->id]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                   title="Lihat">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                </a>
                                @can('update', $p)
                                    <a href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.edit', ['pelanggaran' => $p->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors"
                                       title="Edit">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </a>
                                @endcan
                                @can('delete', $p)
                                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.destroy', ['pelanggaran' => $p->id]) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus pelanggaran ini? Data yang sudah dihapus tidak dapat dikembalikan.')" class="inline">
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
