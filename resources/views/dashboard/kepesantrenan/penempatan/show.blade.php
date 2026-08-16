@extends('layouts.tenant')

@section('title', 'History Penempatan')
@section('page-title', 'History Penempatan')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.penempatan.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Penempatan</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">History</span>
@endsection

@section('content')

{{-- Header Card --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                <i class="fa-solid fa-user text-emerald-600 dark:text-emerald-400 text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $penempatan->santri->name ?? 'Unknown' }}</h1>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-[12px] text-gray-500 dark:text-gray-400 font-mono">{{ $penempatan->santri->nis ?? '-' }}</span>
                    @if($penempatan->santri->gender ?? false)
                        <x-badge variant="{{ $penempatan->santri->gender === 'L' ? 'info' : 'pink' }}" size="sm">
                            {{ $penempatan->santri->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </x-badge>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            {{-- Current status --}}
            @if($penempatan->santri->kamar_id ?? false)
                <div class="px-3 py-1.5 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-lg">
                    <span class="text-[12px] text-emerald-700 dark:text-emerald-300">
                        <i class="fa-solid fa-bed mr-1"></i>
                        Saat ini: {{ $penempatan->santri->kamar->name ?? 'Unknown' }}
                    </span>
                </div>
            @else
                <div class="px-3 py-1.5 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <span class="text-[12px] text-gray-600 dark:text-gray-400">
                        <i class="fa-solid fa-house-circle-xmark mr-1"></i>
                        Belum punya kamar
                    </span>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- History Timeline --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">Riwayat Penempatan</h2>
        <p class="text-[12px] text-gray-500 dark:text-gray-400 mt-0.5">
            Catatan penempatan kamar santri (termasuk yang sudah checkout)
        </p>
    </div>

    @if($history->isEmpty())
        <div class="text-center py-12 px-6">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-clock-rotate-left text-gray-400 dark:text-gray-500 text-lg"></i>
            </div>
            <h3 class="text-[14px] font-medium text-gray-900 dark:text-gray-100 mb-1">Belum ada riwayat</h3>
            <p class="text-[12px] text-gray-500 dark:text-gray-400">
                Santri ini belum pernah ditempatkan di kamar.
            </p>
        </div>
    @else
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($history as $h)
                <div class="p-6 flex items-start gap-4">
                    {{-- Timeline indicator --}}
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full {{ $h->tanggal_keluar ? 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' }} flex items-center justify-center shrink-0">
                            @if($h->tanggal_keluar)
                                <i class="fa-solid fa-right-from-bracket text-[11px]"></i>
                            @else
                                <i class="fa-solid fa-bed text-[11px]"></i>
                            @endif
                        </div>
                        @if(!$loop->last)
                            <div class="w-px h-full min-h-[40px] bg-gray-200 dark:bg-gray-700 my-2"></div>
                        @endif
                    </div>

                    {{-- Content --}}
                    <div class="flex-1">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                            <div>
                                <h3 class="text-[14px] font-medium text-gray-900 dark:text-gray-100">
                                    {{ $h->kamar->name ?? 'Unknown' }}
                                </h3>
                                <p class="text-[12px] text-gray-500 dark:text-gray-400 mt-0.5">
                                    @if($h->tanggal_keluar)
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-solid fa-arrow-right-to-bracket text-emerald-500 text-[10px]"></i>
                                            Masuk: {{ $h->tanggal_masuk?->format('d M Y') }}
                                        </span>
                                        <span class="mx-2">-</span>
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-solid fa-right-from-bracket text-red-500 text-[10px]"></i>
                                            Keluar: {{ $h->tanggal_keluar?->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400">
                                            <i class="fa-solid fa-bed text-[10px]"></i>
                                            Masuk: {{ $h->tanggal_masuk?->format('d M Y') }}
                                            <span class="ml-2 px-2 py-0.5 bg-emerald-50 dark:bg-emerald-500/10 rounded text-[10px] font-medium">Aktif</span>
                                        </span>
                                    @endif
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                @if(!$h->tanggal_keluar)
                                    @can('delete', $h)
                                        <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.penempatan.destroy', ['penempatan' => $h->id]) }}"
                                              onsubmit="return confirm('Checkout santri dari kamar ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1.5 text-[12px] font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg
                                                           hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                                                <i class="fa-solid fa-right-from-bracket mr-1"></i>
                                                Checkout
                                            </button>
                                        </form>
                                    @endcan
                                @else
                                    <span class="text-[12px] text-gray-400 dark:text-gray-500">
                                        <i class="fa-solid fa-check mr-1"></i>Selesai
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($h->keterangan)
                            <p class="mt-2 text-[12px] text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-800 rounded-lg p-2">
                                {{ $h->keterangan }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Actions --}}
<div class="mt-6 flex items-center justify-between">
    <a href="{{ tenant_route('dashboard.kepesantrenan.penempatan.index') }}"
       class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
              hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
        <i class="fa-solid fa-arrow-left mr-1.5"></i>
        Kembali
    </a>

    @if($penempatan->santri->kamar_id ?? false)
        @can('create', App\Models\MutasiKamar::class)
            <a href="{{ tenant_route('dashboard.kepesantrenan.mutasi.create', ['santri_id' => $penempatan->santri_id]) }}"
               class="px-4 py-2 text-[13px] font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                <i class="fa-solid fa-people-arrows mr-1.5"></i>
                Pindahkan Kamar
            </a>
        @endcan
    @else
        @can('create', App\Models\PenempatanKamar::class)
            <a href="{{ tenant_route('dashboard.kepesantrenan.penempatan.create', ['santri_id' => $penempatan->santri_id]) }}"
               class="px-4 py-2 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                <i class="fa-solid fa-user-plus mr-1.5"></i>
                Tempatkan di Kamar
            </a>
        @endcan
    @endif
</div>

@endsection
