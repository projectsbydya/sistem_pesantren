@extends('layouts.dashboard')

@section('title', 'Super Admin Dashboard')
@section('page-title', 'Super Admin Dashboard')

@php
$tenants = App\Models\Tenant::withCount(['santri', 'users'])->latest()->get();
$totalSantri = App\Models\Santri::count();
$totalUsers = App\Models\User::where('is_super_admin', false)->count();
$nav = App\Services\NavigationGateService::forUser(Auth::user());
@endphp

@section('content')
<div class="space-y-6">
    <x-alert type="warning" title="SUPER ADMIN MODE">
        Anda memiliki akses penuh ke manajemen tenant. Data operasional (santri, user, keuangan) tidak dapat diakses.
    </x-alert>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Total Tenant" value="{{ $tenants->count() }}" icon="fa-building" color="blue"/>
        <x-stat-card title="Total Santri" value="{{ $totalSantri }}" icon="fa-user-graduate" color="emerald"/>
        <x-stat-card title="Total User" value="{{ $totalUsers }}" icon="fa-users" color="purple"/>
        <x-stat-card title="Tenant Trial" value="{{ $tenants->where('is_trial', true)->count() }}" icon="fa-flask" color="amber"/>
    </div>

    <!-- Tenant Summary Cards -->
    <x-card title="Semua Tenant" subtitle="{{ $tenants->count() }} tenant terdaftar">
        @if($tenants->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($tenants as $tenant)
                    <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-sm transition-shadow">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center flex-shrink-0">
                                    <span class="font-bold text-emerald-600 text-sm">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 text-sm">{{ $tenant->name }}</h3>
                                    <p class="text-xs text-gray-500 font-mono">{{ $tenant->slug }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <x-badge variant="{{ $tenant->is_active ? 'success' : 'danger' }}" size="xs">
                                    {{ $tenant->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                                @if($tenant->is_trial)
                                    <x-badge variant="warning" size="xs">Trial</x-badge>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center gap-3 text-sm text-gray-600">
                            <span class="flex items-center gap-1"><i class="fa-solid fa-user-graduate text-blue-400 text-xs"></i> {{ $tenant->santri_count }}</span>
                            <span class="flex items-center gap-1"><i class="fa-solid fa-users text-purple-400 text-xs"></i> {{ $tenant->users_count }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-10">
                <i class="fa-solid fa-building text-4xl text-gray-300 mb-3"></i>
                <p class="text-sm text-gray-500">Belum ada tenant. Buat tenant pertama untuk memulai.</p>
            </div>
        @endif
    </x-card>

    <!-- Quick Actions -->
    <x-card title="Aksi Cepat">
        <div class="flex flex-wrap gap-3">
            @if($nav->canCreateTenant())
                <x-btn href="{{ route('dashboard.super-admin.tenants.create') }}" icon="fa-plus" variant="primary">Buat Tenant Baru</x-btn>
            @endif
            @if($nav->canViewTenants())
                <x-btn href="{{ route('dashboard.super-admin.tenants.index') }}" icon="fa-building" variant="outline">Kelola Tenant</x-btn>
            @endif
            @if($nav->canViewSubscriptions())
                <x-btn href="{{ route('dashboard.super-admin.subscriptions.index') }}" icon="fa-receipt" variant="outline">Subscription & Trial</x-btn>
            @endif
            @if($nav->canViewRevenue())
                <x-btn href="{{ route('dashboard.super-admin.revenue.index') }}" icon="fa-chart-line" variant="outline">Revenue</x-btn>
            @endif
            @if($nav->canViewUsage())
                <x-btn href="{{ route('dashboard.super-admin.usage.index') }}" icon="fa-chart-bar" variant="outline">Usage</x-btn>
            @endif
        </div>
    </x-card>
</div>
@endsection
