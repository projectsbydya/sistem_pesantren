@extends('layouts.tenant')

@section('title', 'Monitoring Sholat - ' . $santri->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ tenant_route('dashboard.diniyah.monitoring-sholat.index', ['programSlug' => $programSlug]) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Monitoring Sholat — {{ $santri->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $program->name }}</p>
        </div>
    </div>

    @if($records->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
            <p class="text-gray-500 dark:text-gray-400">Belum ada data monitoring sholat untuk santri ini.</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Berjamaah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($records as $rec)
                        @php $color = \App\Models\DiniyahMonitoring::STATUS_COLORS[$rec->status] ?? 'gray'; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $rec->date?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ \App\Models\DiniyahMonitoring::SHOLAT_TIME_LABELS[$rec->aspect] ?? $rec->aspect }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $color }}-100 dark:bg-{{ $color }}-500/20 text-{{ $color }}-700 dark:text-{{ $color }}-400">
                                    {{ \App\Models\DiniyahMonitoring::SHOLAT_STATUS_LABELS[$rec->status] ?? $rec->status }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-sm">{{ $rec->flag ? 'Ya' : 'Tidak' }}</td>
                            <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $rec->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
