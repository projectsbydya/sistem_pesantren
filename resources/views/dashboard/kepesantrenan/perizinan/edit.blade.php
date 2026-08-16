@extends('layouts.tenant')

@section('title', 'Edit Perizinan')
@section('page-title', 'Edit Perizinan')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Perizinan</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Edit</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Edit Pengajuan Izin</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
        Perbarui data pengajuan izin
    </p>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.perizinan.update', ['perizinan' => $perizinan->id]) }}" class="p-6">
        @csrf
        @method('PUT')

        {{-- Santri Info (read-only) --}}
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[14px] font-bold">
                    {{ strtoupper(substr($perizinan->santri->name ?? 'S', 0, 1)) }}
                </div>
                <div>
                    <p class="text-[14px] font-medium text-gray-900 dark:text-gray-100">{{ $perizinan->santri->name ?? 'Unknown' }}</p>
                    <p class="text-[12px] text-gray-500 dark:text-gray-400 font-mono">{{ $perizinan->santri->nis ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Jenis --}}
            <div>
                <label for="jenis" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Jenis Izin <span class="text-red-500">*</span>
                </label>
                <select id="jenis" name="jenis" required
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('jenis') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    <option value="{{ \App\Models\Perizinan::JENIS_PULANG }}" {{ old('jenis', $perizinan->jenis) === \App\Models\Perizinan::JENIS_PULANG ? 'selected' : '' }}>Pulang ke Rumah</option>
                    <option value="{{ \App\Models\Perizinan::JENIS_KELUAR }}" {{ old('jenis', $perizinan->jenis) === \App\Models\Perizinan::JENIS_KELUAR ? 'selected' : '' }}>Keluar (non-pulang)</option>
                </select>
                @error('jenis')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Alasan --}}
            <div>
                <label for="alasan" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Alasan <span class="text-red-500">*</span>
                </label>
                <input type="text" id="alasan" name="alasan" value="{{ old('alasan', $perizinan->alasan) }}" required
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('alasan') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                       placeholder="Contoh: Urusan keluarga, sakit, dll">
                @error('alasan')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Mulai --}}
            <div>
                <label for="tanggal_mulai" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Tanggal Mulai <span class="text-red-500">*</span>
                </label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', $perizinan->tanggal_mulai?->format('Y-m-d')) }}" required
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
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai', $perizinan->tanggal_selesai?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('tanggal_selesai') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('tanggal_selesai')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Destinasi --}}
            <div>
                <label for="destinasi" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Destinasi
                </label>
                <input type="text" id="destinasi" name="destinasi" value="{{ old('destinasi', $perizinan->destinasi) }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors"
                       placeholder="Contoh: Rumah, Rumah Sakit">
            </div>

            {{-- Penjemput --}}
            <div>
                <label for="penjemput" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Penjemput
                </label>
                <input type="text" id="penjemput" name="penjemput" value="{{ old('penjemput', $perizinan->penjemput) }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors"
                       placeholder="Nama penjemput">
            </div>

            {{-- No HP Penjemput --}}
            <div>
                <label for="no_hp_penjemput" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    No. HP Penjemput
                </label>
                <input type="text" id="no_hp_penjemput" name="no_hp_penjemput" value="{{ old('no_hp_penjemput', $perizinan->no_hp_penjemput) }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors"
                       placeholder="08xxxxxxxxxx">
            </div>

            {{-- Keterangan --}}
            <div class="md:col-span-2">
                <label for="keterangan" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Keterangan Tambahan
                </label>
                <textarea id="keterangan" name="keterangan" rows="3"
                          class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                                 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                                 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors">{{ old('keterangan', $perizinan->keterangan) }}</textarea>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            @can('delete', $perizinan)
                <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.perizinan.destroy', ['perizinan' => $perizinan->id]) }}"
                      onsubmit="return confirm('Yakin ingin menghapus pengajuan ini?')" class="inline">
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
                <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.show', ['perizinan' => $perizinan->id]) }}"
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
