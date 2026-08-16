@extends('layouts.tenant')

@section('title', 'Rekap Absensi Ustadz')
@section('page-title', 'Rekap Absensi Ustadz')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.sdm.absensi-ustadz.index') }}" class="hover:text-emerald-600">Absensi Ustadz</a>
    <i class="fa-solid fa-chevron-right text-[8px] mx-2"></i>
    <span class="text-gray-600 dark:text-gray-300">Rekap</span>
@endsection

@section('content')

<div class="flex items-center justify-between mb-5">
    <a href="{{ tenant_route('dashboard.sdm.absensi-ustadz.index', ['tanggal' => $tanggal]) }}"
       class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
        <i class="fa-solid fa-arrow-left text-[10px]"></i>
        Kembali
    </a>
    <div class="flex items-center gap-3">
        <form method="GET" action="{{ tenant_route('dashboard.sdm.absensi-ustadz.rekap') }}" class="flex items-center gap-2">
            <input type="date" name="tanggal" value="{{ $tanggal }}"
                   class="border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-2.5 py-1 text-[12px] focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500">
            <x-btn type="submit" variant="outline" size="sm" icon="fa-search">Cari</x-btn>
        </form>
        <a href="{{ tenant_route('dashboard.sdm.absensi-ustadz.index', ['tanggal' => $tanggal]) }}"
           class="inline-flex items-center gap-1.5 text-[12px] font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 transition-colors">
            <i class="fa-solid fa-pen text-[10px]"></i>
            Edit
        </a>
    </div>
</div>

{{-- Date heading --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-5 py-4 mb-6">
    <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Tanggal</p>
    <p class="font-bold text-[15px] text-gray-900 dark:text-gray-100">
        {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
    </p>
</div>

{{-- Summary badges --}}
@php
    $colorMap = \App\Models\AbsensiUstadz::STATUS_COLORS;
    $labelMap = \App\Models\AbsensiUstadz::STATUS_LABELS;
@endphp
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    @foreach(\App\Models\AbsensiUstadz::STATUS as $s)
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
        <p class="text-[14px] font-medium text-gray-700 dark:text-gray-300">Belum ada data absensi ustadz</p>
        <a href="{{ tenant_route('dashboard.sdm.absensi-ustadz.index', ['tanggal' => $tanggal]) }}"
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
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ustadz</th>
                        <th class="px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-center">Status</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($absensiList as $ab)
                        @php $color = $colorMap[$ab->status] ?? 'gray'; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ $ab->ustadz?->user?->name ?? '—' }}</p>
                                @php
                                    $subjectNames = $ab->ustadz ? $ab->ustadz->subjects->pluck('name')->implode(', ') : null;
                                @endphp
                                @if($subjectNames)
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ $subjectNames }}</p>
                                @endif
                                @if($ab->schedule_id === null)
                                    <span class="inline-flex items-center gap-1 mt-1 text-[10px] font-medium px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                                        <i class="fa-solid fa-link-slash"></i>
                                        Historis belum dipetakan · hanya baca
                                    </span>
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
