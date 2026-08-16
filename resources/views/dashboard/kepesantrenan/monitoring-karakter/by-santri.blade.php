@extends('layouts.tenant')

@section('title', 'Riwayat Monitoring Karakter')
@section('page-title', 'Riwayat Monitoring Karakter')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Monitoring Karakter</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Riwayat Santri</span>
@endsection

@section('content')

@php
$aspekLabels = [
    'akhlak' => 'Akhlak',
    'disiplin' => 'Disiplin',
    'tanggung_jawab' => 'Tanggung Jawab',
    'kerja_sama' => 'Kerja Sama',
    'kejujuran' => 'Kejujuran',
    'kemandirian' => 'Kemandirian',
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
            @can('create', App\Models\MonitoringKarakter::class)
                <x-btn href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.create') }}" variant="primary" icon="fa-plus">
                    Tambah Penilaian
                </x-btn>
            @endcan
        </div>
    </div>
</div>

{{-- Rekap per Aspek --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    @foreach($rekap as $aspek => $data)
    @php
        $rataRata = round($data['rata_rata'], 1);
        $skorColor = $rataRata >= 85 ? 'text-emerald-600 dark:text-emerald-400' : ($rataRata >= 70 ? 'text-blue-600 dark:text-blue-400' : ($rataRata >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400'));
    @endphp
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-1">{{ $aspekLabels[$aspek] ?? $aspek }}</p>
        @if($data['total'] > 0)
            <p class="text-2xl font-bold {{ $skorColor }}">{{ $rataRata }}</p>
            <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ $data['total'] }} penilaian</p>
        @else
            <p class="text-2xl font-bold text-gray-300 dark:text-gray-600">-</p>
            <p class="text-[11px] text-gray-400 dark:text-gray-500">Belum ada data</p>
        @endif
    </div>
    @endforeach
</div>

{{-- Riwayat Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">Riwayat Penilaian</h2>
    </div>

    @if($monitoring->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-16 px-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-heart-pulse text-gray-400 dark:text-gray-500 text-xl"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada data penilaian</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                Santri ini belum memiliki riwayat penilaian karakter.
            </p>
            @can('create', App\Models\MonitoringKarakter::class)
                <x-btn href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.create') }}" variant="primary" size="sm" icon="fa-plus">
                    Tambah Penilaian
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
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aspek</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Skor</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Predikat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Periode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dinilai Oleh</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monitoring as $m)
                    @php
                        $predikatVariant = match($m->predikat) {
                            'sangat_baik' => 'success',
                            'baik' => 'info',
                            'cukup' => 'warning',
                            'kurang' => 'danger',
                            default => 'default',
                        };
                        $predikatLabel = match($m->predikat) {
                            'sangat_baik' => 'Sangat Baik',
                            'baik' => 'Baik',
                            'cukup' => 'Cukup',
                            'kurang' => 'Kurang',
                            default => $m->predikat,
                        };
                        $skorColor = $m->skor >= 85 ? 'text-emerald-600 dark:text-emerald-400' : ($m->skor >= 70 ? 'text-blue-600 dark:text-blue-400' : ($m->skor >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400'));
                    @endphp
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group">
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">{{ $m->tanggal?->format('d M Y') ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[13px] text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{ $aspekLabels[$m->aspek] ?? $m->aspek }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-[16px] font-bold {{ $skorColor }}">{{ $m->skor }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $predikatVariant }}" size="sm">{{ $predikatLabel }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400 capitalize">{{ $m->periode ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">{{ $m->dinilaiOleh->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.show', ['monitoring' => $m->id]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                   title="Lihat">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                </a>
                                @can('update', $m)
                                    <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.edit', ['monitoring' => $m->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors"
                                       title="Edit">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </a>
                                @endcan
                                @can('delete', $m)
                                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.destroy', ['monitoring' => $m->id]) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus penilaian ini?')" class="inline">
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
