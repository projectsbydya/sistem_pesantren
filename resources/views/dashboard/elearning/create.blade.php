@extends('layouts.tenant')

@section('title', 'E-Learning — Tambah Materi')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-5">
        <a href="{{ tenant_route('dashboard.akademik.elearning.index', ['programSlug' => $programSlug]) }}"
           class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">Tambah Materi E-Learning {{ strtoupper($programSlug) }}</h1>
    </div>

    <form method="POST" action="{{ tenant_route('dashboard.akademik.elearning.store', ['programSlug' => $programSlug]) }}" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Judul <span class="text-red-500">*</span></label>
            <input type="text" name="judul" value="{{ old('judul') }}" required
                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
            @error('judul') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
            <textarea name="deskripsi" rows="3"
                      class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">{{ old('deskripsi') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kelas</label>
                <select name="kelas_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">— Semua Kelas —</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}">{{ $k->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mata Pelajaran</label>
                <select name="subject_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">— Semua —</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Link</label>
            <input type="url" name="link" value="{{ old('link') }}" placeholder="https://..."
                   class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition-colors">
                <i class="fa-solid fa-save mr-1"></i> Simpan
            </button>
        </div>
    </form>
</div>
@endsection
