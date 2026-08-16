@extends('layouts.tenant')

@php
    $typeConfig = $viewMeta['typeConfig'];
    $title = $viewMeta['title'];
    $icon = $viewMeta['icon'];
    $assignmentFields = $typeConfig['assignment_fields'] ?? [];
    $assignmentStates = \App\Services\Academic\AcademicAssignmentRegistry::assignmentStates();
    $assignmentStateLabels = \App\Services\Academic\AcademicAssignmentRegistry::assignmentStateLabels();
@endphp

@section('title', 'Edit ' . $title . ' - ' . $program->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ tenant_route('dashboard.' . $pack . '.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $viewMeta['variant'] ?? null]) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Edit {{ $title }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $program->name }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
        <form method="POST" action="{{ tenant_route('dashboard.' . $pack . '.' . $featureSlug . '.update', ['programSlug' => $programSlug, 'id' => $assignment->id, 'type' => $viewMeta['variant'] ?? null]) }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kelas</label>
                <select name="kelas_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    <option value="">Tanpa Kelas</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" {{ (int) $assignment->kelas_id === $kelas->id ? 'selected' : '' }}>{{ $kelas->name }}</option>
                    @endforeach
                </select>
            </div>

            @foreach($assignmentFields as $field)
                @php
                    $name = $field['column'] ? $field['name'] : 'metadata[' . $field['name'] . ']';
                    $value = $field['column'] ? $assignment->{$field['name']} : ($assignment->metadata[$field['name']] ?? null);
                @endphp
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                        {{ $field['label'] }}
                        @if(!empty($field['required'])) <span class="text-red-400">*</span> @endif
                    </label>

                    @if($field['type'] === 'textarea')
                        <textarea name="{{ $name }}" @if(!empty($field['required'])) required @endif rows="3" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">{{ $value }}</textarea>
                    @elseif($field['type'] === 'select')
                        <select name="{{ $name }}" @if(!empty($field['required'])) required @endif class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                            @foreach($field['options'] ?? [] as $key => $label)
                                <option value="{{ $key }}" {{ (string) $value === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    @elseif($field['type'] === 'theme')
                        <select name="{{ $name }}" @if(!empty($field['required'])) required @endif class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                            <option value="">Pilih Tema</option>
                            @foreach($themeList as $theme)
                                <option value="{{ $theme->id }}" {{ (int) $value === $theme->id ? 'selected' : '' }}>{{ $theme->name }}</option>
                            @endforeach
                        </select>
                    @elseif($field['type'] === 'number')
                        <input type="number" name="{{ $name }}" value="{{ $value }}" @if(!empty($field['required'])) required @endif @if(isset($field['min'])) min="{{ $field['min'] }}" @endif @if(isset($field['max'])) max="{{ $field['max'] }}" @endif class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    @elseif($field['type'] === 'date')
                        <input type="date" name="{{ $name }}" value="{{ $value }}" @if(!empty($field['required'])) required @endif class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    @else
                        <input type="{{ $field['type'] }}" name="{{ $name }}" value="{{ $value }}" @if(!empty($field['required'])) required @endif @if(!empty($field['max'])) maxlength="{{ $field['max'] }}" @endif class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                    @endif
                </div>
            @endforeach

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status Tugas</label>
                    <select name="state" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                        @foreach($assignmentStates as $st)
                            <option value="{{ $st }}" {{ $assignment->state === $st ? 'selected' : '' }}>{{ $assignmentStateLabels[$st] ?? $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Deadline</label>
                    <input type="date" name="due_date" value="{{ $assignment->due_date?->format('Y-m-d') }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Catatan Tugas</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm">{{ $assignment->notes }}</textarea>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700">Simpan</button>
                <a href="{{ tenant_route('dashboard.' . $pack . '.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $viewMeta['variant'] ?? null]) }}" class="px-4 py-2 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-700 rounded-lg text-sm hover:bg-gray-50 dark:hover:bg-gray-800">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
