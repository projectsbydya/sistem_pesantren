@extends('layouts.dashboard')

@section('title', 'Tambah Program ke Katalog')
@section('page-title', 'Tambah Program ke Katalog')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <x-super-admin.page-header
        title="Tambah Program ke Katalog"
        subtitle="Program ini akan tersedia secara global untuk semua tenant."
        backUrl="{{ route('dashboard.super-admin.programs.index') }}"/>

    <x-card>
        <form method="POST" action="{{ route('dashboard.super-admin.programs.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Nama Program <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    autocomplete="off"
                    placeholder="Contoh: Tahfidz Intensif"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700">
                    Slug <span class="text-red-500">*</span>
                </label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" required
                    pattern="^[a-z0-9-]+$"
                    placeholder="tahfidz-intensif"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 font-mono text-sm">
                <p class="mt-1 text-xs text-gray-400">Hanya huruf kecil, angka, dan tanda hubung. Dibuat otomatis dari nama — dapat diubah manual.</p>
                @error('slug')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                <textarea name="description" id="description" rows="3"
                    placeholder="Penjelasan singkat tentang program ini..."
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('description') }}</textarea>
                <p class="mt-1 text-xs text-gray-400">Maksimal 1000 karakter.</p>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked
                    class="w-4 h-4 rounded border-gray-300 text-emerald-600 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <div>
                    <label for="is_active" class="text-sm font-medium text-gray-700">Program Aktif</label>
                    <p class="text-xs text-gray-400">Program nonaktif tidak akan tersedia untuk tenant.</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <x-btn href="{{ route('dashboard.super-admin.programs.index') }}" variant="ghost">Batal</x-btn>
                <x-btn type="submit" icon="fa-plus" variant="primary">Tambah ke Katalog</x-btn>
            </div>
        </form>
    </x-card>
</div>

<script>
    (function () {
        const nameInput = document.getElementById('name');
        const slugInput = document.getElementById('slug');
        let slugEdited = slugInput.value.length > 0;

        function toSlug(str) {
            return str
                .toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^a-z0-9-]/g, '')
                .replace(/-+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        nameInput.addEventListener('input', function () {
            if (!slugEdited) {
                slugInput.value = toSlug(this.value);
            }
        });

        slugInput.addEventListener('input', function () {
            slugEdited = this.value.length > 0;
        });
    })();
</script>
@endsection
