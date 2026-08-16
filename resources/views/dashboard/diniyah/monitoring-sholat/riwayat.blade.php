@extends('layouts.tenant')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ tenant_route('dashboard.diniyah.monitoring-sholat.index', ['programSlug' => $programSlug]) }}" class="p-2 bg-gray-100 dark:bg-gray-800 rounded-lg">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Riwayat Monitoring Sholat</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $santri->name }} — {{ $program->name }}</p>
            </div>
        </div>
    </div>

    @if($records->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-mosque text-gray-400 dark:text-gray-500 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum ada data</h3>
            <p class="text-gray-500 dark:text-gray-400">Santri belum memiliki riwayat monitoring sholat.</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Tanggal</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Waktu</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Status</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Berjamaah</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-600 dark:text-gray-400">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($records as $record)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                            {{ $record->date?->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100 capitalize">
                            {{ $record->aspect }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-1 rounded text-xs font-medium
                                @if($record->status === 'hadir') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                                @elseif($record->status === 'tidak_hadir') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                @else bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 @endif">
                                {{ $record->getStatusLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                            @if($record->flag)
                                <span class="text-emerald-600"><i class="fa-solid fa-check"></i> Ya</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                            {{ $record->notes ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    @endif
</div>
@endsection
