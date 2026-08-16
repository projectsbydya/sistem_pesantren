@extends('layouts.tenant')

@section('title', 'Absensi')
@section('page-title', 'Absensi')
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Absensi</span>
@endsection

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Absensi Santri</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            Pilih jadwal untuk mengisi absensi — {{ \App\Services\TenantService::getTenant()?->name }}
        </p>
    </div>
</div>

@if(session('success'))
    <x-alert type="success" class="mb-5">{{ session('success') }}</x-alert>
@endif

{{-- Date picker --}}
<form method="GET" action="{{ tenant_route('dashboard.akademik.absensi.index', $programSlug) }}" class="mb-6">
    <div class="flex items-center gap-3 flex-wrap">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal:</label>
        <input type="date" name="tanggal" value="{{ $tanggal }}"
               class="border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
        <x-btn type="submit" variant="outline" size="sm" icon="fa-search">Cari Jadwal</x-btn>
    </div>
</form>

{{-- Jadwal list --}}
@if($jadwalList->isEmpty())
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
        <i class="fa-solid fa-calendar-xmark text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
        <p class="text-[14px] font-medium text-gray-700 dark:text-gray-300">
            Tidak ada jadwal untuk hari
            {{ $hariIndo ?? \Carbon\Carbon::parse($tanggal)->translatedFormat('l') }}
            ({{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }})
        </p>
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($jadwalList as $jadwal)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-gray-100 text-[14px]">{{ $jadwal->mata_pelajaran }}</p>
                        <p class="text-[12px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $jadwal->kelas }}</p>
                    </div>
                    <span class="shrink-0 text-[11px] font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 px-2 py-1 rounded-lg">
                        {{ $jadwal->jam_mulai }} – {{ $jadwal->jam_selesai }}
                    </span>
                </div>
                <p class="text-[12px] text-gray-500 dark:text-gray-400 mb-4">
                    <i class="fa-solid fa-chalkboard-user mr-1"></i>
                    {{ $jadwal->ustadz?->user?->name ?? '—' }}
                </p>
                <a href="{{ tenant_route('dashboard.akademik.absensi.input', [$programSlug, 'jadwal_id' => $jadwal->id, 'tanggal' => $tanggal]) }}"
                   class="inline-flex items-center gap-1.5 text-[12px] font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors">
                    <i class="fa-solid fa-clipboard-list text-[11px]"></i>
                    Isi Absensi
                </a>
                <span class="mx-2 text-gray-300 dark:text-gray-600">·</span>
                <a href="{{ tenant_route('dashboard.akademik.absensi.rekap', [$programSlug, 'jadwal_id' => $jadwal->id, 'tanggal' => $tanggal]) }}"
                   class="inline-flex items-center gap-1.5 text-[12px] font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 transition-colors">
                    <i class="fa-solid fa-chart-bar text-[11px]"></i>
                    Rekap
                </a>
            </div>
        @endforeach
    </div>
@endif

@endsection
