@extends('layouts.tenant')

@php
    $isMuhadhoroh = $record->type === 'muhadhoroh';
    $typeLabel    = \App\Models\Muhadhoroh::getLabels()[$record->type] ?? 'Muhadhoroh';
    $icon         = $isMuhadhoroh ? 'fa-microphone' : 'fa-person-chalkboard';
    $accentBg     = $isMuhadhoroh ? 'bg-purple-50 dark:bg-purple-500/10' : 'bg-indigo-50 dark:bg-indigo-500/10';
    $accentTxt    = $isMuhadhoroh ? 'text-purple-600 dark:text-purple-400' : 'text-indigo-600 dark:text-indigo-400';
    $accentFocus  = $isMuhadhoroh ? 'focus:ring-purple-500/20 focus:border-purple-500' : 'focus:ring-indigo-500/20 focus:border-indigo-500';
    $accentSave   = $isMuhadhoroh ? 'bg-purple-600 hover:bg-purple-700' : 'bg-indigo-600 hover:bg-indigo-700';
@endphp

@section('title', 'Edit ' . $typeLabel)
@section('page-title', 'Edit ' . $typeLabel)
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $record->type]) }}"
       class="text-gray-600 dark:text-gray-300 hover:text-purple-600">{{ $typeLabel }}</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">{{ Str::limit($record->title, 40) }}</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="flex items-center gap-3 mb-6">
    <a href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $record->type]) }}"
       class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors">
        <i class="fa-solid fa-arrow-left text-sm"></i>
    </a>
    <div class="w-9 h-9 rounded-lg {{ $accentBg }} {{ $accentTxt }} flex items-center justify-center shrink-0">
        <i class="fa-solid {{ $icon }} text-sm"></i>
    </div>
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Edit {{ $typeLabel }}</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            {{ $program->name }} — perbarui data {{ $isMuhadhoroh ? 'muhadhoroh' : 'public speaking' }}
        </p>
    </div>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <form method="POST"
          action="{{ tenant_route('dashboard.modern.' . $featureSlug . '.update', ['programSlug' => $programSlug, 'id' => $record->id]) }}"
          class="p-6">
        @csrf
        @method('PUT')

        {{-- Santri (read-only) --}}
        <div class="mb-6 pb-6 border-b border-gray-100 dark:border-gray-800">
            <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                <i class="fa-solid fa-user-graduate mr-1.5 text-gray-400"></i>Santri
            </label>
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700">
                <div class="w-10 h-10 rounded-full {{ $accentBg }} {{ $accentTxt }} flex items-center justify-center text-[14px] font-bold shrink-0">
                    {{ strtoupper(substr($record->santri->name ?? 'S', 0, 1)) }}
                </div>
                <div>
                    <p class="text-[14px] font-medium text-gray-900 dark:text-gray-100">{{ $record->santri->name ?? '—' }}</p>
                    <p class="text-[12px] text-gray-500 dark:text-gray-400 font-mono">{{ $record->santri->nis ?? '—' }}</p>
                </div>
                <span class="ml-auto text-[11px] text-gray-400 dark:text-gray-500 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">Read-only</span>
            </div>
            <input type="hidden" name="santri_id" value="{{ $record->santri_id }}">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Title --}}
            <div class="md:col-span-2">
                <label for="title" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <i class="fa-solid {{ $icon }} mr-1.5 {{ $accentTxt }} text-[11px]"></i>Judul <span class="text-red-500">*</span>
                </label>
                <input type="text" id="title" name="title"
                       value="{{ old('title', $record->title) }}"
                       required maxlength="255"
                       placeholder="{{ $isMuhadhoroh ? 'Judul pidato...' : 'Judul presentasi...' }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                              {{ $accentFocus }} transition-colors
                              @error('title') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('title') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Theme --}}
            <div>
                <label for="theme_id" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <i class="fa-solid fa-tag mr-1.5 text-gray-400 text-[11px]"></i>Tema
                </label>
                <select id="theme_id" name="theme_id"
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100 {{ $accentFocus }} transition-colors
                               @error('theme_id') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    <option value="">— Pilih tema —</option>
                    @foreach($themeList as $theme)
                        <option value="{{ $theme->id }}" {{ old('theme_id', $record->theme_id) == $theme->id ? 'selected' : '' }}>{{ $theme->name }}</option>
                    @endforeach
                </select>
                @error('theme_id') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Language --}}
            <div>
                <label for="language" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <i class="fa-solid fa-language mr-1.5 text-gray-400 text-[11px]"></i>Bahasa
                </label>
                <input type="text" id="language" name="language"
                       value="{{ old('language', $record->language) }}"
                       maxlength="10"
                       placeholder="ar / en / id"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 {{ $accentFocus }} transition-colors">
            </div>

            {{-- Score --}}
            <div>
                <label for="score" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <i class="fa-solid fa-star-half-stroke mr-1.5 text-amber-400 text-[11px]"></i>Nilai
                </label>
                <input type="number" id="score" name="score"
                       value="{{ old('score', $record->score) }}"
                       min="0" max="100"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 {{ $accentFocus }} transition-colors
                              @error('score') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('score') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Performed At --}}
            <div>
                <label for="performed_at" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <i class="fa-solid fa-calendar-day mr-1.5 text-gray-400 text-[11px]"></i>Tanggal Tampil
                </label>
                <input type="date" id="performed_at" name="performed_at"
                       value="{{ old('performed_at', $record->performed_at?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 {{ $accentFocus }} transition-colors">
            </div>

            {{-- Description --}}
            <div class="md:col-span-2">
                <label for="description" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <i class="fa-solid fa-align-left mr-1.5 text-gray-400 text-[11px]"></i>Deskripsi / Sinopsis
                </label>
                <textarea id="description" name="description" rows="3"
                          placeholder="{{ $isMuhadhoroh ? 'Ringkasan isi pidato...' : 'Ringkasan presentasi...' }}"
                          class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                                 text-gray-900 dark:text-gray-100 {{ $accentFocus }} transition-colors">{{ old('description', $record->description) }}</textarea>
            </div>

            {{-- Video Submission --}}
            <div>
                <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fa-solid fa-video mr-1.5 text-gray-400 text-[11px]"></i>Submission Video
                </label>
                <label class="inline-flex items-center gap-2.5 cursor-pointer">
                    <input type="checkbox" name="is_video_submission" id="is_video_edit" value="1"
                           {{ old('is_video_submission', $record->is_video_submission) ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-purple-600 focus:ring-purple-500">
                    <span class="text-[13px] text-gray-600 dark:text-gray-400">Ya, ini adalah video submission</span>
                </label>
            </div>

            {{-- Submission URL --}}
            <div>
                <label for="submission_url" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <i class="fa-solid fa-link mr-1.5 text-gray-400 text-[11px]"></i>URL Submission
                </label>
                <input type="url" id="submission_url" name="submission_url"
                       value="{{ old('submission_url', $record->submission_url) }}"
                       maxlength="500"
                       placeholder="https://..."
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 {{ $accentFocus }} transition-colors
                              @error('submission_url') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('submission_url') <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p> @enderror
            </div>

            {{-- Notes --}}
            <div class="md:col-span-2">
                <label for="notes" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    <i class="fa-solid fa-note-sticky mr-1.5 text-gray-400 text-[11px]"></i>Catatan
                </label>
                <textarea id="notes" name="notes" rows="2"
                          class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                                 text-gray-900 dark:text-gray-100 {{ $accentFocus }} transition-colors">{{ old('notes', $record->notes) }}</textarea>
            </div>

        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            @can('delete', $record)
                <form method="POST"
                      action="{{ tenant_route('dashboard.modern.' . $featureSlug . '.destroy', ['programSlug' => $programSlug, 'id' => $record->id]) }}"
                      onsubmit="return confirm('Yakin ingin menghapus data muhadhoroh ini?')" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 text-[13px] font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                        <i class="fa-solid fa-trash mr-1.5"></i> Hapus
                    </button>
                </form>
            @else
                <div></div>
            @endcan

            <div class="flex items-center gap-3">
                <a href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $record->type]) }}"
                   class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <i class="fa-solid fa-xmark mr-1.5"></i> Batal
                </a>
                <button type="submit"
                        class="px-4 py-2 text-[13px] font-medium text-white {{ $accentSave }} rounded-lg transition-colors">
                    <i class="fa-solid fa-check mr-1.5"></i> Simpan Perubahan
                </button>
            </div>
        </div>

    </form>
</div>

@endsection
