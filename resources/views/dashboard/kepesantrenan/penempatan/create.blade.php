@extends('layouts.tenant')

@section('title', 'Tambah Penempatan')
@section('page-title', 'Tambah Penempatan')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.penempatan.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Penempatan</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Tambah</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Tambah Penempatan Kamar</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
        Tempatkan santri di kamar yang tersedia
    </p>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.penempatan.store') }}" class="p-6">
        @csrf

        @if($santri->isEmpty())
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-exclamation-triangle text-amber-500 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-medium text-amber-900 dark:text-amber-100">Tidak ada santri yang perlu ditempatkan</h4>
                        <p class="text-[13px] text-amber-700 dark:text-amber-300 mt-1">
                            Semua santri aktif sudah memiliki kamar.
                        </p>
                    </div>
                </div>
            </div>
        @elseif($kamar->isEmpty())
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-circle-xmark text-red-500 mt-0.5"></i>
                    <div>
                        <h4 class="text-sm font-medium text-red-900 dark:text-red-100">Tidak ada kamar tersedia</h4>
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
                            {{ $s->name }} ({{ $s->nis ?? 'NIS tidak tersedia' }})
                        </option>
                    @endforeach
                </select>
                @error('santri_id')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
                @if($santri->isEmpty())
                    <p class="mt-1 text-[12px] text-amber-600 dark:text-amber-400">Semua santri sudah memiliki kamar</p>
                @endif
            </div>

            {{-- Kamar --}}
            <div>
                <label for="kamar_id" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Kamar <span class="text-red-500">*</span>
                </label>
                <select id="kamar_id" name="kamar_id" required
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('kamar_id') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                        {{ $kamar->isEmpty() ? 'disabled' : '' }}>
                    <option value="">Pilih Kamar</option>
                    @foreach($kamar as $k)
                        <option value="{{ $k->id }}" {{ old('kamar_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->name }} (Sisa: {{ $k->sisa_kapasitas }})
                        </option>
                    @endforeach
                </select>
                @error('kamar_id')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
                @if($kamar->isEmpty())
                    <p class="mt-1 text-[12px] text-red-600 dark:text-red-400">Semua kamar penuh</p>
                @endif
            </div>

            {{-- Tanggal Masuk --}}
            <div>
                <label for="tanggal_masuk" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Tanggal Masuk <span class="text-red-500">*</span>
                </label>
                <input type="date" id="tanggal_masuk" name="tanggal_masuk" value="{{ old('tanggal_masuk', now()->format('Y-m-d')) }}" required
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                              @error('tanggal_masuk') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                @error('tanggal_masuk')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Keterangan --}}
            <div>
                <label for="keterangan" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Keterangan
                </label>
                <input type="text" id="keterangan" name="keterangan" value="{{ old('keterangan') }}"
                       class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors"
                       placeholder="Keterangan tambahan (opsional)">
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
                <i class="fa-solid fa-save mr-1.5"></i>
                Simpan
            </button>
        </div>
    </form>
</div>

@endsection
