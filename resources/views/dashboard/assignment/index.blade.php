@extends('layouts.tenant')

@php
    $typeConfig = $viewMeta['typeConfig'];
    $title = $viewMeta['title'];
    $icon = $viewMeta['icon'];
    $assignmentFields = $typeConfig['assignment_fields'] ?? [];
    $memberFields = \App\Services\Academic\AcademicAssignmentRegistry::memberFields($viewMeta['type']);
    $memberStatuses = $typeConfig['member_statuses'] ?? [];
    $memberStatusLabels = $typeConfig['member_status_labels'] ?? [];
    $memberStatusColors = $typeConfig['member_status_colors'] ?? [];
    $assignmentStates = \App\Services\Academic\AcademicAssignmentRegistry::assignmentStates();
    $assignmentStateLabels = \App\Services\Academic\AcademicAssignmentRegistry::assignmentStateLabels();
    $assignmentStateColors = \App\Services\Academic\AcademicAssignmentRegistry::assignmentStateColors();

    $pageTheme = match ($viewMeta['type'] ?? $viewMeta['feature'] ?? '') {
        'diniyah-hafalan-doa', 'hafalan-doa', 'doa' => [
            'icon' => 'fa-hands-praying',
            'gradient' => 'from-blue-500 via-indigo-600 to-violet-800',
            'statColor' => 'blue',
        ],
        'diniyah-hafalan-hadits', 'hafalan-hadits', 'hadits' => [
            'icon' => 'fa-book-open',
            'gradient' => 'from-amber-400 via-orange-600 to-red-700',
            'statColor' => 'amber',
        ],
        'diniyah-hafalan-surat', 'hafalan-surat', 'surat' => [
            'icon' => 'fa-book-quran',
            'gradient' => 'from-emerald-500 via-teal-700 to-cyan-900',
            'statColor' => 'emerald',
        ],
        default => [
            'icon' => $icon,
            'gradient' => 'from-emerald-500 via-teal-700 to-cyan-900',
            'statColor' => 'emerald',
        ],
    };

    $totalAssignments = $records->count();
    $publishedCount = $records->where('state', 'published')->count();
    $draftCount = $records->where('state', 'draft')->count();
    $archivedCount = $records->where('state', 'archived')->count();
    $totalMembers = $records->sum(fn ($r) => $r->members->count());
@endphp

@section('title', $title . ' - ' . $program->name)

@section('content')
<div class="max-w-6xl mx-auto" x-data="{ createOpen: false }">
    {{-- Hero header --}}
    <div class="mb-6 rounded-2xl bg-gradient-to-r {{ $pageTheme['gradient'] }} p-6 text-white shadow-lg">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center shrink-0">
                    <i class="fa-solid {{ $pageTheme['icon'] }} text-2xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold">{{ $title }}</h1>
                    <p class="text-white/80 text-sm mt-0.5">{{ $program->name }} &mdash; tugas per kelas</p>
                </div>
            </div>
            @can('create', App\Models\Assignment::class)
                <button type="button" @click="createOpen = !createOpen" class="px-4 py-2 bg-white hover:bg-gray-100 text-{{ $pageTheme['statColor'] }}-700 rounded-lg text-sm font-semibold shadow-lg transition-colors">
                    <i class="fa-solid fa-plus mr-1"></i> <span x-text="createOpen ? 'Tutup Form' : 'Tambah Tugas'"></span>
                </button>
            @endcan
        </div>
    </div>

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-stat-card title="Total Tugas" :value="$totalAssignments" :icon="$pageTheme['icon']" :color="$pageTheme['statColor']" />
        <x-stat-card title="Tugas Aktif" :value="$publishedCount" icon="fa-check-circle" color="emerald" />
        <x-stat-card title="Draft" :value="$draftCount" icon="fa-pen-to-square" color="gray" />
        <x-stat-card title="Santri Terdaftar" :value="$totalMembers" icon="fa-users" color="blue" />
    </div>

    @can('create', App\Models\Assignment::class)
    <div x-show="createOpen" x-cloak class="mb-6">
        <x-card title="Buat Tugas Baru" subtitle="{{ $title }} untuk kelas tertentu">
            <form method="POST" action="{{ tenant_route('dashboard.' . $pack . '.' . $featureSlug . '.store', ['programSlug' => $programSlug, 'type' => $viewMeta['variant'] ?? null]) }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf

                <div class="md:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kelas <span class="text-red-400">*</span></label>
                    <select name="kelas_id" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                        <option value="">Pilih Kelas</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                        @endforeach
                    </select>
                </div>

                @foreach($assignmentFields as $field)
                    @php
                        $name = $field['column'] ? $field['name'] : 'metadata[' . $field['name'] . ']';
                        $old = $field['column'] ? old($field['name']) : old('metadata.' . $field['name']);
                    @endphp
                    <div class="{{ in_array($field['type'], ['textarea']) ? 'md:col-span-3' : '' }}">
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                            {{ $field['label'] }}
                            @if(!empty($field['required'])) <span class="text-red-400">*</span> @endif
                        </label>

                        @if($field['type'] === 'textarea')
                            <textarea name="{{ $name }}" @if(!empty($field['required'])) required @endif rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">{{ $old }}</textarea>
                        @elseif($field['type'] === 'select')
                            <select name="{{ $name }}" @if(!empty($field['required'])) required @endif class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                                @foreach($field['options'] ?? [] as $key => $label)
                                    <option value="{{ $key }}" {{ (string) $old === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif($field['type'] === 'theme')
                            <select name="{{ $name }}" @if(!empty($field['required'])) required @endif class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                                <option value="">Pilih Tema</option>
                                @foreach($themeList as $theme)
                                    <option value="{{ $theme->id }}" {{ (int) $old === $theme->id ? 'selected' : '' }}>{{ $theme->name }}</option>
                                @endforeach
                            </select>
                        @elseif($field['type'] === 'number')
                            <input type="number" name="{{ $name }}" value="{{ $old }}" @if(!empty($field['required'])) required @endif @if(isset($field['min'])) min="{{ $field['min'] }}" @endif @if(isset($field['max'])) max="{{ $field['max'] }}" @endif class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                        @elseif($field['type'] === 'date')
                            <input type="date" name="{{ $name }}" value="{{ $old }}" @if(!empty($field['required'])) required @endif class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                        @else
                            <input type="{{ $field['type'] }}" name="{{ $name }}" value="{{ $old }}" @if(!empty($field['required'])) required @endif @if(!empty($field['max'])) maxlength="{{ $field['max'] }}" @endif @if(!empty($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                        @endif
                    </div>
                @endforeach

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status Tugas</label>
                    <select name="state" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                        @foreach($assignmentStates as $st)
                            <option value="{{ $st }}" {{ $st === 'published' ? 'selected' : '' }}>{{ $assignmentStateLabels[$st] ?? $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Deadline</label>
                    <input type="date" name="due_date" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                </div>

                <div class="md:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Catatan Tugas</label>
                    <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm"></textarea>
                </div>

                <div class="md:col-span-3 flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">Simpan</button>
                    <button type="button" @click="createOpen = false" class="px-4 py-2 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-700 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-800">Batal</button>
                </div>
            </form>
        </x-card>
    </div>
    @endcan

    {{-- Filter & search --}}
    @unless($isReadOnlyViewer ?? false)
    <x-card class="mb-6">
        <div class="flex flex-col lg:flex-row gap-4 items-end justify-between">
            <form method="GET" action="{{ tenant_route('dashboard.' . $pack . '.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $viewMeta['variant'] ?? null]) }}" class="flex flex-row flex-wrap items-end gap-3 w-full lg:w-auto">
                <div class="w-48">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kelas</label>
                    <select name="kelas_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ (int) $kelasId === $kelas->id ? 'selected' : '' }}>{{ $kelas->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-semibold hover:bg-gray-900 shadow-sm"><i class="fa-solid fa-filter mr-1"></i> Filter</button>
                @if($kelasId)
                    <a href="{{ tenant_route('dashboard.' . $pack . '.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $viewMeta['variant'] ?? null]) }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700">Reset</a>
                @endif
            </form>

            <div class="relative w-full lg:w-72">
                <button type="button" id="assignmentSearchButton" class="absolute left-3 top-1/2 -translate-y-1/2 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none" aria-label="Cari">
                    <i class="fa-solid fa-search"></i>
                </button>
                <input type="text" id="assignmentSearch" placeholder="Cari tugas, kelas, atau santri..." class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm pl-10 pr-4">
            </div>
        </div>
    </x-card>
    @endunless

    @if($records->isEmpty())
        <x-empty-state title="Belum ada tugas"
                       :message="'Data ' . strtolower($title) . ' akan muncul setelah tugas dibuat.'"
                       :icon="$icon"
                       variant="default" />
    @else
        <div id="assignmentList" class="space-y-4">
            @foreach($records as $rec)
                @php
                    $stateColor = $assignmentStateColors[$rec->state] ?? 'gray';
                    $memberCount = $rec->members->count();
                    $completedCount = $rec->members->where('status', last($memberStatuses))->count();
                    $progress = $memberCount > 0 ? round(($completedCount / $memberCount) * 100) : 0;
                @endphp
                @php
                    $searchText = strtolower(
                        ($rec->title ?? '') . ' ' .
                        ($rec->kelas?->name ?? '') . ' ' .
                        ($rec->target ?? '') . ' ' .
                        $rec->members->pluck('santri.name')->filter()->implode(' ')
                    );
                    $searchText = preg_replace('/\s+/', ' ', trim(str_replace(["\r", "\n"], ' ', $searchText)));
                @endphp
                <x-card class="assignment-card overflow-hidden" :padding="false" data-search="{{ $searchText }}">
                    <div x-data="{ open: false }">
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors" @click="open = !open">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <i class="fa-solid {{ $pageTheme['icon'] }} text-{{ $pageTheme['statColor'] }}-500 text-sm"></i>
                                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $rec->title }}</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-{{ $stateColor }}-100 dark:bg-{{ $stateColor }}-500/20 text-{{ $stateColor }}-700 dark:text-{{ $stateColor }}-400">
                                        {{ $assignmentStateLabels[$rec->state] ?? $rec->state }}
                                    </span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-300">
                                        <i class="fa-solid fa-chalkboard text-[10px] text-gray-500 dark:text-gray-400"></i>
                                        {{ $rec->kelas?->name ?? 'Tanpa Kelas' }}
                                    </span>
                                    @if($rec->due_date)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-300">
                                            <i class="fa-solid fa-calendar-days text-[10px] text-gray-500 dark:text-gray-400"></i>
                                            {{ $rec->due_date->format('d/m/Y') }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800 text-xs text-gray-600 dark:text-gray-300">
                                        <i class="fa-solid fa-users text-[10px] text-gray-500 dark:text-gray-400"></i>
                                        {{ $memberCount }} santri
                                    </span>
                                </div>
                                @if($rec->target)
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 truncate">{{ $rec->target }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <div class="hidden sm:block text-right w-28">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="text-gray-500 dark:text-gray-400">Progress</span>
                                        <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $progress }}%</span>
                                    </div>
                                    <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-{{ $pageTheme['statColor'] }}-500 rounded-full" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    @can('update', $rec)
                                        <a href="{{ tenant_route('dashboard.' . $pack . '.' . $featureSlug . '.edit', ['programSlug' => $programSlug, 'id' => $rec->id, 'type' => $viewMeta['variant'] ?? null]) }}" class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors" title="Edit"><i class="fa-solid fa-pen text-[11px]"></i></a>
                                    @endcan
                                    @can('delete', $rec)
                                        <form method="POST" action="{{ tenant_route('dashboard.' . $pack . '.' . $featureSlug . '.destroy', ['programSlug' => $programSlug, 'id' => $rec->id, 'type' => $viewMeta['variant'] ?? null]) }}" onsubmit="return confirm('Hapus tugas ini?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-md transition-colors" title="Hapus"><i class="fa-solid fa-trash text-[11px]"></i></button>
                                        </form>
                                    @endcan
                                    <span class="text-xs text-gray-400 dark:text-gray-500 ml-1"><i class="fa-solid fa-chevron-down transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i></span>
                                </div>
                            </div>
                        </div>

                        <div x-show="open" x-cloak class="border-t border-gray-100 dark:border-gray-800 p-4 sm:p-5">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Progress Santri</h4>
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $completedCount }}/{{ $memberCount }} selesai</span>
                            </div>
                            <div class="sm:hidden h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-4">
                                <div class="h-full bg-{{ $pageTheme['statColor'] }}-500 rounded-full" style="width: {{ $progress }}%"></div>
                            </div>
                            <div class="overflow-x-auto -mx-4 sm:-mx-5 px-4 sm:px-5">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                                        <tr>
                                            <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Santri</th>
                                            @foreach($memberFields as $fieldName => $fieldDef)
                                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $fieldDef['label'] }}</th>
                                            @endforeach
                                            <th class="px-3 py-2 text-right text-xs font-semibold text-gray-500 dark:text-gray-400">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                        @forelse($rec->members as $member)
                                            @can('view', $member)
                                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                                <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">
                                                    <a href="{{ tenant_route('dashboard.' . $pack . '.' . $featureSlug . '.show', ['programSlug' => $programSlug, 'id' => $member->santri_id, 'type' => $viewMeta['variant'] ?? null]) }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 font-medium">
                                                        {{ $member->santri?->name ?? '—' }}
                                                    </a>
                                                </td>
                                                @foreach($memberFields as $fieldName => $fieldDef)
                                                    @php
                                                        $value = $fieldDef['column']
                                                            ? $member->{$fieldName}
                                                            : ($member->metadata[$fieldName] ?? null);
                                                    @endphp
                                                    <td class="px-3 py-2">
                                                        @if($fieldName === 'status')
                                                            @php $color = $memberStatusColors[$value] ?? 'gray'; @endphp
                                                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-{{ $color }}-100 dark:bg-{{ $color }}-500/20 text-{{ $color }}-700 dark:text-{{ $color }}-400">
                                                                {{ $memberStatusLabels[$value] ?? $value }}
                                                            </span>
                                                        @elseif($fieldName === 'is_video_submission')
                                                            <span class="text-xs text-gray-600 dark:text-gray-300">{{ $value ? 'Ya' : 'Tidak' }}</span>
                                                        @elseif($fieldName === 'score' && $value !== null)
                                                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $value }}</span>
                                                        @elseif(in_array($fieldName, ['progress', 'notes']))
                                                            <span class="text-xs text-gray-600 dark:text-gray-300">{{ Str::limit($value ?? '-', 40) }}</span>
                                                        @else
                                                            <span class="text-xs text-gray-600 dark:text-gray-300">{{ $value ?? '-' }}</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="px-3 py-2 text-right">
                                                    @can('update', $member)
                                                        <button type="button" class="text-xs text-emerald-600 hover:text-emerald-700 font-medium" onclick="document.getElementById('member-form-{{ $member->id }}').classList.toggle('hidden')">Edit</button>
                                                    @endcan
                                                </td>
                                            </tr>

                                            @can('update', $member)
                                            <tr id="member-form-{{ $member->id }}" class="hidden bg-gray-50 dark:bg-gray-800/30">
                                                <td colspan="{{ count($memberFields) + 2 }}" class="px-3 py-2">
                                                    <form method="POST" action="{{ tenant_route('dashboard.' . $pack . '.' . $featureSlug . '.update-status', ['programSlug' => $programSlug, 'id' => $member->id, 'type' => $viewMeta['variant'] ?? null]) }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                                                        @csrf @method('PATCH')
                                                        @foreach($memberFields as $fieldName => $fieldDef)
                                                            @php
                                                                $fieldValue = $fieldDef['column']
                                                                    ? $member->{$fieldName}
                                                                    : ($member->metadata[$fieldName] ?? null);
                                                            @endphp
                                                            <div>
                                                                <label class="block text-[10px] font-medium text-gray-500 dark:text-gray-400 mb-1">{{ $fieldDef['label'] }}</label>
                                                                @if($fieldName === 'status')
                                                                    <select name="status" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs">
                                                                        @foreach($memberStatuses as $st)
                                                                            <option value="{{ $st }}" {{ $fieldValue === $st ? 'selected' : '' }}>{{ $memberStatusLabels[$st] ?? $st }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                @elseif($fieldName === 'progress' || $fieldName === 'notes')
                                                                    <textarea name="{{ $fieldName }}" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs">{{ $fieldValue }}</textarea>
                                                                @elseif($fieldName === 'is_video_submission')
                                                                    <select name="{{ $fieldName }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs">
                                                                        <option value="1" {{ $fieldValue ? 'selected' : '' }}>Ya</option>
                                                                        <option value="0" {{ ! $fieldValue ? 'selected' : '' }}>Tidak</option>
                                                                    </select>
                                                                @else
                                                                    <input type="{{ $fieldDef['type'] }}" name="{{ $fieldName }}" value="{{ $fieldValue }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-xs">
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                        <div class="flex items-center gap-2">
                                                            <button type="submit" class="px-3 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700">Simpan</button>
                                                            <button type="button" class="px-3 py-2 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-700 rounded-lg text-xs hover:bg-gray-50 dark:hover:bg-gray-800" onclick="document.getElementById('member-form-{{ $member->id }}').classList.add('hidden')">Batal</button>
                                                        </div>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endcan
                                            @endcan
                                        @empty
                                            <tr><td colspan="{{ count($memberFields) + 2 }}" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400 text-center">Belum ada santri di kelas ini.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </x-card>
            @endforeach

            <div id="noSearchResults" class="hidden bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-8 text-center">
                <div class="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid fa-search text-gray-400 dark:text-gray-500"></i>
                </div>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Tidak ada tugas yang cocok</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Coba kata kunci lain.</p>
            </div>
        </div>
    @endif
</div>

<script>
    (function () {
        function initSearch() {
            const searchInput = document.getElementById('assignmentSearch');
            const searchButton = document.getElementById('assignmentSearchButton');
            const cards = document.querySelectorAll('.assignment-card');
            const noResults = document.getElementById('noSearchResults');

            if (!searchInput) {
                return;
            }

            function normalize(str) {
                return (str || '').toLowerCase().trim();
            }

            function applySearch() {
                const term = normalize(searchInput.value);
                let visibleCount = 0;

                cards.forEach(function (card) {
                    const text = normalize(card.dataset.search);
                    const isMatch = !term || text.includes(term);

                    if (isMatch) {
                        card.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        card.classList.add('hidden');
                    }
                });

                if (noResults) {
                    if (term && visibleCount === 0) {
                        noResults.classList.remove('hidden');
                    } else {
                        noResults.classList.add('hidden');
                    }
                }
            }

            searchInput.addEventListener('input', applySearch);
            searchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    applySearch();
                }
            });

            if (searchButton) {
                searchButton.addEventListener('click', function (event) {
                    event.preventDefault();
                    searchInput.focus();
                    applySearch();
                });
            }

            applySearch();
        }

        document.addEventListener('DOMContentLoaded', initSearch);
    })();
</script>
@endsection
