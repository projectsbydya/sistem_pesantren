@extends('layouts.dashboard')

@section('title', 'Manajemen Plan')
@section('page-title', 'Manajemen Plan')

@php
    $billingCycle = request('billing_cycle');
    $isActive = request('is_active');
@endphp

@section('content')
<div class="space-y-6">
    <x-super-admin.page-header
        title="Manajemen Plan"
        subtitle="Kelola paket langganan dan fitur yang tersedia untuk tenant.">
        <x-slot:actions>
            @can('create', App\Models\Plan::class)
                <x-btn href="{{ route('dashboard.super-admin.plans.create') }}" icon="fa-plus" variant="primary">
                    Buat Plan Baru
                </x-btn>
            @endcan
        </x-slot:actions>
    </x-super-admin.page-header>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Total Plan" value="{{ $plans->count() }}" icon="fa-layer-group" color="blue"/>
        <x-stat-card title="Plan Aktif" value="{{ $plans->where('is_active', true)->count() }}" icon="fa-check-circle" color="emerald"/>
        <x-stat-card title="Bulanan" value="{{ $plans->where('billing_cycle', 'monthly')->count() }}" icon="fa-calendar" color="purple"/>
        <x-stat-card title="Tahunan" value="{{ $plans->where('billing_cycle', 'yearly')->count() }}" icon="fa-calendar-alt" color="amber"/>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-sm text-gray-500">Filter:</span>
        <a href="{{ route('dashboard.super-admin.plans.index') }}"
           class="px-3 py-1.5 text-sm font-medium rounded-full transition-colors {{ $billingCycle === null && $isActive === null ? 'bg-emerald-100 text-emerald-700' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Semua
        </a>
        <a href="{{ route('dashboard.super-admin.plans.index', ['billing_cycle' => 'monthly']) }}"
           class="px-3 py-1.5 text-sm font-medium rounded-full transition-colors {{ $billingCycle === 'monthly' ? 'bg-emerald-100 text-emerald-700' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Bulanan
        </a>
        <a href="{{ route('dashboard.super-admin.plans.index', ['billing_cycle' => 'yearly']) }}"
           class="px-3 py-1.5 text-sm font-medium rounded-full transition-colors {{ $billingCycle === 'yearly' ? 'bg-emerald-100 text-emerald-700' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Tahunan
        </a>
        <a href="{{ route('dashboard.super-admin.plans.index', ['is_active' => '1']) }}"
           class="px-3 py-1.5 text-sm font-medium rounded-full transition-colors {{ $isActive === '1' ? 'bg-emerald-100 text-emerald-700' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">
            Aktif
        </a>
    </div>

    <!-- Plans Grid -->
    @if($plans->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($plans as $plan)
                <div class="bg-white rounded-xl shadow-sm border {{ $plan->is_active ? 'border-gray-200' : 'border-gray-200 opacity-75' }} overflow-hidden flex flex-col">
                    <div class="p-6 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">{{ $plan->name }}</h3>
                                <p class="text-sm text-gray-500 font-mono mt-0.5">{{ $plan->code }}</p>
                            </div>
                            <x-badge variant="{{ $plan->is_active ? 'success' : 'default' }}" size="sm">
                                {{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}
                            </x-badge>
                        </div>

                        <div class="mt-4">
                            <span class="text-3xl font-bold text-gray-900">{{ $plan->getFormattedPrice() }}</span>
                            <span class="text-sm text-gray-500">/ {{ $plan->billing_cycle === 'yearly' ? 'tahun' : 'bulan' }}</span>
                        </div>

                        @if($plan->description)
                            <p class="mt-3 text-sm text-gray-600 line-clamp-2">{{ $plan->description }}</p>
                        @endif

                        <div class="mt-5 space-y-2 text-sm">
                            @php
                                $limits = [
                                    ['icon' => 'fa-user-graduate', 'label' => 'Santri', 'value' => $plan->santri_limit ?: '∞'],
                                    ['icon' => 'fa-users', 'label' => 'User', 'value' => $plan->user_limit ?: '∞'],
                                    ['icon' => 'fa-building', 'label' => 'Cabang', 'value' => $plan->branch_limit ?: '∞'],
                                    ['icon' => 'fa-hdd', 'label' => 'Storage', 'value' => ($plan->storage_limit_mb ?: '∞') . ' MB'],
                                ];
                            @endphp
                            @foreach($limits as $limit)
                                <div class="flex items-center justify-between py-1.5 border-b border-gray-100 last:border-0">
                                    <span class="text-gray-500 flex items-center gap-2">
                                        <i class="fa-solid {{ $limit['icon'] }} text-gray-400 text-xs w-4"></i>
                                        {{ $limit['label'] }}
                                    </span>
                                    <span class="font-semibold text-gray-700">{{ $limit['value'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        @if(!empty($plan->features) && is_array($plan->features))
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Fitur</p>
                                <ul class="space-y-1.5">
                                    @foreach($plan->features as $feature => $enabled)
                                        @if($enabled)
                                            <li class="flex items-center gap-2 text-sm text-gray-600">
                                                <i class="fa-solid fa-check text-emerald-500 text-xs"></i>
                                                {{ is_string($feature) ? $feature : 'Fitur ' . ($loop->iteration) }}
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-2">
                        @can('update', $plan)
                            <x-btn href="{{ route('dashboard.super-admin.plans.edit', $plan) }}" icon="fa-pen" variant="outline" size="sm">
                                Edit
                            </x-btn>
                        @endcan
                        <div class="flex items-center gap-2">
                            @can('toggleActive', $plan)
                                <form method="POST" action="{{ route('dashboard.super-admin.plans.toggle-active', $plan) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-lg transition-colors
                                                {{ $plan->is_active ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                                        <i class="fa-solid {{ $plan->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                        {{ $plan->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            @endcan
                            @can('delete', $plan)
                                <form method="POST" action="{{ route('dashboard.super-admin.plans.destroy', $plan) }}" class="inline"
                                      onsubmit="return confirm('Yakin ingin menghapus plan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors" title="Hapus">
                                        <i class="fa-solid fa-trash text-xs"></i>
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-layer-group text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Belum ada plan</h3>
            <p class="text-sm text-gray-500 mb-4">Buat plan pertama untuk mulai menawarkan paket langganan kepada tenant.</p>
            @can('create', App\Models\Plan::class)
                <x-btn href="{{ route('dashboard.super-admin.plans.create') }}" icon="fa-plus" variant="primary">Buat Plan Baru</x-btn>
            @endcan
        </div>
    @endif
</div>
@endsection
