@extends('layouts.tenant')

@section('title', 'Riwayat Kegiatan Harian')
@section('page-title', 'Riwayat Kegiatan Harian')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Kegiatan Harian</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Riwayat Santri</span>
@endsection

@section('content')

@php
$jenisLabels = [
    'sholat' => 'Sholat',
    'tilawah' => 'Tilawah',
    'dzikir' => 'Dzikir',
    'sholat_dhuha' => 'Sholat Dhuha',
    'sholat_tahajjud' => 'Sholat Tahajjud',
    'sholat_rawatib' => 'Sholat Rawatib',
    'murojaah' => 'Murojaah',
    'setoran' => 'Setoran',
    'kegiatan_pagi' => 'Kegiatan Pagi',
    'kegiatan_sore' => 'Kegiatan Sore',
    'kegiatan_malam' => 'Kegiatan Malam',
];
$statistics = [
    'total' => $kegiatan->count(),
    'terjadwal' => $kegiatan->where('status', 'terjadwal')->count(),
    'dilaksanakan' => $kegiatan->where('status', 'dilaksanakan')->count(),
    'tidak_dilaksanakan' => $kegiatan->where('status', 'tidak_dilaksanakan')->count(),
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
            @can('create', App\Models\KegiatanHarian::class)
                <x-btn href="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.create') }}" variant="primary" icon="fa-plus">
                    Tambah Kegiatan
                </x-btn>
            @endcan
        </div>
    </div>
</div>

{{-- Statistics Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Total" value="{{ $statistics['total'] }}" icon="fa-clipboard-list" color="emerald" />
    <x-stat-card title="Terjadwal" value="{{ $statistics['terjadwal'] }}" icon="fa-clock" color="amber" />
    <x-stat-card title="Dilaksanakan" value="{{ $statistics['dilaksanakan'] }}" icon="fa-check" color="rose" />
    <x-stat-card title="Tidak Dilaksanakan" value="{{ $statistics['tidak_dilaksanakan'] }}" icon="fa-xmark" color="purple" />
</div>

{{-- Riwayat Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">Riwayat Kegiatan</h2>
    </div>

    @if($kegiatan->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-16 px-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-clipboard-list text-gray-400 dark:text-gray-500 text-xl"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada data kegiatan</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                Santri ini belum memiliki riwayat kegiatan harian.
            </p>
            @can('create', App\Models\KegiatanHarian::class)
                <x-btn href="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.create') }}" variant="primary" size="sm" icon="fa-plus">
                    Tambah Kegiatan
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kegiatan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dicatat Oleh</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kegiatan as $k)
                    @php
                        $statusVariant = match($k->status) {
                            'terjadwal' => 'warning',
                            'dilaksanakan' => 'success',
                            'tidak_dilaksanakan' => 'danger',
                            default => 'default',
                        };
                        $statusLabel = match($k->status) {
                            'terjadwal' => 'Terjadwal',
                            'dilaksanakan' => 'Dilaksanakan',
                            'tidak_dilaksanakan' => 'Tidak',
                            default => $k->status,
                        };
                        $kategoriVariant = match($k->kategori) {
                            'wajib' => 'danger',
                            'sunnah' => 'info',
                            'ekstra' => 'default',
                            default => 'default',
                        };
                    @endphp
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group">
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">{{ $k->tanggal?->format('d M Y') ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div>
                                <span class="text-[13px] text-gray-700 dark:text-gray-300">{{ $jenisLabels[$k->jenis_kegiatan] ?? $k->jenis_kegiatan }}</span>
                                <x-badge variant="{{ $kategoriVariant }}" size="sm" class="ml-2">{{ $k->kategori }}</x-badge>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">
                                {{ $k->waktu_mulai ?? '-' }}{{ $k->waktu_selesai ? ' - ' . $k->waktu_selesai : '' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $statusVariant }}" size="sm" dot>{{ $statusLabel }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">{{ $k->dicatatOleh->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.show', ['kegiatan' => $k->id]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                   title="Lihat">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                </a>
                                @if($k->status === 'terjadwal')
                                    @can('markStatus', $k)
                                        <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.done', ['kegiatan' => $k->id]) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="p-1.5 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 rounded-md transition-colors"
                                                    title="Tandai Selesai"
                                                    onclick="return confirm('Tandai kegiatan sebagai selesai?')">
                                                <i class="fa-solid fa-check text-[11px]"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.missed', ['kegiatan' => $k->id]) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="p-1.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-md transition-colors"
                                                    title="Tidak Dilaksanakan"
                                                    onclick="return confirm('Tandai kegiatan sebagai tidak dilaksanakan?')">
                                                <i class="fa-solid fa-xmark text-[11px]"></i>
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                                @can('update', $k)
                                    <a href="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.edit', ['kegiatan' => $k->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors"
                                       title="Edit">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </a>
                                @endcan
                                @can('delete', $k)
                                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.destroy', ['kegiatan' => $k->id]) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus kegiatan ini?')" class="inline">
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
