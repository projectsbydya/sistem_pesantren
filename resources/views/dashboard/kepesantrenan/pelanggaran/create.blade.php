@extends('layouts.tenant')

@section('title', 'Tambah Pelanggaran')
@section('page-title', 'Tambah Pelanggaran')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Pelanggaran</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Tambah</span>
@endsection

@section('content')

@php
$santriList = \App\Models\Santri::where('status', 'active')->orderBy('name')->get();
@endphp

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Tambah Pelanggaran</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
        Catat pelanggaran santri
    </p>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.store') }}" class="p-6">
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
                    @foreach($santriList as $s)
                        <option value="{{ $s->id }}" {{ old('santri_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->nis ?? 'NIS tidak tersedia' }})
                        </option>
                    @endforeach
                </select>
                @error('santri_id')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Jenis --}}
            <div>
                <label for="jenis" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Jenis Pelanggaran <span class="text-red-500">*</span>
                </label>
                <select id="jenis" name="jenis" required
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('jenis') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    <option value="">Pilih Jenis</option>
                    <option value="ringan" {{ old('jenis') === 'ringan' ? 'selected' : '' }}>Ringan</option>
                    <option value="sedang" {{ old('jenis') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="berat" {{ old('jenis') === 'berat' ? 'selected' : '' }}>Berat</option>
                </select>
                @error('jenis')
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
                    <option value="">Pilih Kategori</option>
                    <option value="Akademik" {{ old('kategori') === 'Akademik' ? 'selected' : '' }}>Akademik</option>
                    <option value="Disiplin" {{ old('kategori') === 'Disiplin' ? 'selected' : '' }}>Disiplin</option>
                    <option value="Kebersihan" {{ old('kategori') === 'Kebersihan' ? 'selected' : '' }}>Kebersihan</option>
                    <option value="Kerapian" {{ old('kategori') === 'Kerapian' ? 'selected' : '' }}>Kerapian</option>
                    <option value="Keamanan" {{ old('kategori') === 'Keamanan' ? 'selected' : '' }}>Keamanan</option>
                    <option value="Lainnya" {{ old('kategori') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
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
                <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('tanggal') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('tanggal')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Lokasi --}}
            <div>
                <label for="lokasi" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Lokasi Kejadian
                </label>
                <input type="text" id="lokasi" name="lokasi" value="{{ old('lokasi') }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors"
                       placeholder="Contoh: Kelas, Asrama, Kantin">
            </div>

            {{-- Deskripsi --}}
            <div class="md:col-span-2">
                <label for="deskripsi" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Deskripsi <span class="text-red-500">*</span>
                </label>
                <textarea id="deskripsi" name="deskripsi" rows="4" required
                          class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                                 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                                 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                                 @error('deskripsi') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                          placeholder="Jelaskan detail pelanggaran yang terjadi...">{{ old('deskripsi') }}</textarea>
                @error('deskripsi')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.index') }}"
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
