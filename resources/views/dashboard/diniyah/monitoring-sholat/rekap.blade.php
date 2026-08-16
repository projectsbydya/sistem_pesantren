@extends('layouts.tenant')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Rekap Monitoring Sholat</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $program->name }} — Periode: {{ $from }} s/d {{ $to }}</p>
        </div>
        <a href="{{ tenant_route('dashboard.diniyah.monitoring-sholat.index', ['programSlug' => $programSlug]) }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm">
            <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Records</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $rekap['total'] }}</p>
        </div>
        <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-200 dark:border-emerald-800 p-4">
            <p class="text-sm text-emerald-600 dark:text-emerald-400">Hadir</p>
            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $rekap['by_status']['hadir'] ?? 0 }}</p>
        </div>
        <div class="bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-800 p-4">
            <p class="text-sm text-red-600 dark:text-red-400">Tidak Hadir</p>
            <p class="text-2xl font-bold text-red-700 dark:text-red-300">{{ $rekap['by_status']['tidak_hadir'] ?? 0 }}</p>
        </div>
        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800 p-4">
            <p class="text-sm text-amber-600 dark:text-amber-400">Terlambat</p>
            <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ $rekap['by_status']['terlambat'] ?? 0 }}</p>
        </div>
    </div>

    {{-- Filter --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}" class="px-3 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}" class="px-3 py-2 border rounded-lg dark:bg-gray-800 dark:border-gray-700">
            </div>
            <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm">
                <i class="fa-solid fa-filter mr-1"></i> Filter
            </button>
        </form>
    </div>

    {{-- By Waktu Sholat --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Berdasarkan Waktu Sholat</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @foreach(['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $waktu)
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400 capitalize">{{ $waktu }}</p>
                <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $rekap['by_aspect'][$waktu] ?? 0 }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
