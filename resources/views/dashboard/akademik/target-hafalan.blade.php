@extends('layouts.tenant')

@section('title', 'Target Hafalan')

@section('content')
@php $tenant = \App\Services\TenantService::getTenant(); @endphp

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Target Hafalan</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">Kelola target dan progres hafalan per santri — {{ $tenant?->name }}</p>
    </div>
    @if(Auth::user()->isAdmin())
        <div class="flex items-center gap-2">
            <x-btn href="#" variant="secondary" icon="fa-sliders">Set Massal</x-btn>
            <x-btn href="#" variant="primary" icon="fa-plus">Set Target</x-btn>
        </div>
    @endif
</div>

{{-- Summary --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @php
    $targetStats = [
        ['label' => 'Target Aktif',     'value' => 128, 'icon' => 'fa-bullseye',          'color' => 'emerald'],
        ['label' => 'Tercapai Bulan Ini','value' => 42,  'icon' => 'fa-trophy',            'color' => 'blue'],
        ['label' => 'On Track',         'value' => 74,  'icon' => 'fa-arrow-trend-up',    'color' => 'purple'],
        ['label' => 'Perlu Reminder',   'value' => 12,  'icon' => 'fa-bell',              'color' => 'amber'],
    ];
    $tColors = [
        'emerald' => 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-400',
        'blue'    => 'bg-blue-50 dark:bg-blue-500/10 border-blue-200 dark:border-blue-500/20 text-blue-600 dark:text-blue-400',
        'purple'  => 'bg-purple-50 dark:bg-purple-500/10 border-purple-200 dark:border-purple-500/20 text-purple-600 dark:text-purple-400',
        'amber'   => 'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/20 text-amber-600 dark:text-amber-400',
    ];
    @endphp
    @foreach($targetStats as $s)
        <div class="rounded-xl border p-4 {{ $tColors[$s['color']] }} transition-colors">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-semibold uppercase tracking-wider opacity-70">{{ $s['label'] }}</span>
                <i class="fa-solid {{ $s['icon'] }}"></i>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $s['value'] }}</p>
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
                <option>Semua Status</option>
                <option>Tercapai</option>
                <option>On Track</option>
                <option>Perlu Perhatian</option>
            </select>
            <select class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <option>{{ now()->format('F Y') }}</option>
                <option>{{ now()->subMonth()->format('F Y') }}</option>
            </select>
        </div>
    </div>
</x-card>

{{-- Target Table --}}
<x-card title="Daftar Target Hafalan" subtitle="{{ now()->format('F Y') }}" :padding="false">
    <x-slot:actions>
        <x-btn variant="ghost" size="sm" icon="fa-download">Export</x-btn>
    </x-slot:actions>
    <div class="overflow-x-auto">
        <table class="w-full text-[13px]">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Santri</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Kelas</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Target</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider min-w-[160px]">Progress</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Deadline</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Status</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Reminder</th>
                    @if(Auth::user()->isAdmin())
                        <th class="text-right px-4 py-3 font-semibold text-gray-600 dark:text-gray-400 uppercase text-[11px] tracking-wider">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @php
                $targetData = [
                    ['name' => 'Fatimah Az-Zahra', 'kelas' => 'Aliyah 12A',     'target' => '20 Juz', 'current' => 18, 'max' => 20, 'deadline' => '30 Apr', 'status' => 'on_track',   'reminder' => false],
                    ['name' => 'Muhammad Iqbal',   'kelas' => 'Aliyah 12A',     'target' => '18 Juz', 'current' => 15, 'max' => 18, 'deadline' => '30 Apr', 'status' => 'on_track',   'reminder' => false],
                    ['name' => 'Aisyah Putri',     'kelas' => 'Tsanawiyah 9B',  'target' => '15 Juz', 'current' => 14, 'max' => 15, 'deadline' => '30 Apr', 'status' => 'tercapai',   'reminder' => false],
                    ['name' => 'Ahmad Fauzi',      'kelas' => 'Aliyah 11A',     'target' => '15 Juz', 'current' => 10, 'max' => 15, 'deadline' => '15 Apr', 'status' => 'terlambat',  'reminder' => true],
                    ['name' => 'Budi Santoso',     'kelas' => 'Tsanawiyah 10A', 'target' => '8 Juz',  'current' => 6,  'max' => 8,  'deadline' => '30 Apr', 'status' => 'on_track',   'reminder' => false],
                    ['name' => 'Candra Wijaya',    'kelas' => 'Ibtidaiyah 7A',  'target' => '3 Juz',  'current' => 2,  'max' => 3,  'deadline' => '30 Apr', 'status' => 'perhatian',  'reminder' => true],
                    ['name' => 'Dedi Kurniawan',   'kelas' => 'Tsanawiyah 10B', 'target' => '10 Juz', 'current' => 9,  'max' => 10, 'deadline' => '30 Apr', 'status' => 'on_track',   'reminder' => false],
                ];
                $statusMap = [
                    'tercapai'  => ['label' => 'Tercapai',    'variant' => 'success'],
                    'on_track'  => ['label' => 'On Track',    'variant' => 'info'],
                    'perhatian' => ['label' => 'Perhatian',   'variant' => 'warning'],
                    'terlambat' => ['label' => 'Terlambat',   'variant' => 'danger'],
                ];
                @endphp
                @foreach($targetData as $row)
                    @php
                    $pct = round(($row['current'] / $row['max']) * 100);
                    $barColor = match($row['status']) {
                        'tercapai'  => 'bg-emerald-500',
                        'on_track'  => 'bg-blue-500',
                        'perhatian' => 'bg-amber-400',
                        'terlambat' => 'bg-red-500',
                        default     => 'bg-gray-400',
                    };
                    $sm = $statusMap[$row['status']];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($row['name'], 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $row['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $row['kelas'] }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-900 dark:text-gray-100">{{ $row['target'] }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden min-w-[80px]">
                                    <div class="{{ $barColor }} h-full rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-[11px] font-semibold text-gray-600 dark:text-gray-400 shrink-0">{{ $row['current'] }}/{{ $row['max'] }}</span>
                            </div>
                            <span class="text-[10px] font-medium {{ $barColor === 'bg-emerald-500' ? 'text-emerald-600 dark:text-emerald-400' : ($barColor === 'bg-blue-500' ? 'text-blue-500' : ($barColor === 'bg-amber-400' ? 'text-amber-600' : 'text-red-600')) }}">{{ $pct }}%</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">{{ $row['deadline'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <x-badge variant="{{ $sm['variant'] }}" size="sm" dot>{{ $sm['label'] }}</x-badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($row['reminder'])
                                <span class="inline-flex items-center gap-1 text-[11px] text-amber-600 dark:text-amber-400 font-medium">
                                    <i class="fa-solid fa-bell text-xs animate-bounce"></i> Aktif
                                </span>
                            @else
                                <span class="text-[11px] text-gray-400">—</span>
                            @endif
                        </td>
                        @if(Auth::user()->isAdmin())
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <x-btn variant="ghost" size="xs" icon="fa-pen">Edit</x-btn>
                                    <x-btn variant="ghost" size="xs" icon="fa-bell" class="text-amber-600">
                                    </x-btn>
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
            <span>{{ count($targetData) }} dari 128 santri ditampilkan</span>
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
