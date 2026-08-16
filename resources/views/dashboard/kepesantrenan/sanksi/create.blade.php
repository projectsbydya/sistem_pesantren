@extends('layouts.tenant')

@section('title', 'Tambah Sanksi')
@section('page-title', 'Tambah Sanksi')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.sanksi.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Sanksi</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Tambah</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Tambah Sanksi</h1>
    <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
        Berikan sanksi untuk pelanggaran santri
    </p>
</div>

{{-- Form --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.sanksi.store') }}" class="p-6">
        @csrf

        {{-- Pelanggaran Info (if provided) --}}
        @if($pelanggaran)
            <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                <div class="flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5"></i>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-amber-900 dark:text-amber-100">Pelanggaran Terkait</h4>
                        <p class="text-[13px] text-amber-800 dark:text-amber-200 mt-1">
                            <strong>{{ $pelanggaran->santri->name ?? 'Unknown' }}</strong> - {{ $pelanggaran->kategori }}
                        </p>
                        <p class="text-[12px] text-amber-700 dark:text-amber-300 mt-1">{{ Str::limit($pelanggaran->deskripsi, 100) }}</p>
                        <input type="hidden" name="pelanggaran_id" value="{{ $pelanggaran->id }}">
                    </div>
                </div>
            </div>
        @else
            @php
                $pelanggaranList = \App\Models\Pelanggaran::with('santri')->where('status', '!=', 'selesai')->orderBy('tanggal', 'desc')->get();
            @endphp
            <div class="mb-6">
                <label for="pelanggaran_id" class="block text-[13px] font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                    Pelanggaran <span class="text-red-500">*</span>
                </label>
                <select id="pelanggaran_id" name="pelanggaran_id" required
                        class="w-full px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                               text-gray-900 dark:text-gray-100
                               focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors
                               @error('pelanggaran_id') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror">
                    <option value="">Pilih Pelanggaran</option>
                    @foreach($pelanggaranList as $p)
                        <option value="{{ $p->id }}" {{ old('pelanggaran_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->santri->name ?? 'Unknown' }} - {{ $p->kategori }} ({{ $p->tanggal?->format('d M Y') }})
                        </option>
                    @endforeach
                </select>
                @error('pelanggaran_id')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>
        @endif

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
                    <option value="">Pilih Jenis</option>
                    <option value="peringatan" {{ old('jenis') === 'peringatan' ? 'selected' : '' }}>Peringatan</option>
                    <option value="tugas" {{ old('jenis') === 'tugas' ? 'selected' : '' }}>Tugas Khusus</option>
                    <option value="skorsing" {{ old('jenis') === 'skorsing' ? 'selected' : '' }}>Skorsing</option>
                    <option value="dikembalikan" {{ old('jenis') === 'dikembalikan' ? 'selected' : '' }}>Dikembalikan ke Orang Tua</option>
                </select>
                @error('jenis')
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
                <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">Kosongkan jika sanksi berlaku terus-menerus</p>
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
                                 @error('deskripsi') border-red-300 focus:border-red-500 focus:ring-red-500/20 @enderror"
                          placeholder="Jelaskan detail sanksi yang diberikan...">{{ old('deskripsi') }}</textarea>
                @error('deskripsi')
                    <p class="mt-1 text-[12px] text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ tenant_route('dashboard.kepesantrenan.sanksi.index') }}"
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
