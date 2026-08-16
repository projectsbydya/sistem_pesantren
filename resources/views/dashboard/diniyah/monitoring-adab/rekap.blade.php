@extends('layouts.tenant')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Rekap Monitoring Adab</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $program->name }} — Periode: {{ $from }} s/d {{ $to }}</p>
        </div>
        <a href="{{ tenant_route('dashboard.diniyah.monitoring-adab.index', ['programSlug' => $programSlug]) }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm">
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
            <p class="text-sm text-emerald-600 dark:text-emerald-400">Sangat Baik</p>
            <p class="text-2xl font-bold text-emerald-700 dark:text-emerald-300">{{ $rekap['by_status']['sangat_baik'] ?? 0 }}</p>
        </div>
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-200 dark:border-blue-800 p-4">
            <p class="text-sm text-blue-600 dark:text-blue-400">Baik</p>
            <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $rekap['by_status']['baik'] ?? 0 }}</p>
        </div>
        <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800 p-4">
            <p class="text-sm text-amber-600 dark:text-amber-400">Cukup/Kurang</p>
            <p class="text-2xl font-bold text-amber-700 dark:text-amber-300">{{ ($rekap['by_status']['cukup'] ?? 0) + ($rekap['by_status']['kurang'] ?? 0) }}</p>
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

    @if(isset($rekap['average_score']))
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Rata-rata Skor</h3>
        <div class="flex items-center gap-4">
            <div class="text-4xl font-bold text-emerald-600 dark:text-emerald-400">{{ $rekap['average_score'] }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">dari skala 1-4</div>
        </div>
    </div>
    @endif
</div>
@endsection
