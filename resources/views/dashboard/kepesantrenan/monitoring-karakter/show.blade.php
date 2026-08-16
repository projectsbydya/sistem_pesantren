@extends('layouts.tenant')

@section('title', 'Detail Penilaian Karakter')
@section('page-title', 'Detail Penilaian')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Monitoring</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Detail</span>
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
$predikatVariant = match($monitoring->predikat) {
    'sangat_baik' => 'success',
    'baik' => 'info',
    'cukup' => 'warning',
    'kurang' => 'danger',
    default => 'default',
};
$predikatLabel = match($monitoring->predikat) {
    'sangat_baik' => 'Sangat Baik',
    'baik' => 'Baik',
    'cukup' => 'Cukup',
    'kurang' => 'Kurang',
    default => $monitoring->predikat,
};
$skorColor = $monitoring->skor >= 85 ? 'text-emerald-600 dark:text-emerald-400' : ($monitoring->skor >= 70 ? 'text-blue-600 dark:text-blue-400' : ($monitoring->skor >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400'));
@endphp

{{-- Header Card --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                <i class="fa-solid fa-heart-pulse text-emerald-600 dark:text-emerald-400 text-xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[12px] text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded">{{ $aspekLabels[$monitoring->aspek] ?? $monitoring->aspek }}</span>
                    @if($monitoring->periode)
                        <span class="text-[12px] text-gray-500 dark:text-gray-400 capitalize">{{ $monitoring->periode }}</span>
                    @endif
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Penilaian Karakter</h1>
                <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $monitoring->tanggal?->format('d M Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('update', $monitoring)
                <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.edit', ['monitoring' => $monitoring->id]) }}"
                   class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <i class="fa-solid fa-pen mr-1.5"></i>
                    Edit
                </a>
            @endcan
        </div>
    </div>

    {{-- Score Display --}}
    <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
        <div class="flex items-center gap-6">
            <div class="text-center">
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-1">Skor</p>
                <p class="text-4xl font-bold {{ $skorColor }}">{{ $monitoring->skor }}</p>
            </div>
            <div class="h-12 w-px bg-gray-200 dark:bg-gray-700"></div>
            <div>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-1">Predikat</p>
                <x-badge variant="{{ $predikatVariant }}" size="lg">{{ $predikatLabel }}</x-badge>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Info --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Santri Info --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Santri Dinilai</p>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[14px] font-bold">
                        {{ strtoupper(substr($monitoring->santri->name ?? 'S', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-[15px] font-medium text-gray-900 dark:text-gray-100">{{ $monitoring->santri->name ?? 'Unknown' }}</p>
                        <p class="text-[12px] text-gray-500 dark:text-gray-400 font-mono">{{ $monitoring->santri->nis ?? '-' }}</p>
                    </div>
                </div>
                <a href="{{ tenant_route('dashboard.santri.show', ['id' => $monitoring->santri_id]) }}"
                   class="px-3 py-1.5 text-[12px] text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                    <i class="fa-solid fa-user mr-1"></i>
                    Lihat Santri
                </a>
            </div>
        </div>

        {{-- Deskripsi --}}
        @if($monitoring->deskripsi)
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Deskripsi / Catatan</p>
            <p class="text-[14px] text-gray-700 dark:text-gray-300 leading-relaxed">{{ $monitoring->deskripsi }}</p>
        </div>
        @endif

        {{-- Score Interpretation --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Kriteria Penilaian</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div class="p-3 bg-emerald-50 dark:bg-emerald-500/10 rounded-lg text-center">
                    <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mb-1">Sangat Baik</p>
                    <p class="text-[14px] font-bold text-gray-900 dark:text-gray-100">85-100</p>
                </div>
                <div class="p-3 bg-blue-50 dark:bg-blue-500/10 rounded-lg text-center">
                    <p class="text-[11px] text-blue-600 dark:text-blue-400 mb-1">Baik</p>
                    <p class="text-[14px] font-bold text-gray-900 dark:text-gray-100">70-84</p>
                </div>
                <div class="p-3 bg-amber-50 dark:bg-amber-500/10 rounded-lg text-center">
                    <p class="text-[11px] text-amber-600 dark:text-amber-400 mb-1">Cukup</p>
                    <p class="text-[14px] font-bold text-gray-900 dark:text-gray-100">60-69</p>
                </div>
                <div class="p-3 bg-red-50 dark:bg-red-500/10 rounded-lg text-center">
                    <p class="text-[11px] text-red-600 dark:text-red-400 mb-1">Kurang</p>
                    <p class="text-[14px] font-bold text-gray-900 dark:text-gray-100">&lt;60</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar Info --}}
    <div class="space-y-6">
        {{-- Penilai --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Dinilai Oleh</p>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-[12px] font-bold">
                    {{ strtoupper(substr($monitoring->dinilaiOleh->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $monitoring->dinilaiOleh->name ?? 'Unknown' }}</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $monitoring->dinilaiOleh->email ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Informasi</p>
            <div class="space-y-3 text-[13px]">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">ID</span>
                    <span class="text-gray-900 dark:text-gray-100 font-mono">#{{ $monitoring->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Aspek</span>
                    <span class="text-gray-900 dark:text-gray-100">{{ $aspekLabels[$monitoring->aspek] ?? $monitoring->aspek }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Periode</span>
                    <span class="text-gray-900 dark:text-gray-100 capitalize">{{ $monitoring->periode ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Dibuat</span>
                    <span class="text-gray-900 dark:text-gray-100">{{ $monitoring->created_at?->format('d M Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Diperbarui</span>
                    <span class="text-gray-900 dark:text-gray-100">{{ $monitoring->updated_at?->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Aksi Cepat</p>
            <div class="space-y-2">
                <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.by-santri', ['santriId' => $monitoring->santri_id]) }}"
                   class="block w-full px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-center">
                    <i class="fa-solid fa-chart-line mr-1.5"></i>
                    Riwayat Penilaian Santri
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Back Button --}}
<div class="mt-6">
    <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.index') }}"
       class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
              hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>
        Kembali ke Daftar
    </a>
</div>

@endsection
