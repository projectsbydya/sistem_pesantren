@extends('layouts.tenant')

@php
    $isArabic  = $type === 'arabic';
    $typeLabel = \App\Models\Vocabulary::getLabels()[$type] ?? 'Kosakata';
    $icon      = $isArabic ? 'fa-language' : 'fa-spell-check';
    $langCode  = $isArabic ? 'ar' : 'en';
    $accentRing = $isArabic ? 'focus:ring-emerald-500/20 focus:border-emerald-500' : 'focus:ring-blue-500/20 focus:border-blue-500';
    $accentBtn  = $isArabic ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-blue-600 hover:bg-blue-700';
    $accentIcon = $isArabic ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400';
    $wordPh     = $isArabic ? 'كِتَابٌ' : 'knowledge';
    $transPh    = $isArabic ? 'Buku' : 'Pengetahuan';
    $exPh       = $isArabic ? 'هَذَا كِتَابٌ كَبِيرٌ' : 'Knowledge is the key to success.';
    $catPh      = $isArabic ? 'Isim, Kata Benda...' : 'Noun, Verb...';
@endphp

@section('title', $typeLabel . ' — ' . $program->name)
@section('page-title', $typeLabel)
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">{{ $program->name }}</span>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">{{ $typeLabel }}</span>
@endsection

@section('content')

@php
    $total  = $records->count();
    $hafal  = $records->where('status', 'hafal')->count();
    $proses = $records->where('status', 'proses')->count();
    $belum  = $records->where('status', 'belum')->count();
    $pct    = $total > 0 ? round($hafal / $total * 100) : 0;
@endphp

{{-- Type switcher — driven by Vocabulary::getLabels(), no hardcoded strings --}}
@if(!empty($viewMeta['typeSource']))
<div class="flex gap-2 mb-5">
    @foreach(\App\Models\Vocabulary::getLabels() as $t => $label)
        @if(in_array($t, $viewMeta['typeSource']))
        <a href="{{ tenant_route("dashboard.modern.{$featureSlug}.index", ['programSlug' => $programSlug, 'type' => $t]) }}"
           class="px-3 py-1.5 text-xs font-semibold rounded-lg border transition-colors
                  {{ $type === $t ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-emerald-400' }}">
            {{ $label }}
        </a>
        @endif
    @endforeach
</div>
@endif

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="{{ $typeLabel }}" value="{{ $total }}"  icon="{{ $icon }}"        color="{{ $isArabic ? 'emerald' : 'blue' }}" />
    <x-stat-card title="Sudah Hafal"      value="{{ $hafal }}"  icon="fa-circle-check"    color="emerald" />
    <x-stat-card title="Sedang Dihafal"   value="{{ $proses }}" icon="fa-rotate"          color="amber"   />
    <x-stat-card title="Belum Hafal"      value="{{ $belum }}"  icon="fa-hourglass-half"  color="rose"    />
</div>

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg {{ $accentIcon }} flex items-center justify-center shrink-0">
            <i class="fa-solid {{ $icon }} text-sm"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $typeLabel }}</h1>
            <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
                {{ $program->name }} — {{ $isArabic ? 'pengelolaan mufrodat bahasa Arab' : 'pengelolaan vocabulary bahasa Inggris' }}
            </p>
        </div>
    </div>
    @if($total > 0)
    <div class="shrink-0 min-w-[140px]">
        <div class="flex items-center justify-between mb-1">
            <span class="text-[11px] text-gray-400 dark:text-gray-500">Progress hafal</span>
            <span class="text-[11px] font-bold {{ $pct >= 80 ? 'text-emerald-600 dark:text-emerald-400' : ($pct >= 40 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-500 dark:text-rose-400') }}">{{ $pct }}%</span>
        </div>
        <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5">
            <div class="h-1.5 rounded-full transition-all duration-500 {{ $pct >= 80 ? 'bg-emerald-500' : ($pct >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $pct }}%"></div>
        </div>
    </div>
    @endif
</div>

{{-- Flash --}}
@if(session('success'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-sm">
        <i class="fa-solid fa-circle-check shrink-0"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@can('create', App\Models\Vocabulary::class)
{{-- Add Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 mb-6">
    <div class="flex items-center gap-2 mb-4">
        <i class="fa-solid fa-plus-circle text-sm {{ $isArabic ? 'text-emerald-500' : 'text-blue-500' }}"></i>
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tambah {{ $isArabic ? 'Mufrodat' : 'Vocabulary' }}</h3>
    </div>
    <form method="POST"
          action="{{ tenant_route('dashboard.modern.' . $featureSlug . '.store', ['programSlug' => $programSlug, 'type' => $type]) }}"
          class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3 items-end">
        @csrf
        <input type="hidden" name="language" value="{{ $langCode }}">

        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kelas</label>
            <select id="kelas_id"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
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
            <select name="santri_id" id="santri_id" required
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors @error('santri_id') border-red-300 @enderror">
                <option value="">Pilih Kelas dahulu</option>
            </select>
            @error('santri_id') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                {{ $isArabic ? 'Mufrodat' : 'Word' }} <span class="text-red-400">*</span>
            </label>
            <input type="text" name="word" value="{{ old('word') }}" required maxlength="255"
                   placeholder="{{ $wordPh }}"
                   dir="{{ $isArabic ? 'rtl' : 'ltr' }}"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors {{ $isArabic ? 'text-right text-base' : '' }} @error('word') border-red-300 @enderror">
            @error('word') <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Arti</label>
            <input type="text" name="translation" value="{{ old('translation') }}" maxlength="255"
                   placeholder="{{ $transPh }}"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kategori</label>
            <input type="text" name="category" value="{{ old('category') }}" maxlength="100"
                   placeholder="{{ $catPh }}"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status <span class="text-red-400">*</span></label>
            <select name="status" required
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
                @foreach(\App\Models\Vocabulary::getStatuses() as $st)
                    <option value="{{ $st }}" {{ old('status', 'belum') === $st ? 'selected' : '' }}>
                        {{ \App\Models\Vocabulary::getStatusLabels()[$st] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <button type="submit" class="w-full px-4 py-2 {{ $accentBtn }} text-white rounded-lg text-sm font-semibold transition-colors">
                <i class="fa-solid fa-plus mr-1"></i> Simpan
            </button>
        </div>

        <div class="md:col-span-3 lg:col-span-6">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Contoh Kalimat</label>
            <input type="text" name="example_sentence" value="{{ old('example_sentence') }}"
                   placeholder="{{ $exPh }}"
                   dir="{{ $isArabic ? 'rtl' : 'ltr' }}"
                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $isArabic ? 'text-right' : '' }} {{ $accentRing }} transition-colors">
        </div>
    </form>
</div>
@endcan

{{-- Filter by Kelas — pilih kelas dahulu sebelum menampilkan daftar santri --}}
<form method="GET" action="{{ tenant_route('dashboard.modern.' . $featureSlug . '.index', ['programSlug' => $programSlug]) }}"
      class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 mb-6 flex flex-col sm:flex-row items-end gap-3">
    <input type="hidden" name="type" value="{{ $type }}">
    <div class="flex-1 sm:max-w-xs">
        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kelas</label>
        <select name="kelas_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm {{ $accentRing }} transition-colors">
            <option value="">Semua Kelas</option>
            @foreach($kelasList as $kelas)
                <option value="{{ $kelas->id }}" {{ (int) $kelasId === $kelas->id ? 'selected' : '' }}>{{ $kelas->name }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-semibold hover:bg-gray-800 transition-colors">
        <i class="fa-solid fa-filter mr-1"></i> Filter
    </button>
    @if($kelasId)
        <a href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.index', ['programSlug' => $programSlug, 'type' => $type]) }}"
           class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
            Reset
        </a>
    @endif
</form>

{{-- Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    @if($records->isEmpty())
        <div class="text-center py-16 px-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full {{ $accentIcon }} flex items-center justify-center">
                <i class="fa-solid {{ $icon }} text-2xl"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">
                Belum ada {{ $isArabic ? 'mufrodat' : 'vocabulary' }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                {{ $isArabic ? 'Tambahkan mufrodat pertama menggunakan form di atas.' : 'Add the first vocabulary entry using the form above.' }}
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Santri</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <span class="flex items-center gap-1.5"><i class="fa-solid {{ $icon }} text-[10px]"></i>{{ $isArabic ? 'Mufrodat' : 'Word' }}</span>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Arti</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Contoh Kalimat</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nilai</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($records as $rec)
                    @php
                        $statusLabel   = \App\Models\Vocabulary::getStatusLabels()[$rec->status] ?? $rec->status;
                        $statusVariant = match($rec->status) { 'hafal' => 'success', 'proses' => 'warning', default => 'default' };
                        $rowHighlight  = match($rec->status) { 'hafal' => 'bg-emerald-50/30 dark:bg-emerald-500/5', 'proses' => 'bg-amber-50/30 dark:bg-amber-500/5', default => '' };
                        $scoreColor    = $rec->score !== null ? ($rec->score >= 80 ? 'text-emerald-600 dark:text-emerald-400' : ($rec->score >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-500 dark:text-rose-400')) : 'text-gray-300 dark:text-gray-600';
                    @endphp
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/60 dark:hover:bg-gray-800/30 transition-colors group {{ $rowHighlight }}">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full {{ $accentIcon }} flex items-center justify-center text-[12px] font-bold shrink-0">
                                    {{ strtoupper(substr($rec->santri->name ?? 'S', 0, 1)) }}
                                </div>
                                <a href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.show', ['programSlug' => $programSlug, 'id' => $rec->santri_id, 'type' => $type]) }}"
                                   class="text-[13px] font-medium text-gray-900 dark:text-gray-100 hover:text-emerald-600 dark:hover:text-emerald-400 truncate block">
                                    {{ $rec->santri->name ?? '—' }}
                                </a>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-semibold text-gray-900 dark:text-gray-100 {{ $isArabic ? 'text-base leading-relaxed' : 'text-[13px]' }}"
                                  dir="{{ $isArabic ? 'rtl' : 'ltr' }}">{{ $rec->word }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[13px] text-gray-600 dark:text-gray-400">{{ $rec->translation ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($rec->category)
                                <span class="text-[11px] text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded-full">{{ $rec->category }}</span>
                            @else
                                <span class="text-gray-300 dark:text-gray-600 text-[12px]">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 max-w-[180px]">
                            <span class="text-[12px] text-gray-400 dark:text-gray-500 italic truncate block"
                                  dir="{{ $isArabic ? 'rtl' : 'ltr' }}" title="{{ $rec->example_sentence }}">
                                {{ $rec->example_sentence ? Str::limit($rec->example_sentence, 45) : '—' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-[13px] font-bold {{ $scoreColor }}">{{ $rec->score !== null ? $rec->score : '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $statusVariant }}" size="sm" dot>{{ $statusLabel }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.show', ['programSlug' => $programSlug, 'id' => $rec->santri_id, 'type' => $type]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                   title="Lihat kosakata santri">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                </a>
                                @can('update', $rec)
                                    <a href="{{ tenant_route('dashboard.modern.' . $featureSlug . '.edit', ['programSlug' => $programSlug, 'id' => $rec->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors"
                                       title="Edit">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </a>
                                @endcan
                                @can('delete', $rec)
                                    <form method="POST"
                                          action="{{ tenant_route('dashboard.modern.' . $featureSlug . '.destroy', ['programSlug' => $programSlug, 'id' => $rec->id]) }}"
                                          onsubmit="return confirm('Hapus kosakata ini?')" class="inline">
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
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<script>
    (function () {
        const kelasSelect = document.getElementById('kelas_id');
        const santriSelect = document.getElementById('santri_id');

        kelasSelect.addEventListener('change', function () {
            santriSelect.innerHTML = '<option value="">Pilih Santri</option>';
            const selected = this.options[this.selectedIndex];
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
