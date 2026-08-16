@extends('layouts.tenant')

@section('title', 'Tambah Kamar')
@section('page-title', 'Tambah Kamar')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.kamar.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Kamar</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Tambah</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Tambah Kamar Baru</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
        Isi data kamar dengan lengkap
    </p>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.kamar.store') }}" class="p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Nama Kamar --}}
            <div class="md:col-span-2">
                <label for="name" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Nama Kamar <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('name') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                       placeholder="Contoh: Kamar A1">
                @error('name')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Lokasi --}}
            <div>
                <label for="lokasi" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Lokasi
                </label>
                <input type="text" id="lokasi" name="lokasi" value="{{ old('lokasi') }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('lokasi') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                       placeholder="Contoh: Lantai 1, Gedung Barat">
                @error('lokasi')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kapasitas --}}
            <div>
                <label for="kapasitas" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Kapasitas <span class="text-red-500">*</span>
                </label>
                <input type="number" id="kapasitas" name="kapasitas" value="{{ old('kapasitas', 4) }}" required min="1" max="50"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('kapasitas') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                       placeholder="Jumlah santri">
                @error('kapasitas')
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
                    <option value="aktif" {{ old('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ old('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                @error('status')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Fasilitas --}}
            <div>
                <label for="fasilitas" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Fasilitas
                </label>
                <input type="text" id="fasilitas" name="fasilitas" value="{{ old('fasilitas') }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors"
                       placeholder="Contoh: AC, Lemari, Meja Belajar">
            </div>

            {{-- Deskripsi --}}
            <div class="md:col-span-2">
                <label for="description" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Deskripsi
                </label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                                 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                                 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                                 @error('description') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                          placeholder="Deskripsi tambahan tentang kamar...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ tenant_route('dashboard.kepesantrenan.kamar.index') }}"
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

@endsection
