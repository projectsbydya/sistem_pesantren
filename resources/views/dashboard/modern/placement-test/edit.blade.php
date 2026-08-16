@extends('layouts.tenant')

@php
    $isEnglish = ($record->type ?? $type) === 'english';
    $typeLabel = \App\Models\PlacementTest::getLabels()[$record->type ?? $type] ?? 'Placement Test';
    $icon      = 'fa-clipboard-check';
    $accentRing = $isEnglish ? 'focus:ring-blue-500/20 focus:border-blue-500' : 'focus:ring-emerald-500/20 focus:border-emerald-500';
    $accentBtn  = $isEnglish ? 'bg-blue-600 hover:bg-blue-700' : 'bg-emerald-600 hover:bg-emerald-700';
    $accentIcon = $isEnglish ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
@endphp

@section('title', 'Edit ' . $typeLabel . ' — ' . $program->name)
@section('page-title', $typeLabel)
@section('breadcrumb')
    <a href="{{ tenant_route("dashboard.modern.{$featureSlug}.index", ['programSlug' => $programSlug, 'type' => $record->type ?? $type]) }}"
       class="text-gray-600 dark:text-gray-300 hover:text-blue-600">{{ $typeLabel }}</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Edit</span>
@endsection

@section('content')

<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ tenant_route("dashboard.modern.{$featureSlug}.index", ['programSlug' => $programSlug, 'type' => $record->type ?? $type]) }}"
           class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
            <i class="fa-solid fa-arrow-left text-sm"></i>
        </a>
        <div class="w-9 h-9 rounded-lg {{ $accentIcon }} flex items-center justify-center shrink-0">
            <i class="fa-solid {{ $icon }} text-sm"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Edit {{ $typeLabel }}</h1>
            <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $program->name }}</p>
        </div>
    </div>

    <form method="POST"
          action="{{ tenant_route('dashboard.modern.' . $featureSlug . '.update', ['programSlug' => $programSlug, 'id' => $record->id, 'type' => $record->type ?? $type]) }}"
          class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 space-y-4">
        @csrf @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Judul Test <span class="text-red-400">*</span></label>
                <input type="text" name="title" value="{{ old('title', $record->title) }}" required maxlength="255"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors @error('title') border-red-300 @enderror">
                @error('title') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal Test</label>
                <input type="date" name="date" value="{{ old('date', $record->date?->format('Y-m-d')) }}"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Skor Maksimal</label>
                <input type="number" name="max_score" value="{{ old('max_score', $record->max_score) }}" min="0" max="100"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors @error('max_score') border-red-300 @enderror">
                @error('max_score') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Deskripsi</label>
                <input type="text" name="description" value="{{ old('description', $record->description) }}"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Catatan</label>
            <input type="text" name="notes" value="{{ old('notes', $record->notes) }}" maxlength="500"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $record->type ?? $type]) }}"
               class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
                Batal
            </a>
            <button type="submit" class="px-4 py-2 {{ $accentBtn }} text-white rounded-lg text-sm font-semibold transition-colors">
                <i class="fa-solid fa-save mr-1"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

@endsection
