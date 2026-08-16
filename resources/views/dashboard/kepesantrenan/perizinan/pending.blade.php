@extends('layouts.tenant')

@section('title', 'Perizinan Pending')
@section('page-title', 'Perizinan Pending')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Perizinan</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Pending</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Pengajuan Pending</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            Daftar pengajuan izin yang menunggu persetujuan
        </p>
    </div>
    <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.index') }}"
       class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
              hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>
        Kembali
    </a>
</div>

{{-- Search --}}
<form method="GET" action="{{ tenant_route('dashboard.kepesantrenan.perizinan.pending') }}" class="flex gap-3 mb-5">
    <div class="flex-1 max-w-md">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama santri atau NIS..."
                   class="w-full pl-9 pr-4 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                          focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors">
        </div>
    </div>
    @if(request('search'))
        <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.pending') }}" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
            <i class="fa-solid fa-xmark text-xs"></i>
        </a>
    @endif
</form>

{{-- Pending Cards --}}
@if($perizinan->isEmpty())
    <div class="text-center py-16 px-6 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
        <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
            <i class="fa-solid fa-check text-emerald-500 text-xl"></i>
        </div>
        <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100 mb-1">Tidak ada pengajuan pending</h3>
        <p class="text-[13px] text-gray-500 dark:text-gray-400">
            Semua pengajuan izin telah diproses.
        </p>
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($perizinan as $p)
        @php
            $jenisLabel = $p->jenis === \App\Models\Perizinan::JENIS_PULANG ? 'Pulang' : 'Keluar';
            $jenisIcon = $p->jenis === \App\Models\Perizinan::JENIS_PULANG ? 'fa-house' : 'fa-person-walking';
            $isOverdue = $p->tanggal_mulai < now()->startOfDay();
        @endphp
        <div class="bg-white dark:bg-gray-900 rounded-xl border {{ $isOverdue ? 'border-red-200 dark:border-red-800' : 'border-gray-200 dark:border-gray-800' }} overflow-hidden">
            <div class="p-5">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[14px] font-bold">
                            {{ strtoupper(substr($p->santri->name ?? 'S', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-[14px] font-medium text-gray-900 dark:text-gray-100">{{ $p->santri->name ?? 'Unknown' }}</p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400 font-mono">{{ $p->santri->nis ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-badge variant="warning" size="sm" dot>Pending</x-badge>
                        <x-badge variant="{{ $p->jenis === \App\Models\Perizinan::JENIS_PULANG ? 'info' : 'default' }}" size="sm">
                            <i class="fa-solid {{ $jenisIcon }} mr-1 text-[10px]"></i>{{ $jenisLabel }}
                        </x-badge>
                    </div>
                </div>

                <div class="space-y-2 mb-4">
                    <div class="flex items-center gap-2 text-[13px]">
                        <i class="fa-regular fa-calendar text-gray-400 w-4"></i>
                        <span class="text-gray-700 dark:text-gray-300">
                            {{ $p->tanggal_mulai?->format('d M Y') }}
                            @if($p->tanggal_selesai)
                                - {{ $p->tanggal_selesai?->format('d M Y') }}
                            @endif
                            @if($isOverdue)
                                <span class="text-red-500 ml-2 text-[11px]">(Lewat tanggal)</span>
                            @endif
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-[13px]">
                        <i class="fa-solid fa-comment text-gray-400 w-4"></i>
                        <span class="text-gray-700 dark:text-gray-300 truncate">{{ $p->alasan }}</span>
                    </div>
                    @if($p->destinasi)
                    <div class="flex items-center gap-2 text-[13px]">
                        <i class="fa-solid fa-location-dot text-gray-400 w-4"></i>
                        <span class="text-gray-700 dark:text-gray-300">{{ $p->destinasi }}</span>
                    </div>
                    @endif
                    @if($p->penjemput)
                    <div class="flex items-center gap-2 text-[13px]">
                        <i class="fa-solid fa-user text-gray-400 w-4"></i>
                        <span class="text-gray-700 dark:text-gray-300">{{ $p->penjemput }} ({{ $p->no_hp_penjemput ?? '-' }})</span>
                    </div>
                    @endif
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100 dark:border-gray-800">
                    <div class="text-[11px] text-gray-500 dark:text-gray-400">
                        Diajukan {{ $p->created_at?->diffForHumans() }} oleh {{ $p->diajukanOleh->name ?? 'Unknown' }}
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.show', ['perizinan' => $p->id]) }}"
                           class="px-3 py-1.5 text-[12px] text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            Detail
                        </a>
                        @can('approve', $p)
                            <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.perizinan.approve', ['perizinan' => $p->id]) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        class="px-3 py-1.5 text-[12px] text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors"
                                        onclick="return confirm('Setujui pengajuan ini?')">
                                    <i class="fa-solid fa-check mr-1"></i>Setuju
                                </button>
                            </form>
                        @endcan
                        @can('reject', $p)
                            <button type="button"
                                    onclick="document.getElementById('reject-modal-{{ $p->id }}').classList.remove('hidden')"
                                    class="px-3 py-1.5 text-[12px] text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                                <i class="fa-solid fa-xmark mr-1"></i>Tolak
                            </button>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        {{-- Reject Modal --}}
        @can('reject', $p)
        <div id="reject-modal-{{ $p->id }}" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Tolak Pengajuan</h3>
                <p class="text-[13px] text-gray-500 dark:text-gray-400 mb-4">
                    Berikan alasan penolakan untuk pengajuan {{ $p->santri->name ?? 'Unknown' }}
                </p>
                <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.perizinan.reject', ['perizinan' => $p->id]) }}">
                    @csrf
                    <textarea name="alasan" rows="3" required
                              class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                                     text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                                     focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors mb-4"
                              placeholder="Alasan penolakan..."></textarea>
                    <div class="flex justify-end gap-2">
                        <button type="button"
                                onclick="document.getElementById('reject-modal-{{ $p->id }}').classList.add('hidden')"
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
        @endcan
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-6 flex items-center justify-between">
        <p class="text-[12px] text-gray-400 dark:text-gray-500">
            Menampilkan {{ $perizinan->firstItem() ?? 0 }} - {{ $perizinan->lastItem() ?? 0 }} dari {{ $perizinan->total() }} pending
        </p>
        <div class="flex gap-1">
            {{ $perizinan->links('pagination::simple-tailwind') }}
        </div>
    </div>
@endif

@endsection
