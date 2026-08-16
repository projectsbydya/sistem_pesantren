@extends('layouts.tenant')

@section('title', 'Pindahkan Kamar')
@section('page-title', 'Pindahkan Kamar')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.penempatan.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Penempatan</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Pindahkan Kamar</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Pindahkan Kamar Santri</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
        Pindahkan santri dari kamar saat ini ke kamar tujuan
    </p>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.penempatan.move') }}" class="p-6">
        @csrf

        @if($santri->isEmpty())
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-medium text-amber-900 dark:text-amber-100">Tidak ada santri yang dapat dipindahkan</h4>
                        <p class="text-[13px] text-amber-700 dark:text-amber-300 mt-1">
                            Belum ada santri yang memiliki penempatan kamar saat ini.
                        </p>
                    </div>
                </div>
            </div>
        @elseif($kamar->isEmpty())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-xmark text-red-500 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-medium text-red-900 dark:text-red-100">Tidak ada kamar tujuan tersedia</h4>
                        <p class="text-[13px] text-red-700 dark:text-red-300 mt-1">
                            Semua kamar sudah penuh. Tambah kamar baru atau kosongkan kamar yang ada.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Santri --}}
            <div>
                <label for="santri_id" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Santri <span class="text-red-500">*</span>
                </label>
                <select id="santri_id" name="santri_id" required
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('santri_id') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                        {{ $santri->isEmpty() ? 'disabled' : '' }}>
                    <option value="">Pilih Santri</option>
                    @foreach($santri as $s)
                        <option value="{{ $s->id }}" {{ old('santri_id') == $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->nis ?? 'NIS tidak tersedia' }}) - {{ $s->kamar->name ?? 'Belum punya kamar' }}
                        </option>
                    @endforeach
                </select>
                @error('santri_id')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kamar Tujuan --}}
            <div>
                <label for="kamar_tujuan_id" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Kamar Tujuan <span class="text-red-500">*</span>
                </label>
                <select id="kamar_tujuan_id" name="kamar_tujuan_id" required
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('kamar_tujuan_id') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                        {{ $kamar->isEmpty() ? 'disabled' : '' }}>
                    <option value="">Pilih Kamar Tujuan</option>
                    @foreach($kamar as $k)
                        <option value="{{ $k->id }}" {{ old('kamar_tujuan_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->name }} (Sisa: {{ $k->sisa_kapasitas }})
                        </option>
                    @endforeach
                </select>
                @error('kamar_tujuan_id')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Alasan --}}
            <div class="md:col-span-2">
                <label for="alasan" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Alasan Mutasi <span class="text-red-500">*</span>
                </label>
                <select id="alasan" name="alasan" required
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('alasan') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    <option value="">Pilih Alasan</option>
                    <option value="Perpindahan Kelas" {{ old('alasan') === 'Perpindahan Kelas' ? 'selected' : '' }}>Perpindahan Kelas</option>
                    <option value="Permintaan Santri" {{ old('alasan') === 'Permintaan Santri' ? 'selected' : '' }}>Permintaan Santri</option>
                    <option value="Alasan Kesehatan" {{ old('alasan') === 'Alasan Kesehatan' ? 'selected' : '' }}>Alasan Kesehatan</option>
                    <option value="Masalah dengan Penghuni" {{ old('alasan') === 'Masalah dengan Penghuni' ? 'selected' : '' }}>Masalah dengan Penghuni</option>
                    <option value="Penyesuaian Kapasitas" {{ old('alasan') === 'Penyesuaian Kapasitas' ? 'selected' : '' }}>Penyesuaian Kapasitas</option>
                    <option value="Lainnya" {{ old('alasan') === 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
                @error('alasan')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Keterangan --}}
            <div class="md:col-span-2">
                <label for="keterangan" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Keterangan
                </label>
                <textarea id="keterangan" name="keterangan" rows="3"
                          class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                                 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                                 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors"
                          placeholder="Keterangan tambahan tentang alasan mutasi...">{{ old('keterangan') }}</textarea>
                @error('keterangan')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Info Box --}}
        <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                <div>
                    <h4 class="text-sm font-medium text-blue-900 dark:text-blue-100">Informasi Mutasi</h4>
                    <p class="text-[13px] text-blue-700 dark:text-blue-300 mt-1">
                        Mutasi akan mencatat perpindahan santri dari kamar saat ini ke kamar tujuan.
                        Data ini akan tersimpan sebagai audit log dan tidak dapat diubah.
                    </p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ tenant_route('dashboard.kepesantrenan.penempatan.index') }}"
               class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                      hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                Batal
            </a>
            <button type="submit"
                    class="px-4 py-2 text-[13px] font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors"
                    {{ ($santri->isEmpty() || $kamar->isEmpty()) ? 'disabled' : '' }}>
                <i class="fa-solid fa-people-arrows mr-1.5"></i>
                Pindahkan
            </button>
        </div>
    </form>
</div>

@endsection
