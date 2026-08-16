@extends('layouts.tenant')

@section('title', 'Progress ' . $santri->name)
@section('page-title', 'Progress Hafalan & Nilai')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.hafalan-nilai.index') }}" class="hover:text-emerald-600">Hafalan & Nilai</a>
    <i class="fa-solid fa-chevron-right text-[8px] mx-2"></i>
    <span class="text-gray-600 dark:text-gray-300">{{ $santri->name }}</span>
@endsection

@section('content')

<div class="mb-5 flex items-center justify-between flex-wrap gap-3">
    <a href="{{ tenant_route('dashboard.hafalan-nilai.index') }}"
       class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors">
        <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali
    </a>
</div>

{{-- Santri info --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-5 py-4 mb-6">
    <div class="flex items-center gap-4">
        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center shrink-0">
            <i class="fa-solid fa-user text-emerald-600 dark:text-emerald-400 text-[14px]"></i>
        </div>
        <div>
            <p class="font-bold text-[15px] text-gray-900 dark:text-gray-100">{{ $santri->name }}</p>
            <p class="text-[12px] text-gray-500 dark:text-gray-400">
                {{ $santri->kelas?->name ?? '—' }}
                @if($santri->nis) · NIS {{ $santri->nis }} @endif
            </p>
        </div>
        <div class="ml-auto text-right">
            <p class="text-[11px] text-gray-400 dark:text-gray-500">Total Record</p>
            <p class="font-bold text-[18px] text-gray-900 dark:text-gray-100">{{ $records->count() }}</p>
        </div>
    </div>
</div>

@if($records->isEmpty())
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
        <i class="fa-solid fa-book-open text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
        <p class="text-[14px] font-medium text-gray-700 dark:text-gray-300">Belum ada data hafalan/nilai</p>
    </div>
@else
    {{-- Per-subject breakdown --}}
    @foreach($bySubject as $subjectId => $subjectRecords)
        @php
            $subjectName = $subjectRecords->first()->subject?->name ?? '—';
            $avg         = $subjectRecords->whereNotNull('nilai')->avg('nilai');
            $hafalanCount = $subjectRecords->where('jenis', 'hafalan')->count();
            $nilaiCount   = $subjectRecords->where('jenis', 'nilai')->count();
        @endphp
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-4">
            <div class="px-5 py-3.5 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-wrap gap-3">
                <p class="font-semibold text-[14px] text-gray-900 dark:text-gray-100">{{ $subjectName }}</p>
                <div class="flex items-center gap-3 text-[12px]">
                    <span class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-emerald-600">{{ $hafalanCount }}</span> hafalan</span>
                    <span class="text-gray-500 dark:text-gray-400"><span class="font-semibold text-blue-600">{{ $nilaiCount }}</span> nilai</span>
                    @if($avg !== null)
                        <span class="font-bold text-gray-900 dark:text-gray-100">Rata-rata: {{ number_format($avg, 1) }}</span>
                    @endif
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Materi</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nilai</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ustadz</th>
                            <th class="text-left px-4 py-2.5 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($subjectRecords->sortByDesc('tanggal') as $r)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($r->tanggal)->format('d M Y') }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                        {{ $r->jenis === 'hafalan' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' }}">
                                        {{ \App\Models\HafalanNilai::JENIS_LABELS[$r->jenis] ?? $r->jenis }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">{{ $r->materi ?: '—' }}</td>
                                <td class="px-4 py-2.5">
                                    @if($r->nilai !== null)
                                        @php
                                            $n = (float) $r->nilai;
                                            $color = $n >= 80 ? 'emerald' : ($n >= 60 ? 'amber' : 'red');
                                        @endphp
                                        <span class="font-semibold text-{{ $color }}-600 dark:text-{{ $color }}-400">
                                            {{ number_format($n, 1) }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">{{ $r->ustadz?->user?->name ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-gray-500 dark:text-gray-400">{{ $r->catatan ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
@endif

@endsection
