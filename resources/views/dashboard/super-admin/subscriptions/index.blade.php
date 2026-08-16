@extends('layouts.dashboard')

@section('title', 'Subscription Management')
@section('page-title', 'Subscription Management')

@php
    $statusFilter = request('status');
    $cycleFilter = request('billing_cycle');
    $tenantFilter = request('tenant_id');
    $searchFilter = request('search');

    $badgeMap = [
        'blue' => 'info',
        'emerald' => 'success',
        'amber' => 'warning',
        'rose' => 'danger',
        'gray' => 'default',
    ];
@endphp

@section('content')
<div class="space-y-6">
    <x-super-admin.page-header
        title="Subscription Management"
        subtitle="Kelola subscription untuk semua tenant.">
        <x-slot:actions>
            @can('create', App\Models\Subscription::class)
                <x-btn href="{{ route('dashboard.super-admin.subscriptions.create') }}" icon="fa-plus" variant="primary">Buat Subscription</x-btn>
            @endcan
        </x-slot:actions>
    </x-super-admin.page-header>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Aktif" value="{{ $subscriptions->getCollection()->where('status', 'active')->count() }}" icon="fa-check-circle" color="emerald"/>
        <x-stat-card title="Trial" value="{{ $subscriptions->getCollection()->where('status', 'trial')->count() }}" icon="fa-flask" color="blue"/>
        <x-stat-card title="Kadaluarsa" value="{{ $subscriptions->getCollection()->where('status', 'expired')->count() }}" icon="fa-calendar-times" color="rose"/>
        <x-stat-card title="Ditangguhkan" value="{{ $subscriptions->getCollection()->where('status', 'suspended')->count() }}" icon="fa-pause-circle" color="amber"/>
    </div>

    <!-- Filters -->
    <x-card padding="true">
        <form method="GET" action="{{ route('dashboard.super-admin.subscriptions.index') }}" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari</label>
                <input type="text" name="search" value="{{ $searchFilter }}" placeholder="Nama tenant / paket"
                       class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
            </div>
            <div class="w-full md:w-44">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="">Semua</option>
                    @foreach(App\Models\Subscription::STATUS_LABELS as $key => $label)
                        <option value="{{ $key }}" {{ $statusFilter === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-44">
                <label class="block text-xs font-medium text-gray-500 mb-1">Siklus</label>
                <select name="billing_cycle" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="">Semua</option>
                    <option value="monthly" {{ $cycleFilter === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="yearly" {{ $cycleFilter === 'yearly' ? 'selected' : '' }}>Tahunan</option>
                </select>
            </div>
            <div class="w-full md:w-56">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tenant</label>
                <select name="tenant_id" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="">Semua Tenant</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ (string)$tenantFilter === (string)$t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <x-btn type="submit" icon="fa-filter" variant="secondary" size="md">Filter</x-btn>
                @if($statusFilter || $cycleFilter || $tenantFilter || $searchFilter)
                    <x-btn href="{{ route('dashboard.super-admin.subscriptions.index') }}" variant="ghost" size="md">Reset</x-btn>
                @endif
            </div>
        </form>
    </x-card>

    <!-- Subscriptions Table -->
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 rounded-l-lg">Tenant</th>
                        <th class="px-5 py-3">Paket</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3">Berakhir</th>
                        <th class="px-5 py-3 text-right">Jumlah</th>
                        <th class="px-5 py-3 rounded-r-lg text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($subscriptions as $subscription)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4">
                                <span class="font-medium text-gray-900">{{ $subscription->tenant->name ?? '-' }}</span>
                                @if($subscription->tenant?->is_trial)
                                    <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-amber-100 text-amber-700">Trial</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-medium text-gray-900">{{ $subscription->package_name }}</span>
                                <p class="text-xs text-gray-500">{{ $subscription->billing_cycle === 'yearly' ? 'Tahunan' : 'Bulanan' }}</p>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <x-badge variant="{{ $badgeMap[$subscription->getStatusColor()] ?? 'default' }}" size="sm">
                                    {{ $subscription->getStatusLabel() }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                @if($subscription->ends_at)
                                    <div>{{ $subscription->ends_at->format('d M Y') }}</div>
                                    @if($subscription->isEndingSoon(7) && !$subscription->isExpired())
                                        <div class="text-xs text-amber-600">Berakhir dalam {{ $subscription->getRemainingDays() }} hari</div>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right font-semibold text-gray-900">
                                Rp {{ number_format($subscription->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-btn href="{{ route('dashboard.super-admin.subscriptions.show', $subscription) }}" icon="fa-eye" variant="outline" size="xs">Detail</x-btn>
                                    <x-btn href="{{ route('dashboard.super-admin.subscriptions.edit', $subscription) }}" icon="fa-pen" variant="outline" size="xs">Edit</x-btn>
                                    @if($subscription->isActive())
                                        <form method="POST" action="{{ route('dashboard.super-admin.subscriptions.suspend', $subscription) }}" class="inline"
                                              onsubmit="return confirm('Suspend subscription ini?');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-amber-100 text-amber-600 hover:bg-amber-200 transition-colors" title="Suspend">
                                                <i class="fa-solid fa-pause text-xs"></i>
                                            </button>
                                        </form>
                                    @elseif($subscription->isSuspended() || $subscription->isExpired() || $subscription->isTrialExpired())
                                        <form method="POST" action="{{ route('dashboard.super-admin.subscriptions.activate', $subscription) }}" class="inline"
                                              onsubmit="return confirm('Aktifkan subscription ini?');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200 transition-colors" title="Aktifkan">
                                                <i class="fa-solid fa-play text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                    @if($subscription->isActive() || $subscription->isSuspended() || $subscription->isTrial())
                                        <form method="POST" action="{{ route('dashboard.super-admin.subscriptions.cancel', $subscription) }}" class="inline"
                                              onsubmit="return confirm('Batalkan subscription ini?');">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors" title="Batalkan">
                                                <i class="fa-solid fa-ban text-xs"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-receipt text-3xl text-gray-300"></i>
                                    <p>Belum ada subscription</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscriptions->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </x-card>
</div>
@endsection
