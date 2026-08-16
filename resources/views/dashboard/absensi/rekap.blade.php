@extends('layouts.tenant')

@section('title', 'Rekap Absensi')
@section('page-title', 'Rekap Absensi')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.akademik.absensi.index', $programSlug) }}" class="hover:text-emerald-600">Absensi</a>
    <i class="fa-solid fa-chevron-right text-[8px] mx-2"></i>
    <span class="text-gray-600 dark:text-gray-300">Rekap</span>
@endsection

@section('content')

{{-- Back + action --}}
<div class="flex items-center justify-between mb-5">
    <a href="{{ tenant_route('dashboard.akademik.absensi.index', [$programSlug, 'tanggal' => $tanggal]) }}"
       class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
        <i class="fa-solid fa-arrow-left text-[10px]"></i>
        Kembali
    </a>
    <a href="{{ tenant_route('dashboard.akademik.absensi.input', [$programSlug, 'jadwal_id' => $jadwal->id, 'tanggal' => $tanggal]) }}"
       class="inline-flex items-center gap-1.5 text-[12px] font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 transition-colors">
        <i class="fa-solid fa-pen text-[10px]"></i>
        Edit Absensi
    </a>
</div>

{{-- Jadwal info --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-5 py-4 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Jadwal</p>
            <p class="font-bold text-[16px] text-gray-900 dark:text-gray-100">{{ $jadwal->mata_pelajaran }}</p>
            <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
                {{ $jadwal->kelas }} &middot; {{ $jadwal->hari }} &middot; {{ $jadwal->jam_mulai }}–{{ $jadwal->jam_selesai }}
                &middot; {{ $jadwal->ustadz?->user?->name ?? '—' }}
            </p>
        </div>
        <div class="text-right">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Tanggal</p>
            <p class="font-semibold text-gray-900 dark:text-gray-100">
                {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
            </p>
        </div>
    </div>
</div>

{{-- Summary badges --}}
@php
    $colorMap = \App\Models\Absensi::STATUS_COLORS;
    $labelMap = \App\Models\Absensi::STATUS_LABELS;
@endphp
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    @foreach(\App\Models\Absensi::STATUS as $s)
        @php $count = $summary[$s] ?? 0; $color = $colorMap[$s]; @endphp
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3.5 text-center">
            <p class="text-2xl font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ $count }}</p>
            <p class="text-[12px] font-medium text-gray-500 dark:text-gray-400 mt-0.5">{{ $labelMap[$s] }}</p>
        </div>
    @endforeach
</div>

{{-- Detail table --}}
@if($absensiList->isEmpty())
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
        <i class="fa-solid fa-clipboard text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
        <p class="text-[14px] font-medium text-gray-700 dark:text-gray-300">Belum ada data absensi</p>
        <a href="{{ tenant_route('dashboard.akademik.absensi.input', [$programSlug, 'jadwal_id' => $jadwal->id, 'tanggal' => $tanggal]) }}"
           class="inline-flex items-center gap-1.5 mt-4 text-[13px] text-emerald-600 hover:text-emerald-700 font-medium transition-colors">
            <i class="fa-solid fa-clipboard-list text-[11px]"></i>
            Isi Absensi Sekarang
        </a>
    </div>
@else
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Santri</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Status</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($absensiList as $ab)
                        @php $color = $colorMap[$ab->status] ?? 'gray'; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $ab->santri?->name ?? '—' }}</p>
                                @if($ab->santri?->nis)
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ $ab->santri->nis }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold
                                             bg-{{ $color }}-100 text-{{ $color }}-700
                                             dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-400">
                                    {{ $labelMap[$ab->status] ?? $ab->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                {{ $ab->catatan ?: '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection
