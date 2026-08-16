@extends('layouts.tenant')

@php
    $isEnglish = $type === 'english';
    $typeLabel = \App\Models\PlacementTest::getLabels()[$type] ?? 'Placement Test';
    $icon      = $isEnglish ? 'fa-clipboard-check' : 'fa-clipboard-check';
    $accentRing = $isEnglish ? 'focus:ring-blue-500/20 focus:border-blue-500' : 'focus:ring-emerald-500/20 focus:border-emerald-500';
    $accentBtn  = $isEnglish ? 'bg-blue-600 hover:bg-blue-700' : 'bg-emerald-600 hover:bg-emerald-700';
    $accentIcon = $isEnglish ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
@endphp

@section('title', $typeLabel . ' — ' . $program->name)
@section('page-title', $typeLabel)
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">{{ $program->name }}</span>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">{{ $typeLabel }}</span>
@endsection

@section('content')

{{-- Type switcher --}}
@if(!empty($viewMeta['typeSource']))
<div class="flex gap-2 mb-5">
    @foreach(\App\Models\PlacementTest::getLabels() as $t => $label)
        @if(in_array($t, $viewMeta['typeSource']))
        <a href="{{ tenant_route("dashboard.modern.{$featureSlug}.index", ['programSlug' => $programSlug, 'type' => $t]) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors
                  {{ $type === $t ? ($isEnglish ? 'bg-blue-600 text-white border-blue-600' : 'bg-emerald-600 text-white border-emerald-600') : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-gray-400' }}">
            <i class="fa-solid {{ $icon }} mr-1.5 text-[10px]"></i>{{ $label }}
        </a>
        @endif
    @endforeach
</div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <x-stat-card title="{{ $typeLabel }}" value="{{ $records->count() }}" icon="{{ $icon }}" color="{{ $isEnglish ? 'blue' : 'emerald' }}" />
    <x-stat-card title="Peserta" value="{{ $records->sum(fn ($test) => $test->results->count()) }}" icon="fa-users" color="cyan" />
    <x-stat-card title="Rata-rata Nilai" value="{{ $records->flatMap->results->whereNotNull('score')->avg('score') !== null ? number_format($records->flatMap->results->whereNotNull('score')->avg('score'), 1) : '—' }}" icon="fa-star-half-stroke" color="amber" />
</div>

{{-- Header --}}
<div class="flex items-center gap-3 mb-5">
    <div class="w-9 h-9 rounded-lg {{ $accentIcon }} flex items-center justify-center shrink-0">
        <i class="fa-solid {{ $icon }} text-sm"></i>
    </div>
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $typeLabel }}</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            {{ $program->name }} — manajemen placement test dan hasil santri
        </p>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-sm">
        <i class="fa-solid fa-circle-check shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@can('create', [App\Models\PlacementTest::class, $program])
{{-- Add Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <i class="fa-solid fa-plus-circle text-sm {{ $isEnglish ? 'text-blue-500' : 'text-emerald-500' }}"></i>
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tambah {{ $typeLabel }}</h3>
    </div>
    <form method="POST"
          action="{{ tenant_route('dashboard.modern.' . $featureSlug . '.store', ['programSlug' => $programSlug, 'type' => $type]) }}"
          class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Judul Test <span class="text-red-400">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required maxlength="255"
                       placeholder="Judul placement test..."
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors @error('title') border-red-300 @enderror">
                @error('title') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tanggal Test</label>
                <input type="date" name="date" value="{{ old('date') }}"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Skor Maksimal</label>
                <input type="number" name="max_score" value="{{ old('max_score') }}" min="0" max="100"
                       placeholder="0–100"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors @error('max_score') border-red-300 @enderror">
                @error('max_score') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
            </div>

            <div>
                <button type="submit" class="w-full px-4 py-2 {{ $accentBtn }} text-white rounded-lg text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-plus mr-1"></i> Simpan
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Deskripsi</label>
                <input type="text" name="description" value="{{ old('description') }}"
                       placeholder="Keterangan singkat..."
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Catatan</label>
                <input type="text" name="notes" value="{{ old('notes') }}" maxlength="500"
                       placeholder="Catatan..."
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
            </div>
        </div>
    </form>
</div>
@endcan

{{-- Table with inline test detail / result CRUD --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden" x-data="{ detailId: null, editId: null }">
    @if($records->isEmpty())
        <div class="text-center py-16 px-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full {{ $accentIcon }} flex items-center justify-center">
                <i class="fa-solid {{ $icon }} text-2xl"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">
                Belum ada data {{ $typeLabel }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                Tambahkan placement test pertama menggunakan form di atas.
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Judul Test</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Peserta</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rata-rata</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $rec)
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition-colors group"
                        :class="{ 'bg-gray-50/80 dark:bg-gray-800/40': detailId === {{ $rec->id }} }">
                        <td class="px-4 py-3 max-w-[180px]">
                            <span class="text-[13px] font-semibold text-gray-900 dark:text-gray-100 truncate block" title="{{ $rec->title }}">{{ $rec->title }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-400 dark:text-gray-500">{{ $rec->date ? $rec->date->format('d/m/Y') : '—' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-[13px] font-bold text-gray-700 dark:text-gray-300">{{ $rec->results->count() }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-[13px] font-bold text-gray-700 dark:text-gray-300">{{ $rec->results->whereNotNull('score')->avg('score') !== null ? number_format($rec->results->whereNotNull('score')->avg('score'), 1) : '—' }}</span>
                        </td>
                        <td class="px-4 py-3 max-w-[220px]">
                            <span class="text-[12px] text-gray-400 dark:text-gray-500 italic truncate block" title="{{ $rec->description }}">
                                {{ $rec->description ? Str::limit($rec->description, 50) : '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button"
                                        @click="detailId = detailId === {{ $rec->id }} ? null : {{ $rec->id }}; editId = null"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                        title="Lihat detail">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                </button>
                                @can('update', $rec)
                                    <a href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.edit', ['programSlug' => $programSlug, 'id' => $rec->id, 'type' => $type]) }}"
                                       class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors"
                                       title="Edit">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </a>
                                @endcan
                                @can('delete', $rec)
                                    <form method="POST"
                                          action="{{ tenant_route('dashboard.modern.' . $featureSlug . '.destroy', ['programSlug' => $programSlug, 'id' => $rec->id, 'type' => $type]) }}"
                                          onsubmit="return confirm('Hapus placement test ini beserta hasilnya?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-md transition-colors"
                                                title="Hapus">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>

                    {{-- Inline test detail / result CRUD --}}
                    <tr x-show="detailId === {{ $rec->id }}" x-cloak
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/20">
                        <td colspan="6" class="px-4 py-4">
                            <div class="space-y-4">
                                {{-- Test summary --}}
                                <div class="flex flex-wrap gap-3 text-[12px] text-gray-500 dark:text-gray-400">
                                    <span class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-2.5 py-1 rounded-md">
                                        Skor Maksimal: <strong class="text-gray-700 dark:text-gray-300">{{ $rec->max_score ?? '—' }}</strong>
                                    </span>
                                    <span class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-2.5 py-1 rounded-md">
                                        Catatan: {{ $rec->notes ? Str::limit($rec->notes, 40) : '—' }}
                                    </span>
                                </div>

                                @can('create', [App\Models\PlacementTestResult::class, $program])
                                {{-- Add result form --}}
                                <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                                    <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-3">
                                        <i class="fa-solid fa-plus-circle text-[10px] {{ $isEnglish ? 'text-blue-500' : 'text-emerald-500' }} mr-1"></i>
                                        Tambah Hasil
                                    </h4>
                                    <form method="POST"
                                          action="{{ tenant_route('dashboard.modern.' . $featureSlug . '.results.store', ['programSlug' => $programSlug, 'type' => $type]) }}"
                                          class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                                        @csrf
                                        <input type="hidden" name="placement_test_id" value="{{ $rec->id }}">

                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kelas</label>
                                            <select class="kelas-select-result w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors"
                                                    data-target="santri_id_result_{{ $rec->id }}">
                                                <option value="">Pilih Kelas</option>
                                                @foreach($kelasList as $kelas)
                                                    <option value="{{ $kelas->id }}" data-santri="{{ base64_encode(json_encode($kelas->santri->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values())) }}">
                                                        {{ $kelas->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Santri <span class="text-red-400">*</span></label>
                                            <select name="santri_id" id="santri_id_result_{{ $rec->id }}" required
                                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
                                                <option value="">Pilih Kelas dahulu</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Nilai</label>
                                            <input type="number" name="score" value="{{ old('score') }}" min="0" max="100"
                                                   placeholder="0–100"
                                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Catatan</label>
                                            <input type="text" name="notes" value="{{ old('notes') }}" maxlength="500"
                                                   placeholder="Catatan..."
                                                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
                                        </div>

                                        <div>
                                            <button type="submit" class="w-full px-4 py-2 {{ $accentBtn }} text-white rounded-lg text-sm font-semibold transition-colors">
                                                <i class="fa-solid fa-plus mr-1"></i> Simpan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                @endcan

                                {{-- Results list --}}
                                <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                                    @if($rec->results->isEmpty())
                                        <div class="text-center py-8 px-4">
                                            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada hasil untuk test ini.</p>
                                        </div>
                                    @else
                                        <table class="w-full">
                                            <thead>
                                                <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                                                    <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Santri</th>
                                                    <th class="px-3 py-2 text-center text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Nilai</th>
                                                    <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Level</th>
                                                    <th class="px-3 py-2 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Catatan</th>
                                                    <th class="px-3 py-2 text-right text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($rec->results as $res)
                                                <tr class="border-b border-gray-50 dark:border-gray-800/50 last:border-0">
                                                    {{-- View mode --}}
                                                    <td class="px-3 py-2" x-show="editId !== {{ $res->id }}" x-cloak>
                                                        <span class="text-[13px] text-gray-900 dark:text-gray-100">{{ $res->santri->name ?? '—' }}</span>
                                                    </td>
                                                    <td class="px-3 py-2 text-center" x-show="editId !== {{ $res->id }}" x-cloak>
                                                        <span class="text-[13px] font-bold text-gray-700 dark:text-gray-300">{{ $res->score ?? '—' }}</span>
                                                    </td>
                                                    <td class="px-3 py-2" x-show="editId !== {{ $res->id }}" x-cloak>
                                                        <span class="text-[11px] font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">
                                                            {{ $res->level?->label ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td class="px-3 py-2" x-show="editId !== {{ $res->id }}" x-cloak>
                                                        <span class="text-[12px] text-gray-400 dark:text-gray-500 italic">{{ $res->notes ? Str::limit($res->notes, 35) : '—' }}</span>
                                                    </td>
                                                    <td class="px-3 py-2" x-show="editId !== {{ $res->id }}" x-cloak>
                                                        <div class="flex items-center justify-end gap-1">
                                                            @can('update', [$res, $program])
                                                                <button type="button" @click="editId = {{ $res->id }}"
                                                                        class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors"
                                                                        title="Edit">
                                                                    <i class="fa-solid fa-pen text-[11px]"></i>
                                                                </button>
                                                            @endcan
                                                            @can('delete', [$res, $program])
                                                                <form method="POST"
                                                                      action="{{ tenant_route('dashboard.modern.' . $featureSlug . '.results.destroy', ['programSlug' => $programSlug, 'id' => $res->id, 'type' => $type]) }}"
                                                                      onsubmit="return confirm('Hapus hasil ini?')" class="inline">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit"
                                                                            class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-md transition-colors"
                                                                            title="Hapus">
                                                                        <i class="fa-solid fa-trash text-[11px]"></i>
                                                                    </button>
                                                                </form>
                                                            @endcan
                                                        </div>
                                                    </td>

                                                    {{-- Edit mode --}}
                                                    @can('update', [$res, $program])
                                                    <td class="px-3 py-2" colspan="5" x-show="editId === {{ $res->id }}" x-cloak>
                                                        <form method="POST"
                                                              action="{{ tenant_route('dashboard.modern.' . $featureSlug . '.results.update', ['programSlug' => $programSlug, 'id' => $res->id, 'type' => $type]) }}"
                                                              class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                                                            @csrf @method('PUT')
                                                            <div>
                                                                <span class="text-[13px] text-gray-900 dark:text-gray-100 block py-2">{{ $res->santri->name ?? '—' }}</span>
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-medium text-gray-500 dark:text-gray-400 mb-1">Nilai</label>
                                                                <input type="number" name="score" value="{{ $res->score }}" min="0" max="100"
                                                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-medium text-gray-500 dark:text-gray-400 mb-1">Level</label>
                                                                <span class="text-[11px] font-medium text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-full inline-block">
                                                                    {{ $res->level?->label ?? '—' }}
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <label class="block text-[10px] font-medium text-gray-500 dark:text-gray-400 mb-1">Catatan</label>
                                                                <input type="text" name="notes" value="{{ $res->notes }}" maxlength="500"
                                                                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
                                                            </div>
                                                            <div class="flex items-center gap-1">
                                                                <button type="submit" class="px-3 py-2 {{ $accentBtn }} text-white rounded-lg text-xs font-semibold transition-colors">
                                                                    <i class="fa-solid fa-check mr-1"></i> Simpan
                                                                </button>
                                                                <button type="button" @click="editId = null"
                                                                        class="px-3 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-semibold transition-colors">
                                                                    Batal
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </td>
                                                    @endcan
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<script>
    // Kelas → Santri filter for the "Tambah Hasil" forms. There is one such
    // form per placement test row, so we use event delegation + a
    // data-target id rather than a single hardcoded element id.
    (function () {
        document.addEventListener('change', function (e) {
            if (!e.target.classList.contains('kelas-select-result')) return;

            const santriSelect = document.getElementById(e.target.dataset.target);
            if (!santriSelect) return;

            santriSelect.innerHTML = '<option value="">Pilih Santri</option>';
            const selected = e.target.options[e.target.selectedIndex];
            const santriList = JSON.parse(atob(selected.dataset.santri || '') || '[]');
            santriList.forEach(function (s) {
                const option = document.createElement('option');
                option.value = s.id;
                option.textContent = s.name;
                santriSelect.appendChild(option);
            });
        });
    })();
</script>

@endsection
