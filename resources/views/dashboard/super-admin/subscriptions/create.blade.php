@extends('layouts.dashboard')

@section('title', 'Buat Subscription')
@section('page-title', 'Buat Subscription Baru')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-super-admin.page-header
        title="Buat Subscription Baru"
        subtitle="Tetapkan paket, siklus, dan status untuk tenant."
        backUrl="{{ route('dashboard.super-admin.subscriptions.index') }}"/>

    <x-card>
        <form method="POST" action="{{ route('dashboard.super-admin.subscriptions.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Tenant <span class="text-red-500">*</span></label>
                <select name="tenant_id" required class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Pilih Tenant</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ ($preselectedTenant?->id == $t->id) ? 'selected' : '' }}>
                            {{ $t->name }}
                            @if($t->is_trial) (trial) @endif
                        </option>
                    @endforeach
                </select>
                @error('tenant_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Paket <span class="text-red-500">*</span></label>
                    <input type="text" name="package_name" value="{{ old('package_name') }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('package_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Siklus Billing <span class="text-red-500">*</span></label>
                    <select name="billing_cycle" required class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="monthly" {{ old('billing_cycle') == 'monthly' ? 'selected' : '' }}>Bulanan</option>
                        <option value="yearly" {{ old('billing_cycle') == 'yearly' ? 'selected' : '' }}>Tahunan</option>
                    </select>
                    @error('billing_cycle')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jumlah (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount') }}" required min="0"
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status <span class="text-red-500">*</span></label>
                    <select name="status" required class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                        @foreach(App\Models\Subscription::STATUS_LABELS as $status => $label)
                            <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Mulai</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('starts_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Berakhir</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('ends_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Trial Berakhir</label>
                    <input type="datetime-local" name="trial_ends_at" value="{{ old('trial_ends_at') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('trial_ends_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Grace Period Berakhir</label>
                    <input type="datetime-local" name="grace_period_ends_at" value="{{ old('grace_period_ends_at') }}"
                           class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                    @error('grace_period_ends_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <x-btn href="{{ route('dashboard.super-admin.subscriptions.index') }}" variant="ghost">Batal</x-btn>
                <x-btn type="submit" icon="fa-save" variant="primary">Simpan Subscription</x-btn>
            </div>
        </form>
    </x-card>
</div>
@endsection
