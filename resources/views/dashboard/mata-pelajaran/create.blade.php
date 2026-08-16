@extends('layouts.tenant')

@section('title', 'Tambah Mata Pelajaran ' . strtoupper($programSlug))
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.akademik.subjects.index', ['programSlug' => $programSlug]) }}" class="hover:text-emerald-600">Mata Pelajaran {{ strtoupper($programSlug) }}</a>
    <i class="fa-solid fa-chevron-right text-xs mx-2"></i>
    <span>Tambah</span>
@endsection

@section('content')
<div class="max-w-lg mx-auto">

<div class="mb-6">
    <a href="{{ tenant_route('dashboard.akademik.subjects.index', ['programSlug' => $programSlug]) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-emerald-600 transition-colors">
        <i class="fa-solid fa-arrow-left"></i>
        Kembali ke daftar
    </a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">Tambah Mata Pelajaran {{ strtoupper($programSlug) }}</h1>
</div>

<x-card>
    @if($errors->any())
        <x-alert type="error" class="mb-6">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error" class="mb-6">{{ session('error') }}</x-alert>
    @endif

    <form method="POST" action="{{ tenant_route('dashboard.akademik.subjects.store', ['programSlug' => $programSlug]) }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Nama Mata Pelajaran <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                   placeholder="Contoh: Fiqih, Bahasa Arab, Tahfidz">
            @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Kode <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <input type="text" name="code" value="{{ old('code') }}"
                   class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                   placeholder="Contoh: FQH, ARB, THF">
            @error('code')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">
                Deskripsi <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <textarea name="description" rows="3"
                      class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors resize-none"
                      placeholder="Deskripsi singkat mata pelajaran...">{{ old('description') }}</textarea>
            @error('description')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <x-btn href="{{ tenant_route('dashboard.akademik.subjects.index', ['programSlug' => $programSlug]) }}" variant="ghost">Batal</x-btn>
            <x-btn type="submit" variant="primary" icon="fa-save">Simpan</x-btn>
        </div>
    </form>
</x-card>

</div>{{-- max-w-lg mx-auto --}}
@endsection
