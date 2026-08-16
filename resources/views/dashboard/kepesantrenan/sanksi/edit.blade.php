@extends('layouts.tenant')

@section('title', 'Edit Sanksi')
@section('page-title', 'Edit Sanksi')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.sanksi.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Sanksi</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Edit</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Edit Sanksi</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
        Perbarui data sanksi
    </p>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.sanksi.update', ['sanksi' => $sanksi->id]) }}" class="p-6">
        @csrf
        @method('PUT')

        {{-- Santri Info (read-only) --}}
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[14px] font-bold">
                    {{ strtoupper(substr($sanksi->pelanggaran->santri->name ?? 'S', 0, 1)) }}
                </div>
                <div>
                    <p class="text-[14px] font-medium text-gray-900 dark:text-gray-100">{{ $sanksi->pelanggaran->santri->name ?? 'Unknown' }}</p>
                    <p class="text-[12px] text-gray-500 dark:text-gray-400 font-mono">{{ $sanksi->pelanggaran->santri->nis ?? '-' }}</p>
                </div>
                <a href="{{ tenant_route('dashboard.kepesantrenan.pelanggaran.show', ['pelanggaran' => $sanksi->pelanggaran_id]) }}"
                   class="ml-auto px-3 py-1.5 text-[12px] text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-500/20 transition-colors">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    Lihat Pelanggaran
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Jenis --}}
            <div>
                <label for="jenis" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Jenis Sanksi <span class="text-red-500">*</span>
                </label>
                <select id="jenis" name="jenis" required
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('jenis') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    <option value="peringatan" {{ old('jenis', $sanksi->jenis) === 'peringatan' ? 'selected' : '' }}>Peringatan</option>
                    <option value="tugas" {{ old('jenis', $sanksi->jenis) === 'tugas' ? 'selected' : '' }}>Tugas Khusus</option>
                    <option value="skorsing" {{ old('jenis', $sanksi->jenis) === 'skorsing' ? 'selected' : '' }}>Skorsing</option>
                    <option value="dikembalikan" {{ old('jenis', $sanksi->jenis) === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan ke Orang Tua</option>
                </select>
                @error('jenis')
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
                    <option value="aktif" {{ old('status', $sanksi->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="selesai" {{ old('status', $sanksi->status) === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="dibatalkan" {{ old('status', $sanksi->status) === 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
                @error('status')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Mulai --}}
            <div>
                <label for="tanggal_mulai" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Tanggal Mulai <span class="text-red-500">*</span>
                </label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $sanksi->tanggal_mulai?->format('Y-m-d')) }}" required
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('tanggal_mulai') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('tanggal_mulai')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Selesai --}}
            <div>
                <label for="tanggal_selesai" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Tanggal Selesai
                </label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $sanksi->tanggal_selesai?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('tanggal_selesai') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('tanggal_selesai')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="md:col-span-2">
                <label for="deskripsi" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Deskripsi Sanksi <span class="text-red-500">*</span>
                </label>
                <textarea id="deskripsi" name="deskripsi" rows="4" required
                          class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                                 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                                 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                                 @error('deskripsi') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">{{ old('deskripsi', $sanksi->deskripsi) }}</textarea>
                @error('deskripsi')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Hasil Evaluasi --}}
            <div class="md:col-span-2">
                <label for="hasil_evaluasi" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Hasil Evaluasi
                </label>
                <textarea id="hasil_evaluasi" name="hasil_evaluasi" rows="3"
                          class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                                 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                                 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                                 @error('hasil_evaluasi') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                          placeholder="Hasil evaluasi setelah sanksi selesai...">{{ old('hasil_evaluasi', $sanksi->hasil_evaluasi) }}</textarea>
                @error('hasil_evaluasi')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            @can('delete', $sanksi)
                <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.sanksi.destroy', ['sanksi' => $sanksi->id]) }}"
                      onsubmit="return confirm('Yakin ingin menghapus sanksi ini?')" class="inline">
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
                <a href="{{ tenant_route('dashboard.kepesantrenan.sanksi.show', ['sanksi' => $sanksi->id]) }}"
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
