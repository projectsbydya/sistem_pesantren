@extends('layouts.tenant')

@section('title', 'Nilai ' . strtoupper($programSlug) . ' — ' . $santri->name)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Progress Nilai {{ strtoupper($programSlug) }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $santri->name }} — {{ $santri->kelas?->name ?? 'Tanpa Kelas' }}</p>
    </div>

    @if($records->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum ada data nilai</h3>
        </div>
    @else
        @foreach($bySubject as $subjectId => $subjectRecords)
            @php
                $subjectName   = $subjectRecords->first()->subject?->name ?? 'N/A';
                $byType        = $subjectRecords->groupBy('assessment_type');
            @endphp
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 mb-5 overflow-hidden">

                {{-- Subject header --}}
                <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $subjectName }}</h3>
                    <div class="flex items-center gap-2">
                        @foreach($assessmentTypes as $type => $label)
                            @php
                                $typeRecs  = $byType->get($type, collect());
                                $typeMeta  = $assessmentMeta[$type] ?? null;
                                $color     = $typeMeta?->color ?? 'gray';
                                $isAverage = ($typeMeta?->aggregation ?? 'latest') === 'average';
                                if ($isAverage) {
                                    $summary = $typeRecs->count() > 0
                                        ? 'Avg ' . number_format($typeRecs->whereNotNull('nilai')->avg('nilai'), 1)
                                        : null;
                                } else {
                                    $latest = $typeRecs->sortByDesc('tanggal')->first();
                                    $summary = $latest ? number_format($latest->nilai, 1) : null;
                                }
                            @endphp
                            @if($summary !== null)
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $color }}-100 dark:bg-{{ $color }}-500/20 text-{{ $color }}-700 dark:text-{{ $color }}-400">
                                    {{ $label }}: {{ $summary }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Records per assessment type --}}
                @foreach($assessmentTypes as $type => $label)
                    @php
                        $typeRecs = $byType->get($type, collect());
                        $color    = $assessmentMeta[$type]->color ?? 'gray';
                    @endphp
                    @if($typeRecs->isNotEmpty())
                        <div>
                            <div class="px-4 py-1.5 bg-{{ $color }}-50 dark:bg-{{ $color }}-500/10 border-y border-{{ $color }}-100 dark:border-{{ $color }}-500/20">
                                <span class="text-xs font-bold uppercase tracking-wide text-{{ $color }}-700 dark:text-{{ $color }}-400">
                                    {{ $label }}
                                </span>
                            </div>
                            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                                <thead class="bg-gray-50/50 dark:bg-gray-800/30">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Tanggal</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Materi</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 w-20">Nilai</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Ustadz</th>
                                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                    @foreach($typeRecs as $rec)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $rec->tanggal->format('d/m/Y') }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">{{ $rec->materi ?? '-' }}</td>
                                            <td class="px-4 py-2">
                                                @if($rec->nilai !== null)
                                                    <span class="text-sm font-bold
                                                        {{ $rec->nilai >= 75 ? 'text-emerald-600 dark:text-emerald-400' : ($rec->nilai >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                                                        {{ number_format($rec->nilai, 1) }}
                                                    </span>
                                                @else
                                                    <span class="text-sm text-gray-400">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">
                                                {{ $rec->ustadzKelas?->ustadz?->user?->name ?? '-' }}
                                            </td>
                                            <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $rec->catatan ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endforeach

            </div>
        @endforeach
    @endif
</div>
@endsection
