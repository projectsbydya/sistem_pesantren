@extends('layouts.tenant')

@section('title', 'Absensi')

@section('content')
@php $tenant = \App\Services\TenantService::getTenant(); @endphp

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Absensi Santri</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">Kelola kehadiran santri {{ $tenant?->name }}</p>
    </div>
    @if(Auth::user()->isAdmin())
        <div class="flex items-center gap-2">
            <x-btn href="#" variant="secondary" icon="fa-qrcode">
                Scan QR
            </x-btn>
            <x-btn href="#" variant="primary" icon="fa-plus">
                Input Absensi
            </x-btn>
        </div>
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @php
    $stats = [
        ['label' => 'Hadir',  'value' => $hadirCount  ?? 118, 'total' => 128, 'icon' => 'fa-circle-check',     'color' => 'emerald'],
        ['label' => 'Izin',   'value' => $izinCount   ?? 4,   'total' => 128, 'icon' => 'fa-file-circle-check', 'color' => 'blue'],
        ['label' => 'Sakit',  'value' => $sakitCount  ?? 3,   'total' => 128, 'icon' => 'fa-kit-medical',       'color' => 'amber'],
        ['label' => 'Alfa',   'value' => $alfaCount   ?? 3,   'total' => 128, 'icon' => 'fa-circle-xmark',      'color' => 'rose'],
    ];
    $colorMap = [
        'emerald' => ['stat' => 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20', 'icon' => 'text-emerald-600 dark:text-emerald-400', 'bar' => 'bg-emerald-500'],
        'blue'    => ['stat' => 'bg-blue-50 dark:bg-blue-500/10 border-blue-200 dark:border-blue-500/20',             'icon' => 'text-blue-600 dark:text-blue-400',       'bar' => 'bg-blue-500'],
        'amber'   => ['stat' => 'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/20',         'icon' => 'text-amber-600 dark:text-amber-400',     'bar' => 'bg-amber-400'],
        'rose'    => ['stat' => 'bg-red-50 dark:bg-red-500/10 border-red-200 dark:border-red-500/20',                 'icon' => 'text-red-600 dark:text-red-400',         'bar' => 'bg-red-500'],
    ];
    @endphp
    @foreach($stats as $s)
        @php $c = $colorMap[$s['color']]; $pct = round(($s['value']/$s['total'])*100); @endphp
        <div class="rounded-xl border p-4 {{ $c['stat'] }} transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $s['label'] }}</span>
                <i class="fa-solid {{ $s['icon'] }} {{ $c['icon'] }}"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $s['value'] }}</p>
            <div class="h-1.5 bg-white/60 dark:bg-black/20 rounded-full mt-2 overflow-hidden">
                <div class="{{ $c['bar'] }} h-full rounded-full" style="width: {{ $pct }}%"></div>
            </div>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">{{ $pct }}% dari {{ $s['total'] }}</p>
        </div>
    @endforeach
</div>

{{-- Filter --}}
<x-card class="mb-5" :padding="true">
    <div class="flex flex-col md:flex-row gap-3">
        <div class="relative flex-1">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" placeholder="Cari nama santri..."
                   class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        </div>
        <div class="flex flex-wrap gap-2">
            <input type="date" value="{{ request('tanggal', now()->format('Y-m-d')) }}"
                   class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
            <select class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <option>Semua Kelas</option>
                <option>Raudhah</option>
                <option>Ibtidaiyah</option>
                <option>Tsanawiyah</option>
                <option>Aliyah</option>
            </select>
            <select class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <option>Semua Status</option>
                <option>Hadir</option>
                <option>Izin</option>
                <option>Sakit</option>
                <option>Alfa</option>
            </select>
        </div>
    </div>
</x-card>

{{-- Table --}}
<x-card title="Daftar Absensi" subtitle="{{ now()->isoFormat('dddd, D MMMM Y') }}" :padding="false">
    <x-slot:actions>
        <x-btn variant="ghost" size="sm" icon="fa-download">Export</x-btn>
    </x-slot:actions>
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Santri</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Kelas</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Sesi</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Status</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Keterangan</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Jam Masuk</th>
                    @if(Auth::user()->isAdmin())
                        <th class="text-right px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @php
                $absensiData = [
                    ['name' => 'Ahmad Fauzi',    'kelas' => 'Aliyah 12A',     'sesi' => 'Pagi',  'status' => 'hadir',  'ket' => '-',              'jam' => '07:02'],
                    ['name' => 'Budi Santoso',   'kelas' => 'Tsanawiyah 9B',  'sesi' => 'Pagi',  'status' => 'izin',   'ket' => 'Keperluan keluarga', 'jam' => '-'],
                    ['name' => 'Candra Wijaya',  'kelas' => 'Aliyah 11A',     'sesi' => 'Pagi',  'status' => 'alfa',   'ket' => '-',              'jam' => '-'],
                    ['name' => 'Dedi Kurniawan', 'kelas' => 'Tsanawiyah 10A', 'sesi' => 'Pagi',  'status' => 'hadir',  'ket' => '-',              'jam' => '06:58'],
                    ['name' => 'Eko Prasetyo',   'kelas' => 'Ibtidaiyah 7A',  'sesi' => 'Pagi',  'status' => 'sakit',  'ket' => 'Demam',          'jam' => '-'],
                    ['name' => 'Fajar Nugroho',  'kelas' => 'Aliyah 12B',     'sesi' => 'Pagi',  'status' => 'hadir',  'ket' => '-',              'jam' => '07:10'],
                    ['name' => 'Hendra Wijaya',  'kelas' => 'Tsanawiyah 9A',  'sesi' => 'Pagi',  'status' => 'hadir',  'ket' => '-',              'jam' => '07:05'],
                ];
                $statusMap = [
                    'hadir' => ['label' => 'Hadir', 'variant' => 'success'],
                    'izin'  => ['label' => 'Izin',  'variant' => 'info'],
                    'sakit' => ['label' => 'Sakit', 'variant' => 'warning'],
                    'alfa'  => ['label' => 'Alfa',  'variant' => 'danger'],
                ];
                @endphp
                @foreach($absensiData as $row)
                    @php $s = $statusMap[$row['status']]; @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($row['name'], 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $row['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $row['kelas'] }}</td>
                        <td class="px-4 py-3">
                            <x-badge variant="outline" size="sm">{{ $row['sesi'] }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <x-badge variant="{{ $s['variant'] }}" size="sm" dot>{{ $s['label'] }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $row['ket'] !== '-' ? $row['ket'] : '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="font-mono text-[12px] {{ $row['jam'] !== '-' ? 'text-gray-700 dark:text-gray-300' : 'text-gray-400' }}">{{ $row['jam'] }}</span>
                        </td>
                        @if(Auth::user()->isAdmin())
                            <td class="px-4 py-3 text-right">
                                <x-btn variant="ghost" size="xs" icon="fa-pen">Edit</x-btn>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <x-slot:footer>
        <div class="flex items-center justify-between text-[12px] text-gray-500 dark:text-gray-400">
            <span>Menampilkan {{ count($absensiData) }} dari {{ $santriTotal ?? 128 }} santri</span>
            <div class="flex items-center gap-1">
                <button class="px-2 py-1 rounded border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">‹</button>
                <span class="px-3 py-1 rounded bg-emerald-600 text-white font-medium">1</span>
                <button class="px-2 py-1 rounded border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">2</button>
                <button class="px-2 py-1 rounded border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">›</button>
            </div>
        </div>
    </x-slot:footer>
</x-card>

@endsection
