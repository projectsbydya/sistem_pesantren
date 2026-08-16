@extends('layouts.tenant')

@section('title', 'Edit Penilaian Karakter')
@section('page-title', 'Edit Penilaian')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Monitoring</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Edit</span>
@endsection

@section('content')

@php
$aspekLabels = [
    'akhlak' => 'Akhlak',
    'disiplin' => 'Disiplin',
    'tanggung_jawab' => 'Tanggung Jawab',
    'kerja_sama' => 'Kerja Sama',
    'kejujuran' => 'Kejujuran',
    'kemandirian' => 'Kemandirian',
];
@endphp

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Edit Penilaian Karakter</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
        Perbarui data penilaian
    </p>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.update', ['monitoring' => $monitoring->id]) }}" class="p-6">
        @csrf
        @method('PUT')

        {{-- Santri Info (read-only) --}}
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[14px] font-bold">
                    {{ strtoupper(substr($monitoring->santri->name ?? 'S', 0, 1)) }}
                </div>
                <div>
                    <p class="text-[14px] font-medium text-gray-900 dark:text-gray-100">{{ $monitoring->santri->name ?? 'Unknown' }}</p>
                    <p class="text-[12px] text-gray-500 dark:text-gray-400 font-mono">{{ $monitoring->santri->nis ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Aspek --}}
            <div>
                <label for="aspek" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Aspek Penilaian <span class="text-red-500">*</span>
                </label>
                <select id="aspek" name="aspek" required
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('aspek') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    @foreach($aspekLabels as $key => $label)
                        <option value="{{ $key }}" {{ old('aspek', $monitoring->aspek) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('aspek')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Skor --}}
            <div>
                <label for="skor" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Skor (1-100) <span class="text-red-500">*</span>
                </label>
                <input type="number" id="skor" name="skor" value="{{ old('skor', $monitoring->skor) }}" required min="1" max="100"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('skor') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                       oninput="updatePredikat(this.value)">
                @error('skor')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">85-100: Sangat Baik, 70-84: Baik, 60-69: Cukup, &lt;60: Kurang</p>
            </div>

            {{-- Predikat (auto-calculated) --}}
            <div>
                <label for="predikat" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Predikat
                </label>
                <input type="text" id="predikat" name="predikat" value="{{ old('predikat', $monitoring->predikat) }}" readonly
                       class="w-full px-3 py-2 text-[13px] bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-600 dark:text-gray-400 cursor-not-allowed">
                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Otomatis dihitung dari skor</p>
            </div>

            {{-- Tanggal --}}
            <div>
                <label for="tanggal" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Tanggal <span class="text-red-500">*</span>
                </label>
                <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', $monitoring->tanggal?->format('Y-m-d')) }}" required
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('tanggal') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('tanggal')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Periode --}}
            <div>
                <label for="periode" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Periode
                </label>
                <select id="periode" name="periode"
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors">
                    <option value="">Pilih Periode</option>
                    <option value="mingguan" {{ old('periode', $monitoring->periode) === 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                    <option value="bulanan" {{ old('periode', $monitoring->periode) === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="semester" {{ old('periode', $monitoring->periode) === 'semester' ? 'selected' : '' }}>Semester</option>
                </select>
            </div>

            {{-- Deskripsi --}}
            <div class="md:col-span-2">
                <label for="deskripsi" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Deskripsi / Catatan
                </label>
                <textarea id="deskripsi" name="deskripsi" rows="4"
                          class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                                 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                                 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors">{{ old('deskripsi', $monitoring->deskripsi) }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            @can('delete', $monitoring)
                <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.destroy', ['monitoring' => $monitoring->id]) }}"
                      onsubmit="return confirm('Yakin ingin menghapus penilaian ini?')" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="px-4 py-2 text-[13px] font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-lg
                                   hover:bg-red-100 dark:hover:bg-red-500/20 transition-colors">
                        <i class="fa-solid fa-trash mr-1.5"></i>
                        Hapus
                    </button>
                </form>
            @else
                <div></div>
            @endcan

            <div class="flex items-center gap-3">
                <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.show', ['monitoring' => $monitoring->id]) }}"
                   class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    Batal
                </a>
                <button type="submit"
                        class="px-4 py-2 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                    <i class="fa-solid fa-save mr-1.5"></i>
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function updatePredikat(skor) {
    const predikatInput = document.getElementById('predikat');
    let predikat = '';
    if (skor >= 85) predikat = 'sangat_baik';
    else if (skor >= 70) predikat = 'baik';
    else if (skor >= 60) predikat = 'cukup';
    else predikat = 'kurang';
    predikatInput.value = predikat;
}
</script>

@endsection
