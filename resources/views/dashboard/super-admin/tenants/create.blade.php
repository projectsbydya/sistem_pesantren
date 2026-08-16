@extends('layouts.dashboard')

@section('title', 'Buat Tenant Baru')
@section('page-title', 'Buat Tenant Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-super-admin.page-header
        title="Buat Tenant Baru"
        subtitle="Buat tenant beserta akun admin pertama."
        backUrl="{{ route('dashboard.super-admin.tenants.index') }}"/>

    @if ($errors->any())
        <x-alert type="error" title="Terjadi Kesalahan">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <x-card>
        <form method="POST" action="{{ route('dashboard.super-admin.tenants.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Tenant <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="trial_days" class="block text-sm font-medium text-gray-700">Masa Trial (hari)</label>
                    <input type="number" id="trial_days" name="trial_days" value="{{ old('trial_days', 14) }}" min="0"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('trial_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="is_active" name="is_active" value="1" checked
                        class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <label for="is_active" class="text-sm font-medium text-gray-700">Aktifkan tenant</label>
                        <p class="text-xs text-gray-500">Tenant yang nonaktif tidak bisa diakses user.</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Akun Admin Pertama</h3>

                <div>
                    <label for="admin_name" class="block text-sm font-medium text-gray-700">Nama Admin <span class="text-red-500">*</span></label>
                    <input type="text" id="admin_name" name="admin_name" value="{{ old('admin_name') }}" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    @error('admin_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="mt-4 rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-800">
                    <p><i class="fa-solid fa-circle-info mr-1"></i> Email admin dan password akan dibuat otomatis berdasarkan nama tenant. Password hanya ditampilkan satu kali di halaman kredensial setelah tenant berhasil dibuat.</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <x-btn href="{{ route('dashboard.super-admin.tenants.index') }}" variant="ghost">Batal</x-btn>
                <x-btn type="submit" icon="fa-save" variant="primary">Buat Tenant</x-btn>
            </div>
        </form>
    </x-card>
</div>
@endsection
