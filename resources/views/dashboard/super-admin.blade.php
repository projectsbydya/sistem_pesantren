@extends('layouts.dashboard')

@section('title', 'Super Admin Panel')
@section('page-title', 'Super Admin Panel')

@section('content')
@php
$tenants = App\Models\Tenant::withCount(['santri', 'users'])->latest()->get();
$totalUsers = App\Models\User::where('is_super_admin', false)->count();
@endphp

<div class="space-y-6">
    <!-- Warning Banner -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        <div>
            <p class="font-medium text-amber-900">SUPER ADMIN MODE</p>
            <p class="text-sm text-amber-700 mt-1">
                Anda memiliki akses penuh ke semua data. Gunakan dengan hati-hati.
            </p>
        </div>
    </div>

    <!-- Global Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-600">Total Tenant</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $tenants->count() }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-600">Total User</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalUsers }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm font-medium text-gray-600">Trial Tenant</p>
            <p class="text-3xl font-bold text-amber-600 mt-1">
                {{ $tenants->where('is_trial', true)->count() }}
            </p>
        </div>
    </div>

    <!-- Tenants Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Semua Tenant</h3>
            <a href="{{ route('dashboard.super-admin.tenants.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Buat Tenant Baru
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 font-medium">
                    <tr>
                        <th class="px-6 py-3">Tenant</th>
                        <th class="px-6 py-3">Slug</th>
                        <th class="px-6 py-3 text-center">Santri</th>
                        <th class="px-6 py-3 text-center">User</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($tenants as $tenant)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600 font-bold text-sm">
                                        {{ strtoupper(substr($tenant->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $tenant->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">{{ $tenant->slug }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $tenant->santri_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $tenant->users_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    @if($tenant->is_active)
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Aktif</span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Nonaktif</span>
                                    @endif
                                    @if($tenant->is_trial)
                                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Trial</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <form method="POST" action="{{ route('switch-tenant') }}" class="inline">
                                    @csrf
                                    <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                                    <button type="submit" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                                        Masuk Tenant
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Belum ada tenant
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
