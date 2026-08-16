@extends('layouts.tenant')

@section('title', 'Riwayat Mutasi Kamar')
@section('page-title', 'Riwayat Mutasi Kamar')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.mutasi.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Mutasi Kamar</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Riwayat Santri</span>
@endsection

@section('content')

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
        </div>
    </div>
</div>

{{-- Riwayat Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800">
        <h2 class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">Riwayat Mutasi Kamar</h2>
    </div>

    @if($mutasi->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-16 px-6">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-people-arrows text-gray-400 dark:text-gray-500 text-xl"></i>
            </div>
            <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada mutasi</h3>
            <p class="text-[13px] text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                Santri ini belum memiliki riwayat perpindahan kamar.
            </p>
        </div>
    @else
        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dari Kamar</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"></th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ke Kamar</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alasan</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Diproses Oleh</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mutasi as $m)
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group">
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">
                                {{ $m->tanggal_mutasi?->format('d M Y') ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-bed text-gray-400 dark:text-gray-500 text-xs"></i>
                                <span class="text-[13px] text-gray-700 dark:text-gray-300">{{ $m->kamarAsal->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <i class="fa-solid fa-arrow-right text-emerald-500 dark:text-emerald-400 text-sm"></i>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-bed text-emerald-500 dark:text-emerald-400 text-xs"></i>
                                <span class="text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $m->kamarTujuan->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                                {{ $m->alasan }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">{{ $m->processedBy->name ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ tenant_route('dashboard.kepesantrenan.mutasi.show', ['mutasi' => $m->id]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                   title="Lihat Detail">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                </a>
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
