@extends('layouts.tenant')

@section('title', 'Tambah Penilaian Karakter')
@section('page-title', 'Tambah Penilaian')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Monitoring</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Tambah</span>
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
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Tambah Penilaian Karakter</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
        Nilai aspek karakter santri
    </p>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.store') }}" class="p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Santri --}}
            <div class="md:col-span-2">
                <label for="santri_id" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Santri <span class="text-red-500">*</span>
                </label>
                <select id="santri_id" name="santri_id" required
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('santri_id') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    <option value="">Pilih Santri</option>
                    @foreach($santri as $s)
                        <option value="{{ $s->id }}" {{ old('santri_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->nis ?? 'NIS tidak tersedia' }})
                        </option>
                    @endforeach
                </select>
                @error('santri_id')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

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
                    <option value="">Pilih Aspek</option>
                    @foreach($aspekLabels as $key => $label)
                        <option value="{{ $key }}" {{ old('aspek') === $key ? 'selected' : '' }}>{{ $label }}</option>
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
                <input type="number" id="skor" name="skor" value="{{ old('skor', 70) }}" required min="1" max="100"
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
                <input type="text" id="predikat" name="predikat" value="{{ old('predikat') }}" readonly
                       class="w-full px-3 py-2 text-[13px] bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-600 dark:text-gray-400 cursor-not-allowed">
                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Otomatis dihitung dari skor</p>
            </div>

            {{-- Tanggal --}}
            <div>
                <label for="tanggal" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Tanggal <span class="text-red-500">*</span>
                </label>
                <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required
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
                    <option value="mingguan" {{ old('periode') === 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                    <option value="bulanan" {{ old('periode') === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                    <option value="semester" {{ old('periode') === 'semester' ? 'selected' : '' }}>Semester</option>
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
                                 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors"
                          placeholder="Deskripsi penilaian, catatan perkembangan, atau evidence...">{{ old('deskripsi') }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.index') }}"
               class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                      hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="px-4 py-2 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                <i class="fa-solid fa-save mr-1.5"></i>
                Simpan
            </button>
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
// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    const skorInput = document.getElementById('skor');
    if (skorInput) updatePredikat(skorInput.value);
});
</script>

@endsection
