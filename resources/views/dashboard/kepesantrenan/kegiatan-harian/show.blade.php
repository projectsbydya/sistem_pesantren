@extends('layouts.tenant')

@section('title', 'Detail Kegiatan')
@section('page-title', 'Detail Kegiatan')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Kegiatan</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Detail</span>
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
$statusVariant = match($kegiatan->status) {
    'terjadwal' => 'warning',
    'dilaksanakan' => 'success',
    'tidak_dilaksanakan' => 'danger',
    default => 'default',
};
$statusLabel = match($kegiatan->status) {
    'terjadwal' => 'Terjadwal',
    'dilaksanakan' => 'Dilaksanakan',
    'tidak_dilaksanakan' => 'Tidak Dilaksanakan',
    default => $kegiatan->status,
};
$kategoriVariant = match($kegiatan->kategori) {
    'wajib' => 'danger',
    'sunnah' => 'info',
    'ekstra' => 'default',
    default => 'default',
};
@endphp

{{-- Header Card --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                <i class="fa-solid fa-clipboard-check text-emerald-600 dark:text-emerald-400 text-xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <x-badge variant="{{ $statusVariant }}" size="sm" dot>{{ $statusLabel }}</x-badge>
                    <x-badge variant="{{ $kategoriVariant }}" size="sm">{{ $kegiatan->kategori }}</x-badge>
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $jenisLabels[$kegiatan->jenis_kegiatan] ?? $kegiatan->jenis_kegiatan }}</h1>
                <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $kegiatan->tanggal?->format('d M Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($kegiatan->status === 'terjadwal')
                @can('markStatus', $kegiatan)
                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.done', ['kegiatan' => $kegiatan->id]) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors"
                                onclick="return confirm('Tandai kegiatan sebagai selesai?')">
                            <i class="fa-solid fa-check mr-1.5"></i>
                            Tandai Selesai
                        </button>
                    </form>
                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.missed', ['kegiatan' => $kegiatan->id]) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 text-[13px] font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg
                                       hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors"
                                onclick="return confirm('Tandai kegiatan sebagai tidak dilaksanakan?')">
                            <i class="fa-solid fa-xmark mr-1.5"></i>
                            Tidak Dilaksanakan
                        </button>
                    </form>
                @endcan
            @endif

            @can('update', $kegiatan)
                <a href="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.edit', ['kegiatan' => $kegiatan->id]) }}"
                   class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <i class="fa-solid fa-pen mr-1.5"></i>
                    Edit
                </a>
            @endcan
        </div>
    </div>

    {{-- Waktu --}}
    @if($kegiatan->waktu_mulai || $kegiatan->waktu_selesai)
    <div class="border-t border-gray-100 dark:border-gray-800 pt-6">
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-2">
                <i class="fa-regular fa-clock text-gray-400"></i>
                <span class="text-[14px] text-gray-700 dark:text-gray-300">
                    {{ $kegiatan->waktu_mulai ?? '-' }} {{ $kegiatan->waktu_selesai ? '- ' . $kegiatan->waktu_selesai : '' }}
                </span>
            </div>
        </div>
    </div>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Info --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Santri Info --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Santri</p>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[14px] font-bold">
                        {{ strtoupper(substr($kegiatan->santri->name ?? 'S', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-[15px] font-medium text-gray-900 dark:text-gray-100">{{ $kegiatan->santri->name ?? 'Unknown' }}</p>
                        <p class="text-[12px] text-gray-500 dark:text-gray-400 font-mono">{{ $kegiatan->santri->nis ?? '-' }}</p>
                    </div>
                </div>
                <a href="{{ tenant_route('dashboard.santri.show', ['id' => $kegiatan->santri_id]) }}"
                   class="px-3 py-1.5 text-[12px] text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                    <i class="fa-solid fa-user mr-1"></i>
                    Lihat Santri
                </a>
            </div>
        </div>

        {{-- Catatan --}}
        @if($kegiatan->catatan)
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Catatan</p>
            <p class="text-[14px] text-gray-700 dark:text-gray-300 leading-relaxed">{{ $kegiatan->catatan }}</p>
        </div>
        @endif

        {{-- Status Timeline --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-4">Status</p>
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full {{ $kegiatan->status === 'terjadwal' ? 'bg-amber-500' : ($kegiatan->status === 'dilaksanakan' ? 'bg-emerald-500' : 'bg-red-500') }}"></div>
                    <span class="text-[14px] font-medium text-gray-900 dark:text-gray-100">{{ $statusLabel }}</span>
                </div>
            </div>
            @if($kegiatan->status !== 'terjadwal')
            <p class="mt-3 text-[12px] text-gray-500 dark:text-gray-400">
                Status diperbarui oleh {{ $kegiatan->dicatatOleh->name ?? 'Unknown' }}
            </p>
            @endif
        </div>
    </div>

    {{-- Sidebar Info --}}
    <div class="space-y-6">
        {{-- Pencatat --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Dicatat Oleh</p>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center text-[12px] font-bold">
                    {{ strtoupper(substr($kegiatan->dicatatOleh->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $kegiatan->dicatatOleh->name ?? 'Unknown' }}</p>
                    <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $kegiatan->dicatatOleh->email ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Informasi</p>
            <div class="space-y-3 text-[13px]">
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">ID</span>
                    <span class="text-gray-900 dark:text-gray-100 font-mono">#{{ $kegiatan->id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Jenis</span>
                    <span class="text-gray-900 dark:text-gray-100">{{ $jenisLabels[$kegiatan->jenis_kegiatan] ?? $kegiatan->jenis_kegiatan }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Kategori</span>
                    <span class="text-gray-900 dark:text-gray-100 capitalize">{{ $kegiatan->kategori }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Dibuat</span>
                    <span class="text-gray-900 dark:text-gray-100">{{ $kegiatan->created_at?->format('d M Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500 dark:text-gray-400">Diperbarui</span>
                    <span class="text-gray-900 dark:text-gray-100">{{ $kegiatan->updated_at?->format('d M Y H:i') }}</span>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Aksi Cepat</p>
            <div class="space-y-2">
                <a href="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.by-santri', ['santriId' => $kegiatan->santri_id]) }}"
                   class="block w-full px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-center">
                    <i class="fa-solid fa-list mr-1.5"></i>
                    Riwayat Kegiatan Santri
                </a>
            </div>
        </div>
    </div>
</div>

{{-- Back Button --}}
<div class="mt-6">
    <a href="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.index') }}"
       class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
              hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>
        Kembali ke Daftar
    </a>
</div>

@endsection
