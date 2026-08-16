@extends('layouts.tenant')

@section('title', 'Edit Pelanggaran')
@section('page-title', 'Edit Pelanggaran')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Pelanggaran</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Edit</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Edit Pelanggaran</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
        Perbarui data pelanggaran
    </p>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.update', ['pelanggaran' => $pelanggaran->id]) }}" class="p-6">
        @csrf
        @method('PUT')

        {{-- Santri Info (read-only) --}}
        <div class="md:col-span-2 mb-6">
            <label class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">Santri</label>
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <div class="w-10 h-10 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[14px] font-bold shrink-0">
                    {{ strtoupper(substr($pelanggaran->santri->name ?? 'S', 0, 1)) }}
                </div>
                <div>
                    <p class="text-[14px] font-medium text-gray-900 dark:text-gray-100">{{ $pelanggaran->santri->name ?? 'Unknown' }}</p>
                    <p class="text-[12px] text-gray-500 dark:text-gray-400 font-mono">{{ $pelanggaran->santri->nis ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                    <option value="ringan" {{ old('jenis', $pelanggaran->jenis) === 'ringan' ? 'selected' : '' }}>Ringan</option>
                    <option value="sedang" {{ old('jenis', $pelanggaran->jenis) === 'sedang' ? 'selected' : '' }}>Sedang</option>
                    <option value="berat" {{ old('jenis', $pelanggaran->jenis) === 'berat' ? 'selected' : '' }}>Berat</option>
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
                    <option value="Akademik" {{ old('kategori', $pelanggaran->kategori) === 'Akademik' ? 'selected' : '' }}>Akademik</option>
                    <option value="Disiplin" {{ old('kategori', $pelanggaran->kategori) === 'Disiplin' ? 'selected' : '' }}>Disiplin</option>
                    <option value="Kebersihan" {{ old('kategori', $pelanggaran->kategori) === 'Kebersihan' ? 'selected' : '' }}>Kebersihan</option>
                    <option value="Kerapian" {{ old('kategori', $pelanggaran->kategori) === 'Kerapian' ? 'selected' : '' }}>Kerapian</option>
                    <option value="Keamanan" {{ old('kategori', $pelanggaran->kategori) === 'Keamanan' ? 'selected' : '' }}>Keamanan</option>
                    <option value="Lainnya" {{ old('kategori', $pelanggaran->kategori) === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
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
                <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', $pelanggaran->tanggal?->format('Y-m-d')) }}" required
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
                <input type="text" id="lokasi" name="lokasi" value="{{ old('lokasi', $pelanggaran->lokasi) }}"
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
                                 @error('deskripsi') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">{{ old('deskripsi', $pelanggaran->deskripsi) }}</textarea>
                @error('deskripsi')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tindak Lanjut --}}
            <div class="md:col-span-2">
                <label for="tindak_lanjut" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Tindak Lanjut
                </label>
                <textarea id="tindak_lanjut" name="tindak_lanjut" rows="3"
                          class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                                 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                                 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                                 @error('tindak_lanjut') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                          placeholder="Tindak lanjut yang telah dilakukan...">{{ old('tindak_lanjut', $pelanggaran->tindak_lanjut) }}</textarea>
                @error('tindak_lanjut')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            @can('delete', $pelanggaran)
                <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.destroy', ['pelanggaran' => $pelanggaran->id]) }}"
                      onsubmit="return confirm('Yakin ingin menghapus pelanggaran ini? Data yang sudah dihapus tidak dapat dikembalikan.')" class="inline">
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
                <a href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.show', ['pelanggaran' => $pelanggaran->id]) }}"
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
