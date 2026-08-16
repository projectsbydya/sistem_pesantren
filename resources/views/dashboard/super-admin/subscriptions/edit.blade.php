@extends('layouts.dashboard')

@section('title', 'Edit Subscription — ' . ($subscription->tenant->name ?? 'Subscription'))
@section('page-title', 'Edit Subscription')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-super-admin.page-header
        title="Edit Subscription"
        subtitle="{{ $subscription->tenant->name ?? 'Unknown Tenant' }} — {{ $subscription->package_name }}"
        backUrl="{{ route('dashboard.super-admin.subscriptions.index') }}"/>

    <x-card>
        <form method="POST" action="{{ route('dashboard.super-admin.subscriptions.update', $subscription) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700">Tenant</label>
                <select name="tenant_id" class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500" disabled>
                    <option value="{{ $subscription->tenant_id }}" selected>
                        {{ $subscription->tenant->name ?? 'Unknown' }}
                    </option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Tenant tidak dapat diubah.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Paket <span class="text-red-500">*</span></label>
                    <input type="text" name="package_name" value="{{ old('package_name', $subscription->package_name) }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('package_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Siklus Billing <span class="text-red-500">*</span></label>
                    <select name="billing_cycle" required class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="monthly" {{ old('billing_cycle', $subscription->billing_cycle) == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                        <option value="yearly" {{ old('billing_cycle', $subscription->billing_cycle) == 'yearly' ? 'selected' : '' }}>Tahunan</option>
                    </select>
                    @error('billing_cycle')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jumlah (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount', $subscription->amount) }}" required min="0"
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                        @foreach(App\Models\Subscription::STATUS_LABELS as $status => $label)
                            <option value="{{ $status }}" {{ old('status', $subscription->status) == $status ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Mulai</label>
                    <input type="datetime-local" name="starts_at"
                        value="{{ old('starts_at', $subscription->starts_at?->format('Y-m-d\TH:i')) }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('starts_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Berakhir</label>
                    <input type="datetime-local" name="ends_at"
                        value="{{ old('ends_at', $subscription->ends_at?->format('Y-m-d\TH:i')) }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('ends_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Trial Berakhir</label>
                    <input type="datetime-local" name="trial_ends_at"
                        value="{{ old('trial_ends_at', $subscription->trial_ends_at?->format('Y-m-d\TH:i')) }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('trial_ends_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Grace Period Berakhir</label>
                    <input type="datetime-local" name="grace_period_ends_at"
                        value="{{ old('grace_period_ends_at', $subscription->grace_period_ends_at?->format('Y-m-d\TH:i')) }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('grace_period_ends_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-btn href="{{ route('dashboard.super-admin.subscriptions.index') }}" variant="ghost">Batal</x-btn>
                <x-btn type="submit" icon="fa-save" variant="primary">Simpan Perubahan</x-btn>
            </div>
        </form>
    </x-card>
</div>
@endsection
