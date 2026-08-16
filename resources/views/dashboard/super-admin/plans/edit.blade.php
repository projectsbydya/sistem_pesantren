@extends('layouts.dashboard')

@section('title', 'Edit Plan')
@section('page-title', 'Edit Plan — ' . $plan->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-super-admin.page-header
        title="Edit Plan"
        subtitle="{{ $plan->name }}"
        backUrl="{{ route('dashboard.super-admin.plans.index') }}">
        <x-slot:actions>
            @can('delete', $plan)
                <form method="POST" action="{{ route('dashboard.super-admin.plans.destroy', $plan) }}" class="inline"
                      onsubmit="return confirm('Yakin ingin menghapus plan {{ $plan->name }}?');">
                    @csrf
                    @method('DELETE')
                    <x-btn type="submit" icon="fa-trash" variant="danger" size="sm">Hapus</x-btn>
                </form>
            @endcan
        </x-slot:actions>
    </x-super-admin.page-header>

    <x-card>
        <form method="POST" action="{{ route('dashboard.super-admin.plans.update', $plan) }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Plan <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $plan->name) }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="code" class="block text-sm font-medium text-gray-700">Kode / Slug <span class="text-red-500">*</span></label>
                    <input type="text" id="code" name="code" value="{{ old('code', $plan->code) }}" required
                           pattern="^[a-z0-9\-]+$"
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 font-mono text-sm">
                    <p class="mt-1 text-xs text-gray-500">Hanya huruf kecil, angka, dan tanda hubung.</p>
                    @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"
                              class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">{{ old('description', $plan->description) }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Harga (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" id="price" name="price" value="{{ old('price', $plan->price) }}" required min="0"
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('price')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="billing_cycle" class="block text-sm font-medium text-gray-700">Siklus Billing <span class="text-red-500">*</span></label>
                    <select id="billing_cycle" name="billing_cycle" required
                            class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="monthly" {{ old('billing_cycle', $plan->billing_cycle) === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                        <option value="yearly" {{ old('billing_cycle', $plan->billing_cycle) === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                    </select>
                    @error('billing_cycle')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="trial_days" class="block text-sm font-medium text-gray-700">Trial (hari) <span class="text-red-500">*</span></label>
                    <input type="number" id="trial_days" name="trial_days" value="{{ old('trial_days', $plan->trial_days) }}" required min="0" max="365"
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('trial_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="santri_limit" class="block text-sm font-medium text-gray-700">Limit Santri <span class="text-xs text-gray-400">(0 = unlimited)</span></label>
                    <input type="number" id="santri_limit" name="santri_limit" value="{{ old('santri_limit', $plan->santri_limit) }}" required min="0"
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('santri_limit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="user_limit" class="block text-sm font-medium text-gray-700">Limit User <span class="text-xs text-gray-400">(0 = unlimited)</span></label>
                    <input type="number" id="user_limit" name="user_limit" value="{{ old('user_limit', $plan->user_limit) }}" required min="0"
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('user_limit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="branch_limit" class="block text-sm font-medium text-gray-700">Limit Cabang <span class="text-xs text-gray-400">(0 = unlimited)</span></label>
                    <input type="number" id="branch_limit" name="branch_limit" value="{{ old('branch_limit', $plan->branch_limit) }}" required min="0"
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('branch_limit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="storage_limit_mb" class="block text-sm font-medium text-gray-700">Limit Storage (MB) <span class="text-xs text-gray-400">(0 = unlimited)</span></label>
                    <input type="number" id="storage_limit_mb" name="storage_limit_mb" value="{{ old('storage_limit_mb', $plan->storage_limit_mb) }}" required min="0"
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('storage_limit_mb')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-100 rounded-lg">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                <div>
                    <label for="is_active" class="text-sm font-medium text-gray-700">Plan aktif</label>
                    <p class="text-xs text-gray-500">Plan nonaktif tidak akan ditampilkan saat pembuatan subscription.</p>
                </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-500">Terakhir diupdate: {{ $plan->updated_at?->format('d M Y H:i') ?? '-' }}</p>
                <div class="flex items-center gap-3">
                    <x-btn href="{{ route('dashboard.super-admin.plans.index') }}" variant="ghost">Batal</x-btn>
                    <x-btn type="submit" icon="fa-save" variant="primary">Simpan Perubahan</x-btn>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection
