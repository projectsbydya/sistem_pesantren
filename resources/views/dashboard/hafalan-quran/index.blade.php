@extends('layouts.tenant')

@section('title', 'Hafalan Quran')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Hafalan Quran</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Rekaman hafalan Al-Quran santri</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 p-4 text-sm text-emerald-700 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- Quick Add Form --}}
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Tambah Record</h3>
        <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.hafalan-quran.store') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Santri</label>
                <select name="santri_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Pilih...</option>
                    @foreach($santriList as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Surah</label>
                <input type="text" name="surah" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm" placeholder="Al-Baqarah">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Ayat</label>
                <div class="flex gap-1">
                    <input type="text" name="ayat_dari" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm" placeholder="Dari">
                    <input type="text" name="ayat_sampai" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm" placeholder="Sampai">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal</label>
                <input type="date" name="tanggal" value="{{ today()->toDateString() }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select name="status" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    @foreach(\App\Models\HafalanQuran::STATUS as $st)
                        <option value="{{ $st }}">{{ \App\Models\HafalanQuran::STATUS_LABELS[$st] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">
                    <i class="fa-solid fa-plus mr-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>

    {{-- Records --}}
    @if($records->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-book-quran text-gray-400 dark:text-gray-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum ada data hafalan</h3>
        </div>
    @else
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Santri</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Surah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Ayat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Nilai</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Ustadz</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($records as $rec)
                        @php $color = \App\Models\HafalanQuran::STATUS_COLORS[$rec->status] ?? 'gray'; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $rec->tanggal->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $rec->santri?->name }}</td>
                            <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $rec->surah }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $rec->ayat_dari }}{{ $rec->ayat_sampai ? ' - ' . $rec->ayat_sampai : '' }}</td>
                            <td class="px-4 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $rec->nilai !== null ? number_format($rec->nilai, 1) : '-' }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $color }}-100 dark:bg-{{ $color }}-500/20 text-{{ $color }}-700 dark:text-{{ $color }}-400">
                                    {{ \App\Models\HafalanQuran::STATUS_LABELS[$rec->status] }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $rec->ustadz?->user?->name ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
