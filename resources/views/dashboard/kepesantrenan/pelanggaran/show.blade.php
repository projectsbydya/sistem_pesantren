@extends('layouts.tenant')

@section('title', 'Detail Pelanggaran')
@section('page-title', 'Detail Pelanggaran')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Pelanggaran</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Detail</span>
@endsection

@section('content')

@php
    $jenisVariant = match($pelanggaran->jenis) {
        'ringan' => 'default',
        'sedang' => 'warning',
        'berat' => 'danger',
        default => 'default',
    };
    $jenisLabel = match($pelanggaran->jenis) {
        'ringan' => 'Ringan',
        'sedang' => 'Sedang',
        'berat' => 'Berat',
        default => $pelanggaran->jenis,
    };
    $statusVariant = match($pelanggaran->status) {
        'pending' => 'warning',
        'diproses' => 'info',
        'selesai' => 'success',
        default => 'default',
    };
    $statusLabel = match($pelanggaran->status) {
        'pending' => 'Pending',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        default => $pelanggaran->status,
    };
@endphp

{{-- Header Card --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl {{ $jenisVariant === 'danger' ? 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400' : ($jenisVariant === 'warning' ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400') }} flex items-center justify-center">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <x-badge variant="{{ $jenisVariant }}" size="sm">{{ $jenisLabel }}</x-badge>
                    <x-badge variant="{{ $statusVariant }}" size="sm" dot>{{ $statusLabel }}</x-badge>
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Pelanggaran {{ $pelanggaran->kategori }}</h1>
                <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $pelanggaran->tanggal?->format('d M Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('update', $pelanggaran)
                <a href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.edit', ['pelanggaran' => $pelanggaran->id]) }}"
                   class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <i class="fa-solid fa-pen mr-1.5"></i>
                    Edit
                </a>
            @endcan

            @if($pelanggaran->status === 'pending')
                @can('process', $pelanggaran)
                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.process', ['pelanggaran' => $pelanggaran->id]) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 text-[13px] font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                                onclick="return confirm('Proses pelanggaran ini?')">
                            <i class="fa-solid fa-play mr-1.5"></i>
                            Proses
                        </button>
                    </form>
                @endcan
            @elseif($pelanggaran->status === 'diproses')
                @can('complete', $pelanggaran)
                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.complete', ['pelanggaran' => $pelanggaran->id]) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors"
                                onclick="return confirm('Selesaikan pelanggaran ini?')">
                            <i class="fa-solid fa-check mr-1.5"></i>
                            Selesai
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
                        {{ strtoupper(substr($pelanggaran->santri->name ?? 'S', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-[15px] font-medium text-gray-900 dark:text-gray-100">{{ $pelanggaran->santri->name ?? 'Unknown' }}</p>
                        <p class="text-[12px] text-gray-500 dark:text-gray-400 font-mono">{{ $pelanggaran->santri->nis ?? '-' }}</p>
                    </div>
                </div>
                <a href="{{ tenant_route('dashboard.santri.show', ['id' => $pelanggaran->santri_id]) }}"
                   class="px-3 py-1.5 text-[12px] text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                    <i class="fa-solid fa-user mr-1"></i>
                    Lihat Santri
                </a>
            </div>
        </div>

        {{-- Deskripsi --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Deskripsi Kejadian</p>
            <p class="text-[14px] text-gray-700 dark:text-gray-300 leading-relaxed">{{ $pelanggaran->deskripsi }}</p>
            @if($pelanggaran->lokasi)
                <div class="mt-4 flex items-center gap-2 text-[12px] text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Lokasi: {{ $pelanggaran->lokasi }}</span>
                </div>
            @endif
        </div>

        {{-- Tindak Lanjut --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Tindak Lanjut</p>
            @if($pelanggaran->tindak_lanjut)
                <p class="text-[14px] text-gray-700 dark:text-gray-300 leading-relaxed">{{ $pelanggaran->tindak_lanjut }}</p>
            @else
                <p class="text-[13px] text-gray-400 dark:text-gray-500 italic">Belum ada tindak lanjut</p>
            @endif
        </div>

        {{-- Sanksi Terkait --}}
        @if($pelanggaran->sanksi->count() > 0)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Sanksi Terkait</p>
                    <a href="{{ tenant_route('dashboard.kepesantrenan.sanksi.index', ['pelanggaran_id' => $pelanggaran->id]) }}"
                       class="text-[12px] text-emerald-600 dark:text-emerald-400 hover:underline">
                        Lihat Semua
                    </a>
                </div>
                <div class="space-y-3">
                    @foreach($pelanggaran->sanksi as $sanksi)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div>
                                <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $sanksi->jenis }}</p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $sanksi->deskripsi }}</p>
                            </div>
                            <x-badge variant="{{ $sanksi->status === 'aktif' ? 'warning' : ($sanksi->status === 'selesai' ? 'success' : 'default') }}" size="sm">
                                {{ $sanksi->status === 'aktif' ? 'Aktif' : ($sanksi->status === 'selesai' ? 'Selesai' : $sanksi->status) }}
                            </x-badge>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Sidebar Info --}}
    <div class="space-y-6">
        {{-- Pelapor --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Dilaporkan Oleh</p>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-[12px] font-bold">
                    {{ strtoupper(substr($pelanggaran->pelapor->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $pelanggaran->pelapor->name ?? 'Unknown' }}</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $pelanggaran->pelapor->email ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Informasi</p>
            <div class="space-y-3 text-[13px]">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">ID</span>
                    <span class="text-gray-900 dark:text-gray-100 font-mono">#{{ $pelanggaran->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Dibuat</span>
                    <span class="text-gray-900 dark:text-gray-100">{{ $pelanggaran->created_at?->format('d M Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Diperbarui</span>
                    <span class="text-gray-900 dark:text-gray-100">{{ $pelanggaran->updated_at?->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Aksi Cepat</p>
            <div class="space-y-2">
                @can('create', App\Models\Sanksi::class)
                    <a href="{{ tenant_route('dashboard.kepesantrenan.sanksi.create', ['pelanggaran_id' => $pelanggaran->id]) }}"
                       class="block w-full px-4 py-2 text-[13px] font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-lg
                              hover:bg-amber-100 dark:hover:bg-amber-500/20 transition-colors text-center">
                        <i class="fa-solid fa-gavel mr-1.5"></i>
                        Berikan Sanksi
                    </a>
                @endcan

                <a href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.by-santri', ['santriId' => $pelanggaran->santri_id]) }}"
                   class="block w-full px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-center">
                    <i class="fa-solid fa-list mr-1.5"></i>
                    Riwayat Pelanggaran Santri
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Back Button --}}
<div class="mt-6">
    <a href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.index') }}"
       class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
              hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>
        Kembali ke Daftar
    </a>
</div>

@endsection
