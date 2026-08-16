@extends('layouts.dashboard')

@section('title', 'Edit Tenant — ' . $tenant->name)
@section('page-title', 'Edit Tenant')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <x-super-admin.page-header
        title="Edit Tenant"
        subtitle="{{ $tenant->name }}"
        backUrl="{{ route('dashboard.super-admin.tenants.index') }}"/>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Edit Form -->
        <x-card class="lg:col-span-2" title="Informasi Tenant">
            <form method="POST" action="{{ route('dashboard.super-admin.tenants.update', $tenant) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Tenant <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $tenant->name) }}" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $tenant->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <div>
                            <label for="is_active" class="text-sm font-medium text-gray-700">Tenant Aktif</label>
                            <p class="text-xs text-gray-500">User dapat login jika aktif.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <input type="hidden" name="is_trial" value="0">
                        <input type="checkbox" name="is_trial" id="is_trial" value="1" {{ old('is_trial', $tenant->is_trial) ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-amber-600 focus:ring-amber-500">
                        <div>
                            <label for="is_trial" class="text-sm font-medium text-gray-700">Mode Trial</label>
                            <p class="text-xs text-gray-500">Tandai tenant sedang dalam trial.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <label for="trial_ends_at" class="block text-sm font-medium text-gray-700">Trial Berakhir</label>
                    <input type="date" name="trial_ends_at" id="trial_ends_at"
                        value="{{ old('trial_ends_at', optional($tenant->trial_ends_at)->format('Y-m-d')) }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('trial_ends_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <x-btn href="{{ route('dashboard.super-admin.tenants.index') }}" variant="ghost">Batal</x-btn>
                    <x-btn type="submit" icon="fa-save" variant="primary">Simpan Perubahan</x-btn>
                </div>
            </form>
        </x-card>

        <!-- Stats -->
        <x-card title="Statistik Tenant">
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Slug</p>
                    <p class="font-mono text-sm text-gray-900 bg-gray-50 px-2 py-1 rounded mt-1">{{ $tenant->slug }}</p>
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-500">Total Santri</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tenant->santri_count }}</p>
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-500">Total User</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $tenant->users_count }}</p>
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <p class="text-sm text-gray-500">Dibuat</p>
                    <p class="text-sm font-medium text-gray-900">{{ $tenant->created_at->format('d M Y') }}</p>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Delete -->
    <x-card class="border-red-200" title="Zona Berbahaya">
        <p class="text-sm text-red-700 mb-4">Menghapus tenant akan menghapus semua data terkait. Tindakan ini tidak dapat dibatalkan.</p>
        <form method="POST" action="{{ route('dashboard.super-admin.tenants.destroy', $tenant) }}" onsubmit="return confirm('Yakin ingin menghapus tenant ini?');">
            @csrf
            @method('DELETE')
            <x-btn type="submit" icon="fa-trash" variant="danger">Hapus Tenant</x-btn>
        </form>
    </x-card>
</div>
@endsection
