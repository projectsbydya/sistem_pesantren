@extends('layouts.tenant')

@section('title', 'Detail Sanksi')
@section('page-title', 'Detail Sanksi')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.sanksi.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Sanksi</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Detail</span>
@endsection

@section('content')

@php
    $jenisLabel = match($sanksi->jenis) {
        'peringatan' => 'Peringatan',
        'tugas' => 'Tugas Khusus',
        'skorsing' => 'Skorsing',
        'dikembalikan' => 'Dikembalikan',
        default => $sanksi->jenis,
    };
    $jenisVariant = match($sanksi->jenis) {
        'peringatan' => 'warning',
        'tugas' => 'info',
        'skorsing' => 'danger',
        'dikembalikan' => 'danger',
        default => 'default',
    };
    $statusVariant = match($sanksi->status) {
        'aktif' => 'warning',
        'selesai' => 'success',
        'dibatalkan' => 'default',
        default => 'default',
    };
    $statusLabel = match($sanksi->status) {
        'aktif' => 'Aktif',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        default => $sanksi->status,
    };
@endphp

{{-- Header Card --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl {{ $jenisVariant === 'danger' ? 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400' : ($jenisVariant === 'warning' ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400') }} flex items-center justify-center">
                <i class="fa-solid fa-gavel text-xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <x-badge variant="{{ $jenisVariant }}" size="sm">{{ $jenisLabel }}</x-badge>
                    <x-badge variant="{{ $statusVariant }}" size="sm" dot>{{ $statusLabel }}</x-badge>
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Sanksi {{ $jenisLabel }}</h1>
                <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $sanksi->tanggal_mulai?->format('d M Y') }}
                    @if($sanksi->tanggal_selesai)
                        - {{ $sanksi->tanggal_selesai?->format('d M Y') }}
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('update', $sanksi)
                <a href="{{ tenant_route('dashboard.kepesantrenan.sanksi.edit', ['sanksi' => $sanksi->id]) }}"
                   class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <i class="fa-solid fa-pen mr-1.5"></i>
                    Edit
                </a>
            @endcan

            @if($sanksi->status === 'aktif')
                @can('complete', $sanksi)
                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.sanksi.complete', ['sanksi' => $sanksi->id]) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors"
                                onclick="return confirm('Tandai sanksi ini sebagai selesai?')">
                            <i class="fa-solid fa-check mr-1.5"></i>
                            Selesai
                        </button>
                    </form>
                @endcan

                @can('cancel', $sanksi)
                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.sanksi.cancel', ['sanksi' => $sanksi->id]) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
                                onclick="return confirm('Batalkan sanksi ini?')">
                            <i class="fa-solid fa-xmark mr-1.5"></i>
                            Batalkan
                        </button>
                    </form>
                @endcan
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Info --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Santri Info --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Santri Bersangkutan</p>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[14px] font-bold">
                        {{ strtoupper(substr($sanksi->pelanggaran->santri->name ?? 'S', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-[15px] font-medium text-gray-900 dark:text-gray-100">{{ $sanksi->pelanggaran->santri->name ?? 'Unknown' }}</p>
                        <p class="text-[12px] text-gray-500 dark:text-gray-400 font-mono">{{ $sanksi->pelanggaran->santri->nis ?? '-' }}</p>
                    </div>
                </div>
                <a href="{{ tenant_route('dashboard.santri.show', ['id' => $sanksi->pelanggaran->santri_id]) }}"
                   class="px-3 py-1.5 text-[12px] text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                    <i class="fa-solid fa-user mr-1"></i>
                    Lihat Santri
                </a>
            </div>
        </div>

        {{-- Deskripsi --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Deskripsi Sanksi</p>
            <p class="text-[14px] text-gray-700 dark:text-gray-300 leading-relaxed">{{ $sanksi->deskripsi }}</p>
        </div>

        {{-- Hasil Evaluasi --}}
        @if($sanksi->hasil_evaluasi)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Hasil Evaluasi</p>
                <p class="text-[14px] text-gray-700 dark:text-gray-300 leading-relaxed">{{ $sanksi->hasil_evaluasi }}</p>
            </div>
        @endif
    </div>

    {{-- Sidebar Info --}}
    <div class="space-y-6">
        {{-- Pelanggaran Terkait --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <div class="flex items-center justify-between mb-3">
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Pelanggaran Terkait</p>
                <x-badge variant="{{ $sanksi->pelanggaran->jenis === 'berat' ? 'danger' : ($sanksi->pelanggaran->jenis === 'sedang' ? 'warning' : 'default') }}" size="sm">
                    {{ $sanksi->pelanggaran->jenis === 'ringan' ? 'Ringan' : ($sanksi->pelanggaran->jenis === 'sedang' ? 'Sedang' : 'Berat') }}
                </x-badge>
            </div>
            <p class="text-[14px] font-medium text-gray-900 dark:text-gray-100 mb-1">{{ $sanksi->pelanggaran->kategori }}</p>
            <p class="text-[12px] text-gray-500 dark:text-gray-400 mb-3">{{ Str::limit($sanksi->pelanggaran->deskripsi, 80) }}</p>
            <a href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.show', ['pelanggaran' => $sanksi->pelanggaran_id]) }}"
               class="text-[12px] text-emerald-600 dark:text-emerald-400 hover:underline">
                Lihat Detail Pelanggaran
            </a>
        </div>

        {{-- Pemberi Sanksi --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Diberikan Oleh</p>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-[12px] font-bold">
                    {{ strtoupper(substr($sanksi->diberikanOleh->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $sanksi->diberikanOleh->name ?? 'Unknown' }}</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $sanksi->diberikanOleh->email ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Informasi</p>
            <div class="space-y-3 text-[13px]">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">ID</span>
                    <span class="text-gray-900 dark:text-gray-100 font-mono">#{{ $sanksi->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Dibuat</span>
                    <span class="text-gray-900 dark:text-gray-100">{{ $sanksi->created_at?->format('d M Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Diperbarui</span>
                    <span class="text-gray-900 dark:text-gray-100">{{ $sanksi->updated_at?->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Back Button --}}
<div class="mt-6">
    <a href="{{ tenant_route('dashboard.kepesantrenan.sanksi.index') }}"
       class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
              hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>
        Kembali ke Daftar
    </a>
</div>

@endsection
