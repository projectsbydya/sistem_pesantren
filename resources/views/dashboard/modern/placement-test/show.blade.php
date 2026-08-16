@extends('layouts.tenant')

@php
    $isEnglish = $type === 'english';
    $typeLabel = \App\Models\PlacementTest::getLabels()[$type] ?? 'Placement Test';
    $icon      = 'fa-clipboard-check';
    $accentBg  = $isEnglish ? 'bg-blue-50 dark:bg-blue-500/10' : 'bg-emerald-50 dark:bg-emerald-500/10';
    $accentTxt = $isEnglish ? 'text-blue-600 dark:text-blue-400' : 'text-emerald-600 dark:text-emerald-400';
@endphp

@section('title', $typeLabel . ' — ' . $santri->name)
@section('page-title', $typeLabel)
@section('breadcrumb')
    <a href="{{ tenant_route("dashboard.modern.{$featureSlug}.index", ['programSlug' => $programSlug, 'type' => $type]) }}"
       class="text-gray-600 dark:text-gray-300 hover:text-blue-600">{{ $typeLabel }}</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">{{ $santri->name }}</span>
@endsection

@section('content')

{{-- Header --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ tenant_route("dashboard.modern.{$featureSlug}.index", ['programSlug' => $programSlug, 'type' => $type]) }}"
       class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
        <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div class="w-10 h-10 rounded-full {{ $accentBg }} {{ $accentTxt }} flex items-center justify-center text-[14px] font-bold shrink-0">
        {{ strtoupper(substr($santri->name, 0, 1)) }}
    </div>
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $typeLabel }} — {{ $santri->name }}</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $program->name }}</p>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    <x-stat-card title="Total Test" value="{{ $records->count() }}" icon="{{ $icon }}" color="{{ $isEnglish ? 'blue' : 'emerald' }}" />
    <x-stat-card title="Rata-rata Nilai" value="{{ $records->whereNotNull('score')->avg('score') !== null ? number_format($records->whereNotNull('score')->avg('score'), 1) : '—' }}" icon="fa-star-half-stroke" color="amber" />
</div>

{{-- Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    @if($records->isEmpty())
        <div class="text-center py-16 px-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full {{ $accentBg }} flex items-center justify-center">
                <i class="fa-solid {{ $icon }} {{ $accentTxt }} text-2xl"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">
                Belum ada data {{ $typeLabel }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                Belum ada placement test yang direkam untuk {{ $santri->name }}.
            </p>
            <x-btn href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $type]) }}"
                   variant="primary" icon="fa-arrow-left" size="sm">
                Kembali ke Daftar
            </x-btn>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Test</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nilai</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Level</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Catatan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $rec)
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition-colors group">
                        <td class="px-4 py-3 max-w-[180px]">
                            <span class="text-[13px] font-semibold text-gray-900 dark:text-gray-100 truncate block" title="{{ $rec->test?->title }}">{{ $rec->test?->title ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-[13px] font-bold text-gray-700 dark:text-gray-300">{{ $rec->score !== null ? $rec->score : '—' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($rec->level)
                                <span class="text-[11px] font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">{{ $rec->level->label }}</span>
                            @else
                                <span class="text-gray-300 dark:text-gray-600 text-[12px]">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 max-w-[200px]">
                            <span class="text-[12px] text-gray-400 dark:text-gray-500 italic truncate block" title="{{ $rec->notes }}">
                                {{ $rec->notes ? Str::limit($rec->notes, 50) : '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-400 dark:text-gray-500">{{ $rec->test?->date ? $rec->test->date->format('d/m/Y') : $rec->created_at?->format('d/m/Y') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                {{-- Result actions managed separately via result-specific service methods --}}
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
