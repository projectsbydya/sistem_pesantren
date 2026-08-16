@extends('layouts.dashboard')

@section('title', 'Edit Program — ' . $program->name)
@section('page-title', 'Edit Program')

@section('content')
@php
    $inUse = $program->isInUse();
@endphp

<div class="max-w-2xl mx-auto space-y-6">
    <x-super-admin.page-header
        title="Edit Program"
        subtitle="{{ $program->name }}"
        backUrl="{{ route('dashboard.super-admin.programs.index') }}">
        <x-slot:actions>
            @if($inUse)
                <x-badge variant="info" size="sm" dot>Sedang Digunakan</x-badge>
            @endif
        </x-slot:actions>
    </x-super-admin.page-header>

    @if($inUse)
        <x-alert type="warning" title="Program Sedang Digunakan">
            Perubahan slug akan mempengaruhi routing akademik. Ubah nama dan deskripsi dengan aman, tapi hindari mengubah slug jika memungkinkan.
        </x-alert>
    @endif

    <x-card>
        <form method="POST" action="{{ route('dashboard.super-admin.programs.update', $program) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Nama Program <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $program->name) }}" required
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700">
                    Slug <span class="text-red-500">*</span>
                    @if($inUse)
                        <span class="ml-1 text-xs font-normal text-amber-600">— hati-hati mengubah slug</span>
                    @endif
                </label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $program->slug) }}" required
                    pattern="^[a-z0-9-]+$"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono text-sm
                        {{ $inUse ? 'border-amber-300 bg-amber-50' : '' }}">
                <p class="mt-1 text-xs text-gray-400">Hanya huruf kecil, angka, dan tanda hubung.</p>
                @error('slug')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                <textarea name="description" id="description" rows="3"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description', $program->description) }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Maksimal 1000 karakter.</p>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                    {{ old('is_active', $program->is_active) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-emerald-600 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <div>
                    <label for="is_active" class="text-sm font-medium text-gray-700">Program Aktif</label>
                    <p class="text-xs text-gray-400">
                        @if($inUse)
                            Program yang sedang digunakan tidak dapat dinonaktifkan dari sini. Gunakan tombol toggle di halaman katalog.
                        @else
                            Program nonaktif tidak akan tersedia untuk tenant.
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <x-btn href="{{ route('dashboard.super-admin.programs.index') }}" variant="ghost">Batal</x-btn>
                <x-btn type="submit" icon="fa-save" variant="primary">Simpan Perubahan</x-btn>
            </div>
        </form>
    </x-card>
</div>
@endsection
