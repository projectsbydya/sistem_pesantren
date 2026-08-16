@extends('layouts.tenant')

@section('title', 'Hafalan & Nilai')

@section('content')
@php $tenant = \App\Services\TenantService::getTenant(); @endphp

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Hafalan & Nilai</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">Progress hafalan dan penilaian santri {{ $tenant?->name }}</p>
    </div>
    @if(Auth::user()->isAdmin())
        <div class="flex items-center gap-2">
            <x-btn href="#" variant="secondary" icon="fa-check-double">
                Validasi Massal
            </x-btn>
            <x-btn href="#" variant="primary" icon="fa-plus">
                Input Nilai
            </x-btn>
        </div>
    @endif
</div>

{{-- Summary Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    @php
    $summaryHafalan = [
        ['label' => 'Rata-rata Hafalan',    'value' => '8.4 Juz',  'sub' => 'Seluruh santri',    'icon' => 'fa-book-quran',       'color' => 'purple'],
        ['label' => 'Selesai Target Bulan', 'value' => '42',        'sub' => 'dari 128 santri',   'icon' => 'fa-trophy',           'color' => 'emerald'],
        ['label' => 'Perlu Perhatian',      'value' => '12',        'sub' => 'progress < 50%',    'icon' => 'fa-triangle-exclamation','color' => 'amber'],
    ];
    $hColors = [
        'purple'  => 'from-purple-500 to-violet-600',
        'emerald' => 'from-emerald-500 to-teal-600',
        'amber'   => 'from-amber-500 to-orange-600',
    ];
    @endphp
    @foreach($summaryHafalan as $s)
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $s['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-1.5">{{ $s['value'] }}</p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5">{{ $s['sub'] }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $hColors[$s['color']] }} flex items-center justify-center text-white shadow">
                    <i class="fa-solid {{ $s['icon'] }} text-sm"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Filter --}}
<x-card class="mb-5">
    <div class="flex flex-col md:flex-row gap-3">
        <div class="relative flex-1">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" placeholder="Cari nama santri..."
                   class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        </div>
        <div class="flex flex-wrap gap-2">
            <select class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <option>Semua Kelas</option>
                <option>Raudhah</option>
                <option>Ibtidaiyah</option>
                <option>Tsanawiyah</option>
                <option>Aliyah</option>
            </select>
            <select class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <option>Semua Progress</option>
                <option>≥ 75% (Baik)</option>
                <option>50–74% (Cukup)</option>
                <option>&lt; 50% (Perlu Perhatian)</option>
            </select>
        </div>
    </div>
</x-card>

{{-- Table --}}
<x-card title="Progres Hafalan Santri" :padding="false">
    <x-slot:actions>
        <x-btn variant="ghost" size="sm" icon="fa-download">Export</x-btn>
    </x-slot:actions>
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider w-8">#</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Santri</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Kelas</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider min-w-[180px]">Progress Hafalan</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Nilai Harian</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Nilai Ujian</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Ranking</th>
                    @if(Auth::user()->isAdmin())
                        <th class="text-right px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @php
                $hafalanData = [
                    ['name' => 'Fatimah Az-Zahra', 'kelas' => 'Aliyah 12A',     'juz' => 18, 'maxJuz' => 30, 'harian' => 95, 'ujian' => 92, 'rank' => 1],
                    ['name' => 'Muhammad Iqbal',   'kelas' => 'Aliyah 12A',     'juz' => 15, 'maxJuz' => 30, 'harian' => 88, 'ujian' => 85, 'rank' => 2],
                    ['name' => 'Aisyah Putri',     'kelas' => 'Tsanawiyah 9B',  'juz' => 14, 'maxJuz' => 15, 'harian' => 90, 'ujian' => 88, 'rank' => 1],
                    ['name' => 'Ahmad Fauzi',      'kelas' => 'Aliyah 11A',     'juz' => 10, 'maxJuz' => 30, 'harian' => 78, 'ujian' => 72, 'rank' => 5],
                    ['name' => 'Budi Santoso',     'kelas' => 'Tsanawiyah 10A', 'juz' => 6,  'maxJuz' => 15, 'harian' => 65, 'ujian' => 60, 'rank' => 8],
                    ['name' => 'Candra Wijaya',    'kelas' => 'Ibtidaiyah 7A',  'juz' => 2,  'maxJuz' => 5,  'harian' => 55, 'ujian' => 50, 'rank' => 12],
                ];
                @endphp
                @foreach($hafalanData as $idx => $row)
                    @php
                    $pct = round(($row['juz'] / $row['maxJuz']) * 100);
                    $barColor = $pct >= 75 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-blue-500' : 'bg-amber-400');
                    $rankBg   = $row['rank'] === 1 ? 'bg-amber-400 text-white' : ($row['rank'] <= 3 ? 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' : 'text-gray-500 dark:text-gray-400');
                    $nilaiColor = fn($v) => $v >= 85 ? 'text-emerald-600 dark:text-emerald-400' : ($v >= 70 ? 'text-blue-600 dark:text-blue-400' : 'text-amber-600 dark:text-amber-400');
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-4 py-3 text-gray-400 dark:text-gray-600 font-mono text-[11px]">{{ $idx + 1 }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400 flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($row['name'], 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $row['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $row['kelas'] }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden min-w-[80px]">
                                    <div class="{{ $barColor }} h-full rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-[11px] font-semibold text-gray-700 dark:text-gray-300 shrink-0 w-16">{{ $row['juz'] }}/{{ $row['maxJuz'] }} juz</span>
                            </div>
                            <span class="text-[10px] {{ $pct >= 75 ? 'text-emerald-600 dark:text-emerald-400' : ($pct >= 50 ? 'text-blue-500' : 'text-amber-600') }} font-medium">{{ $pct }}%</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold {{ $nilaiColor($row['harian']) }}">{{ $row['harian'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="font-bold {{ $nilaiColor($row['ujian']) }}">{{ $row['ujian'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold {{ $rankBg }}">
                                {{ $row['rank'] }}
                            </span>
                        </td>
                        @if(Auth::user()->isAdmin())
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <x-btn variant="ghost" size="xs" icon="fa-pen">Input</x-btn>
                                    <x-btn variant="ghost" size="xs" icon="fa-eye"></x-btn>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <x-slot:footer>
        <div class="flex items-center justify-between text-[12px] text-gray-500 dark:text-gray-400">
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-emerald-500"></span> ≥75% Baik</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-blue-500"></span> 50–74% Cukup</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded bg-amber-400"></span> &lt;50% Perhatian</div>
            </div>
            <span>{{ count($hafalanData) }} dari 128 santri</span>
        </div>
    </x-slot:footer>
</x-card>

@endsection
