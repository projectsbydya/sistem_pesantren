@extends('layouts.tenant')

@php
    $typeConfig = $viewMeta['typeConfig'];
    $title = $viewMeta['title'];
    $icon = $viewMeta['icon'];
    // NOTE: $typeConfig['member_fields'] is just the raw list of field-name
    // strings from the registry's TYPES entry — the resolved field
    // definitions (label/type/column) come from memberFields($type).
    $memberFields = \App\Services\Academic\AcademicAssignmentRegistry::memberFields($viewMeta['type']);
    $memberStatusLabels = $typeConfig['member_status_labels'] ?? [];
    $memberStatusColors = $typeConfig['member_status_colors'] ?? [];
@endphp

@section('title', $title . ' - ' . $santri->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ tenant_route('dashboard.' . $pack . '.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $viewMeta['variant'] ?? null]) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $title }} &mdash; {{ $santri->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $program->name }}</p>
        </div>
    </div>

    @if($records->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
            <p class="text-gray-500 dark:text-gray-400">Belum ada tugas {{ strtolower($title) }} untuk santri ini.</p>
        </div>
    @else
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Tugas</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Kelas</th>
                        @foreach($memberFields as $fieldName => $fieldDef)
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">{{ $fieldDef['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($records as $rec)
                        @php
                            $color = $memberStatusColors[$rec->status] ?? 'gray';
                            $assignment = $rec->assignment;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $assignment->created_at?->format('d/m/Y') }}</td>
                            <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $assignment->title }}</td>
                            <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400">{{ $assignment->kelas?->name ?? '-' }}</td>
                            @foreach($memberFields as $fieldName => $fieldDef)
                                @php
                                    $fieldValue = $fieldDef['column']
                                        ? $rec->{$fieldName}
                                        : ($rec->metadata[$fieldName] ?? null);
                                @endphp
                                <td class="px-4 py-2">
                                    @if($fieldName === 'status')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $color }}-100 dark:bg-{{ $color }}-500/20 text-{{ $color }}-700 dark:text-{{ $color }}-400">
                                            {{ $memberStatusLabels[$fieldValue] ?? $fieldValue }}
                                        </span>
                                    @elseif($fieldName === 'is_video_submission')
                                        <span class="text-xs text-gray-600 dark:text-gray-300">{{ $fieldValue ? 'Ya' : 'Tidak' }}</span>
                                    @elseif($fieldName === 'score' && $fieldValue !== null)
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $fieldValue }}</span>
                                    @else
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $fieldValue ?? '-' }}</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
