@extends('layouts.tenant')

@section('title', 'Detail Mutasi')
@section('page-title', 'Detail Mutasi')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.mutasi.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Mutasi</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Detail</span>
@endsection

@section('content')

{{-- Header Card --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 mb-6">
    <div class="flex items-center gap-4 mb-6">
        <div class="w-14 h-14 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
            <i class="fa-solid fa-people-arrows text-emerald-600 dark:text-emerald-400 text-xl"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Detail Mutasi Kamar</h1>
            <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
                {{ $mutasi->tanggal_mutasi?->format('d M Y') }} - {{ $mutasi->tanggal_mutasi?->format('H:i') ?? '-' }}
            </p>
        </div>
    </div>

    {{-- Santri Info --}}
    <div class="border-t border-gray-100 dark:border-gray-800 pt-6 mb-6">
        <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Santri</p>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[14px] font-bold">
                {{ strtoupper(substr($mutasi->santri->name ?? 'S', 0, 1)) }}
            </div>
            <div>
                <p class="text-[15px] font-medium text-gray-900 dark:text-gray-100">{{ $mutasi->santri->name ?? 'Unknown' }}</p>
                <p class="text-[12px] text-gray-500 dark:text-gray-400 font-mono">{{ $mutasi->santri->nis ?? '-' }}</p>
            </div>
            <a href="{{ tenant_route('dashboard.santri.show', ['id' => $mutasi->santri_id]) }}"
               class="ml-auto px-3 py-1.5 text-[12px] text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                <i class="fa-solid fa-user mr-1"></i>
                Lihat Santri
            </a>
        </div>
    </div>

    {{-- Movement Visualization --}}
    <div class="border-t border-gray-100 dark:border-gray-800 pt-6">
        <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-4">Perpindahan</p>
        <div class="flex items-center gap-4">
            {{-- From --}}
            <div class="flex-1 bg-gray-50 dark:bg-gray-800 rounded-xl p-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                        <i class="fa-solid fa-bed text-gray-500 dark:text-gray-400"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Dari Kamar</p>
                        <p class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">{{ $mutasi->kamarAsal->name ?? '-' }}</p>
                    </div>
                </div>
                @if($mutasi->kamarAsal)
                    <p class="text-[12px] text-gray-500 dark:text-gray-400 mt-2">
                        <i class="fa-solid fa-location-dot mr-1"></i>
                        {{ $mutasi->kamarAsal->lokasi ?? 'Lokasi tidak tersedia' }}
                    </p>
                @endif
            </div>

            {{-- Arrow --}}
            <div class="flex-shrink-0">
                <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                    <i class="fa-solid fa-arrow-right text-emerald-600 dark:text-emerald-400"></i>
                </div>
            </div>

            {{-- To --}}
            <div class="flex-1 bg-emerald-50 dark:bg-emerald-500/5 rounded-xl p-4 border border-emerald-100 dark:border-emerald-500/20">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-bed text-emerald-600 dark:text-emerald-400"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-emerald-600 dark:text-emerald-400">Ke Kamar</p>
                        <p class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">{{ $mutasi->kamarTujuan->name ?? 'Unknown' }}</p>
                    </div>
                </div>
                @if($mutasi->kamarTujuan)
                    <p class="text-[12px] text-gray-500 dark:text-gray-400 mt-2">
                        <i class="fa-solid fa-location-dot mr-1"></i>
                        {{ $mutasi->kamarTujuan->lokasi ?? 'Lokasi tidak tersedia' }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Details Card --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">Informasi Tambahan</h2>
    </div>

    <div class="p-6 space-y-4">
        {{-- Alasan --}}
        <div class="flex justify-between items-start">
            <div>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Alasan Mutasi</p>
                <p class="text-[14px] text-gray-900 dark:text-gray-100 mt-1">{{ $mutasi->alasan }}</p>
            </div>
            <span class="px-3 py-1 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg text-[12px]">
                {{ $mutasi->alasan }}
            </span>
        </div>

        {{-- Keterangan --}}
        @if($mutasi->keterangan)
            <div class="border-t border-gray-100 dark:border-gray-800 pt-4">
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-2">Keterangan</p>
                <p class="text-[13px] text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800 rounded-lg p-3">
                    {{ $mutasi->keterangan }}
                </p>
            </div>
        @endif

        {{-- Processed By --}}
        <div class="border-t border-gray-100 dark:border-gray-800 pt-4">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-2">Dicatat Oleh</p>
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-[11px] font-bold">
                    {{ strtoupper(substr($mutasi->processedBy->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $mutasi->processedBy->name ?? 'Unknown' }}</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $mutasi->processedBy->email ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="border-t border-gray-100 dark:border-gray-800 pt-4 flex gap-6 text-[11px] text-gray-500 dark:text-gray-400">
            <span><i class="fa-solid fa-clock mr-1"></i> Dibuat: {{ $mutasi->created_at?->format('d M Y H:i') ?? '-' }}</span>
            <span><i class="fa-solid fa-hashtag mr-1"></i> ID: {{ $mutasi->id }}</span>
        </div>
    </div>
</div>

{{-- Actions --}}
<div class="mt-6 flex items-center justify-between">
    <a href="{{ tenant_route('dashboard.kepesantrenan.mutasi.index') }}"
       class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
              hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>
        Kembali
    </a>

    @if($currentPlacement)
        <a href="{{ tenant_route('dashboard.kepesantrenan.penempatan.show', ['penempatan' => $currentPlacement->id]) }}"
           class="px-4 py-2 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
            <i class="fa-solid fa-clock-rotate-left mr-1.5"></i>
            Lihat History Santri
        </a>
    @endif
</div>

@endsection
