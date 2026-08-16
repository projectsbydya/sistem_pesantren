@extends('layouts.tenant')

@section('title', 'Edit Kegiatan')
@section('page-title', 'Edit Kegiatan')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Kegiatan</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Edit</span>
@endsection

@section('content')

@php
$jenisLabels = [
    'sholat' => 'Sholat',
    'tilawah' => 'Tilawah',
    'dzikir' => 'Dzikir',
    'sholat_dhuha' => 'Sholat Dhuha',
    'sholat_tahajjud' => 'Sholat Tahajjud',
    'sholat_rawatib' => 'Sholat Rawatib',
    'murojaah' => 'Murojaah',
    'setoran' => 'Setoran',
    'kegiatan_pagi' => 'Kegiatan Pagi',
    'kegiatan_sore' => 'Kegiatan Sore',
    'kegiatan_malam' => 'Kegiatan Malam',
];
@endphp

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Edit Kegiatan Harian</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
        Perbarui data kegiatan santri
    </p>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.update', ['kegiatan' => $kegiatan->id]) }}" class="p-6">
        @csrf
        @method('PUT')

        {{-- Santri Info (read-only) --}}
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[14px] font-bold">
                    {{ strtoupper(substr($kegiatan->santri->name ?? 'S', 0, 1)) }}
                </div>
                <div>
                    <p class="text-[14px] font-medium text-gray-900 dark:text-gray-100">{{ $kegiatan->santri->name ?? 'Unknown' }}</p>
                    <p class="text-[12px] text-gray-500 dark:text-gray-400 font-mono">{{ $kegiatan->santri->nis ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Jenis Kegiatan --}}
            <div>
                <label for="jenis_kegiatan" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Jenis Kegiatan <span class="text-red-500">*</span>
                </label>
                <select id="jenis_kegiatan" name="jenis_kegiatan" required
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('jenis_kegiatan') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    @foreach($jenisLabels as $key => $label)
                        <option value="{{ $key }}" {{ old('jenis_kegiatan', $kegiatan->jenis_kegiatan) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('jenis_kegiatan')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kategori --}}
            <div>
                <label for="kategori" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <select id="kategori" name="kategori" required
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('kategori') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    <option value="wajib" {{ old('kategori', $kegiatan->kategori) === 'wajib' ? 'selected' : '' }}>Wajib</option>
                    <option value="sunnah" {{ old('kategori', $kegiatan->kategori) === 'sunnah' ? 'selected' : '' }}>Sunnah</option>
                    <option value="ekstra" {{ old('kategori', $kegiatan->kategori) === 'ekstra' ? 'selected' : '' }}>Ekstra</option>
                </select>
                @error('kategori')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal --}}
            <div>
                <label for="tanggal" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Tanggal <span class="text-red-500">*</span>
                </label>
                <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', $kegiatan->tanggal?->format('Y-m-d')) }}" required
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('tanggal') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('tanggal')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <label for="status" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Status <span class="text-red-500">*</span>
                </label>
                <select id="status" name="status" required
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('status') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    <option value="terjadwal" {{ old('status', $kegiatan->status) === 'terjadwal' ? 'selected' : '' }}>Terjadwal</option>
                    <option value="dilaksanakan" {{ old('status', $kegiatan->status) === 'dilaksanakan' ? 'selected' : '' }}>Dilaksanakan</option>
                    <option value="tidak_dilaksanakan" {{ old('status', $kegiatan->status) === 'tidak_dilaksanakan' ? 'selected' : '' }}>Tidak Dilaksanakan</option>
                </select>
                @error('status')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Waktu Mulai --}}
            <div>
                <label for="waktu_mulai" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Waktu Mulai
                </label>
                <input type="time" id="waktu_mulai" name="waktu_mulai" value="{{ old('waktu_mulai', $kegiatan->waktu_mulai) }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('waktu_mulai') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('waktu_mulai')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Waktu Selesai --}}
            <div>
                <label for="waktu_selesai" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Waktu Selesai
                </label>
                <input type="time" id="waktu_selesai" name="waktu_selesai" value="{{ old('waktu_selesai', $kegiatan->waktu_selesai) }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('waktu_selesai') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('waktu_selesai')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Catatan --}}
            <div class="md:col-span-2">
                <label for="catatan" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Catatan
                </label>
                <textarea id="catatan" name="catatan" rows="3"
                          class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                                 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                                 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors">{{ old('catatan', $kegiatan->catatan) }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            @can('delete', $kegiatan)
                <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.destroy', ['kegiatan' => $kegiatan->id]) }}"
                      onsubmit="return confirm('Yakin ingin menghapus kegiatan ini?')" class="inline">
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
                <a href="{{ tenant_route('dashboard.kepesantrenan.kegiatan-harian.show', ['kegiatan' => $kegiatan->id]) }}"
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

@endsection
