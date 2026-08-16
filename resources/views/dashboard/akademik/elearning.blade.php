@extends('layouts.tenant')

@section('title', 'E-Learning')

@section('content')
@php $tenant = \App\Services\TenantService::getTenant(); @endphp

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">E-Learning</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">Materi digital untuk santri {{ $tenant?->name }}</p>
    </div>
    @if(Auth::user()->isAdmin())
        <x-btn href="#" variant="primary" icon="fa-plus">
            Tambah Materi
        </x-btn>
    @endif
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @php
    $eStats = [
        ['label' => 'Total Materi',  'value' => $totalMateri ?? 48,  'icon' => 'fa-photo-film',   'color' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400'],
        ['label' => 'Video',         'value' => $totalVideo  ?? 24,  'icon' => 'fa-circle-play',  'color' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400'],
        ['label' => 'Dokumen',       'value' => $totalDok    ?? 18,  'icon' => 'fa-file-pdf',     'color' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400'],
        ['label' => 'Kuis',          'value' => $totalKuis   ?? 6,   'icon' => 'fa-clipboard-question','color' => 'bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400'],
    ];
    @endphp
    @foreach($eStats as $s)
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl p-4 shadow-sm flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg {{ $s['color'] }} flex items-center justify-center shrink-0">
                <i class="fa-solid {{ $s['icon'] }}"></i>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">{{ $s['label'] }}</p>
                <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $s['value'] }}</p>
            </div>
        </div>
    @endforeach
</div>

{{-- Category Tabs + Search --}}
<x-card class="mb-5">
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="flex gap-1 flex-wrap">
            @php $cats = ['Semua', 'Fiqih', 'Bahasa Arab', 'Tahfidz', 'Aqidah', 'Hadits']; $active = 'Semua'; @endphp
            @foreach($cats as $cat)
                <button class="px-3 py-1.5 text-[12px] font-medium rounded-lg transition-colors
                    {{ $cat === $active ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                    {{ $cat }}
                </button>
            @endforeach
        </div>
        <div class="relative sm:ml-auto sm:w-64">
            <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" placeholder="Cari materi..."
                   class="w-full pl-9 pr-4 py-1.5 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        </div>
    </div>
</x-card>

{{-- Materi Grid --}}
@php
$materis = [
    ['title' => 'Pengantar Ilmu Fiqih',         'kategori' => 'Fiqih',      'type' => 'video', 'duration' => '42 menit',  'thumb' => 'fa-circle-play',   'author' => 'Ust. Abdullah',  'views' => 128, 'date' => '20 Apr'],
    ['title' => 'Kaidah Bahasa Arab Dasar',      'kategori' => 'Bahasa Arab','type' => 'video', 'duration' => '35 menit',  'thumb' => 'fa-circle-play',   'author' => 'Ustz. Fatimah',  'views' => 95,  'date' => '18 Apr'],
    ['title' => 'Panduan Tahfidz Juz 30',        'kategori' => 'Tahfidz',    'type' => 'pdf',   'duration' => '24 halaman','thumb' => 'fa-file-pdf',       'author' => 'Ust. Ahmad',     'views' => 210, 'date' => '15 Apr'],
    ['title' => 'Rukun Iman dan Dalilnya',        'kategori' => 'Aqidah',     'type' => 'video', 'duration' => '28 menit',  'thumb' => 'fa-circle-play',   'author' => 'Ust. Muhammad',  'views' => 76,  'date' => '12 Apr'],
    ['title' => 'Hadits Arbain — Syarah Lengkap','kategori' => 'Hadits',     'type' => 'pdf',   'duration' => '56 halaman','thumb' => 'fa-file-pdf',       'author' => 'Ust. Hasan',     'views' => 142, 'date' => '10 Apr'],
    ['title' => 'Kuis Fiqih Ibadah Semester 1',  'kategori' => 'Fiqih',      'type' => 'quiz',  'duration' => '20 soal',   'thumb' => 'fa-clipboard-question','author' => 'Ust. Rofi',  'views' => 88,  'date' => '8 Apr'],
];
$typeConfig = [
    'video' => ['badge' => 'danger',  'icon' => 'fa-circle-play',        'bg' => 'from-rose-500 to-pink-600'],
    'pdf'   => ['badge' => 'warning', 'icon' => 'fa-file-pdf',           'bg' => 'from-amber-500 to-orange-600'],
    'quiz'  => ['badge' => 'purple',  'icon' => 'fa-clipboard-question', 'bg' => 'from-purple-500 to-violet-600'],
];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    @foreach($materis as $m)
        @php $tc = $typeConfig[$m['type']]; @endphp
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden hover:shadow-md transition-shadow group">
            {{-- Thumbnail --}}
            <div class="h-36 bg-gradient-to-br {{ $tc['bg'] }} flex items-center justify-center relative">
                <i class="fa-solid {{ $tc['icon'] }} text-4xl text-white/80"></i>
                <div class="absolute top-3 left-3">
                    <x-badge variant="{{ $tc['badge'] }}" size="sm">{{ strtoupper($m['type']) }}</x-badge>
                </div>
                <div class="absolute bottom-3 right-3 bg-black/40 text-white text-[11px] font-medium px-2 py-0.5 rounded">
                    {{ $m['duration'] }}
                </div>
            </div>
            {{-- Content --}}
            <div class="p-4">
                <div class="mb-2">
                    <x-badge variant="info" size="xs">{{ $m['kategori'] }}</x-badge>
                </div>
                <h3 class="font-semibold text-[14px] text-gray-900 dark:text-gray-100 leading-snug group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                    {{ $m['title'] }}
                </h3>
                <div class="flex items-center gap-3 mt-3 text-[11px] text-gray-400">
                    <span class="flex items-center gap-1"><i class="fa-solid fa-chalkboard-user"></i> {{ $m['author'] }}</span>
                    <span class="flex items-center gap-1"><i class="fa-solid fa-eye"></i> {{ $m['views'] }}</span>
                    <span class="ml-auto">{{ $m['date'] }}</span>
                </div>
            </div>
            {{-- Footer --}}
            <div class="px-4 pb-4">
                <a href="#" class="block w-full text-center py-2 text-[12px] font-medium rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-emerald-600 hover:text-white dark:hover:bg-emerald-600 transition-colors">
                    {{ $m['type'] === 'quiz' ? 'Mulai Kuis' : 'Buka Materi' }}
                </a>
            </div>
        </div>
    @endforeach
</div>

{{-- Load More --}}
<div class="mt-6 text-center">
    <x-btn variant="outline" icon="fa-rotate-right">Muat Lebih Banyak</x-btn>
</div>

@endsection
