@extends('layouts.tenant')

@section('title', 'Detail Perizinan')
@section('page-title', 'Detail Perizinan')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Perizinan</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Detail</span>
@endsection

@section('content')

@php
    $isRequester = auth()->user()->santri || auth()->user()->parent;
    $jenisLabel = $perizinan->jenis === \App\Models\Perizinan::JENIS_PULANG ? 'Pulang' : 'Keluar';
    $jenisIcon = $perizinan->jenis === \App\Models\Perizinan::JENIS_PULANG ? 'fa-house' : 'fa-person-walking';
    $statusVariant = match($perizinan->status) {
        \App\Models\Perizinan::STATUS_PENDING => 'warning',
        \App\Models\Perizinan::STATUS_DISETUJUI => 'success',
        \App\Models\Perizinan::STATUS_DITOLAK => 'danger',
        \App\Models\Perizinan::STATUS_KEMBALI => 'blue',
        default => 'default',
    };
    $statusLabel = match($perizinan->status) {
        \App\Models\Perizinan::STATUS_PENDING => 'Pending',
        \App\Models\Perizinan::STATUS_DISETUJUI => 'Disetujui',
        \App\Models\Perizinan::STATUS_DITOLAK => 'Ditolak',
        \App\Models\Perizinan::STATUS_KEMBALI => 'Kembali',
        default => $perizinan->status,
    };
@endphp

{{-- Header Card --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                <i class="fa-solid {{ $jenisIcon }} text-emerald-600 dark:text-emerald-400 text-xl"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <x-badge variant="{{ $statusVariant }}" size="sm" dot>{{ $statusLabel }}</x-badge>
                    <x-badge variant="{{ $perizinan->jenis === \App\Models\Perizinan::JENIS_PULANG ? 'info' : 'default' }}" size="sm">{{ $jenisLabel }}</x-badge>
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Pengajuan {{ $jenisLabel }}</h1>
                <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $perizinan->tanggal_mulai?->format('d M Y') }}
                    @if($perizinan->tanggal_selesai)
                        - {{ $perizinan->tanggal_selesai?->format('d M Y') }}
                    @endif
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($perizinan->status === \App\Models\Perizinan::STATUS_PENDING)
                @can('update', $perizinan)
                    <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.edit', ['perizinan' => $perizinan->id]) }}"
                       class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <i class="fa-solid fa-pen mr-1.5"></i>
                        Edit
                    </a>
                @endcan

                @can('approve', $perizinan)
                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.perizinan.approve', ['perizinan' => $perizinan->id]) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors"
                                onclick="return confirm('Setujui pengajuan ini?')">
                            <i class="fa-solid fa-check mr-1.5"></i>
                            Setujui
                        </button>
                    </form>
                @endcan

                @can('reject', $perizinan)
                    <button type="button"
                            onclick="document.getElementById('reject-modal').classList.remove('hidden')"
                            class="px-4 py-2 text-[13px] font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg
                                   hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                        <i class="fa-solid fa-xmark mr-1.5"></i>
                        Tolak
                    </button>
                @endcan
            @elseif($perizinan->status === \App\Models\Perizinan::STATUS_DISETUJUI && !$perizinan->tanggal_kembali)
                @can('recordReturn', $perizinan)
                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.perizinan.return', ['perizinan' => $perizinan->id]) }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="px-4 py-2 text-[13px] font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors"
                                onclick="return confirm('Catat kepulangan santri?')">
                            <i class="fa-solid fa-house-user mr-1.5"></i>
                            Catat Kembali
                        </button>
                    </form>
                @endcan
            @endif
        </div>
    </div>

    {{-- Approved Warning --}}
    @if($perizinan->status === \App\Models\Perizinan::STATUS_DISETUJUI)
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
            <div>
                <h4 class="text-sm font-medium text-amber-900 dark:text-amber-100">Perhatian</h4>
                <p class="text-[13px] text-amber-700 dark:text-amber-300 mt-1">
                    Silakan menghadap pengurus/ustadz terlebih dahulu sebelum meninggalkan pesantren.
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Alasan --}}
    <div class="border-t border-gray-100 dark:border-gray-800 pt-6">
        <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-2">Alasan</p>
        <p class="text-[16px] font-medium text-gray-900 dark:text-gray-100">{{ $perizinan->alasan }}</p>
    </div>

    @if($perizinan->keterangan)
    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
        <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-2">Keterangan</p>
        <p class="text-[14px] text-gray-700 dark:text-gray-300">{{ $perizinan->keterangan }}</p>
    </div>
    @endif
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Main Info --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Status Timeline --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-4">Status Timeline</p>
            <div class="space-y-4">
                {{-- Diajukan --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-paper-plane text-[11px]"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100">Diajukan</p>
                        <p class="text-[12px] text-gray-500 dark:text-gray-400">{{ $perizinan->created_at?->format('d M Y H:i') }} oleh {{ $perizinan->diajukanOleh->name ?? 'Unknown' }}</p>
                    </div>
                </div>

                {{-- Disetujui/Ditolak --}}
                @if($perizinan->status !== \App\Models\Perizinan::STATUS_PENDING)
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full {{ $perizinan->status === \App\Models\Perizinan::STATUS_DISETUJUI || $perizinan->status === \App\Models\Perizinan::STATUS_KEMBALI ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400' }} flex items-center justify-center shrink-0">
                        <i class="fa-solid {{ $perizinan->status === \App\Models\Perizinan::STATUS_DISETUJUI || $perizinan->status === \App\Models\Perizinan::STATUS_KEMBALI ? 'fa-check' : 'fa-xmark' }} text-[11px]"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $perizinan->status === \App\Models\Perizinan::STATUS_DITOLAK ? 'Ditolak' : 'Disetujui' }}</p>
                        <p class="text-[12px] text-gray-500 dark:text-gray-400">{{ $perizinan->tanggal_persetujuan?->format('d M Y H:i') ?? '-' }} oleh {{ $perizinan->disetujuiOleh->name ?? 'Unknown' }}</p>
                        @if($perizinan->catatan_keamanan)
                            <p class="text-[12px] text-gray-600 dark:text-gray-400 mt-1 italic">"{{ $perizinan->catatan_keamanan }}"</p>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Kembali --}}
                @if($perizinan->tanggal_kembali)
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-house-user text-[11px]"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100">Santri Kembali</p>
                        <p class="text-[12px] text-gray-500 dark:text-gray-400">{{ $perizinan->tanggal_kembali?->format('d M Y H:i') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Sidebar Info --}}
    <div class="space-y-6">
        @if(!$isRequester)
            {{-- Metadata --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Informasi</p>
                <div class="space-y-3 text-[13px]">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">ID</span>
                        <span class="text-gray-900 dark:text-gray-100 font-mono">#{{ $perizinan->id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Dibuat</span>
                        <span class="text-gray-900 dark:text-gray-100">{{ $perizinan->created_at?->format('d M Y H:i') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">Diperbarui</span>
                        <span class="text-gray-900 dark:text-gray-100">{{ $perizinan->updated_at?->format('d M Y H:i') }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if(!$isRequester)
            {{-- Quick Actions --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-3">Aksi Cepat</p>
                <div class="space-y-2">
                    <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.by-santri', ['santriId' => $perizinan->santri_id]) }}"
                       class="block w-full px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors text-center">
                        <i class="fa-solid fa-list mr-1.5"></i>
                        Riwayat Izin Santri
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Reject Modal --}}
@can('reject', $perizinan)
@if($perizinan->status === \App\Models\Perizinan::STATUS_PENDING)
<div id="reject-modal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Tolak Pengajuan</h3>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mb-4">
            Berikan alasan penolakan untuk pengajuan {{ $perizinan->santri->name ?? 'Unknown' }}
        </p>
        <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.perizinan.reject', ['perizinan' => $perizinan->id]) }}">
            @csrf
            <textarea name="alasan" rows="3" required
                      class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                             text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                             focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors mb-4"
                      placeholder="Alasan penolakan..."></textarea>
            <div class="flex justify-end gap-2">
                <button type="button"
                        onclick="document.getElementById('reject-modal').classList.add('hidden')"
                        class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg">
                    Batal
                </button>
                <button type="submit"
                        class="px-4 py-2 text-[13px] font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg">
                    Tolak Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endcan

{{-- Back Button --}}
<div class="mt-6">
    <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.index') }}"
       class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
              hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>
        Kembali ke Daftar
    </a>
</div>

@endsection
