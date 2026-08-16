@extends('layouts.tenant')

@section('title', 'Ajukan Perizinan')
@section('page-title', 'Ajukan Perizinan')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Perizinan</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Ajukan</span>
@endsection

@section('content')

@php
$user = auth()->user();

if ($user?->santri) {
    $santriList = collect([$user->santri]);
    $defaultSantriId = $user->santri->id;
} elseif ($user?->parent) {
    $santriList = $user->parent->santri()->orderBy('name')->get();
    $defaultSantriId = null;
} else {
    $santriList = collect();
    $defaultSantriId = null;
}
@endphp

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Ajukan Izin Keluar/Pulang</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
        Isi formulir pengajuan izin dengan lengkap
    </p>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.perizinan.store') }}" class="p-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Santri --}}
            <div class="md:col-span-2">
                <label for="santri_id" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Santri <span class="text-red-500">*</span>
                </label>
                <select id="santri_id" name="santri_id" required
                        @if($user?->santri) disabled @endif
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('santri_id') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    @if(!$user?->santri)
                        <option value="">Pilih Santri</option>
                    @endif
                    @forelse($santriList as $s)
                        <option value="{{ $s->id }}" {{ old('santri_id', $defaultSantriId) == $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->nis ?? 'NIS tidak tersedia' }})
                        </option>
                    @empty
                        <option value="" disabled>Tidak ada santri yang tersedia</option>
                    @endforelse
                </select>
                @if($user?->santri)
                    <input type="hidden" name="santri_id" value="{{ $user->santri->id }}">
                @endif
                @error('santri_id')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

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
                    <option value="">Pilih Jenis</option>
                    <option value="{{ \App\Models\Perizinan::JENIS_PULANG }}" {{ old('jenis') === \App\Models\Perizinan::JENIS_PULANG ? 'selected' : '' }}>Pulang ke Rumah</option>
                    <option value="{{ \App\Models\Perizinan::JENIS_KELUAR }}" {{ old('jenis') === \App\Models\Perizinan::JENIS_KELUAR ? 'selected' : '' }}>Keluar (non-pulang)</option>
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
                <input type="text" id="alasan" name="alasan" value="{{ old('alasan') }}" required
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
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai', now()->format('Y-m-d')) }}" required
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
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('tanggal_selesai') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('tanggal_selesai')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Kosongkan jika hanya 1 hari</p>
            </div>

            {{-- Destinasi --}}
            <div>
                <label for="destinasi" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Destinasi
                </label>
                <input type="text" id="destinasi" name="destinasi" value="{{ old('destinasi') }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors"
                       placeholder="Contoh: Rumah, Rumah Sakit, Acara keluarga">
            </div>

            {{-- Penjemput --}}
            <div>
                <label for="penjemput" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Penjemput
                </label>
                <input type="text" id="penjemput" name="penjemput" value="{{ old('penjemput') }}"
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
                <input type="text" id="no_hp_penjemput" name="no_hp_penjemput" value="{{ old('no_hp_penjemput') }}"
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
                                 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors"
                          placeholder="Informasi tambahan yang perlu diketahui...">{{ old('keterangan') }}</textarea>
            </div>
        </div>

        {{-- Info Box --}}
        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                <div>
                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">Informasi Pengajuan</h4>
                    <p class="text-[13px] text-blue-700 dark:text-blue-300 mt-1">
                        Pengajuan izin akan diproses oleh pengurus/ustadz. Status pengajuan dapat dilihat di halaman detail.
                        Pastikan data yang dimasukkan sudah benar sebelum mengajukan.
                    </p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.index') }}"
               class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                      hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="px-4 py-2 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors">
                <i class="fa-solid fa-paper-plane mr-1.5"></i>
                Ajukan Izin
            </button>
        </div>
    </form>
</div>

@endsection
