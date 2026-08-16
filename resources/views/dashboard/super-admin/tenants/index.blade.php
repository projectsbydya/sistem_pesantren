@extends('layouts.dashboard')

@section('title', 'Daftar Tenant')
@section('page-title', 'Daftar Tenant')

@section('content')
<div class="space-y-6">
    <x-super-admin.page-header
        title="Daftar Tenant"
        subtitle="Kelola semua tenant dan statusnya.">
        <x-slot:actions>
            @can('create', App\Models\Tenant::class)
                <x-btn href="{{ route('dashboard.super-admin.tenants.create') }}" icon="fa-plus" variant="primary">Buat Tenant Baru</x-btn>
            @endcan
        </x-slot:actions>
    </x-super-admin.page-header>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Total Tenant" value="{{ $tenants->count() }}" icon="fa-building" color="blue"/>
        <x-stat-card title="Tenant Aktif" value="{{ $tenants->where('is_active', true)->count() }}" icon="fa-check-circle" color="emerald"/>
        <x-stat-card title="Tenant Trial" value="{{ $tenants->where('is_trial', true)->count() }}" icon="fa-flask" color="amber"/>
        <x-stat-card title="Nonaktif" value="{{ $tenants->where('is_active', false)->count() }}" icon="fa-ban" color="rose"/>
    </div>

    <!-- Table -->
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 rounded-l-lg">Tenant</th>
                        <th class="px-5 py-3">Slug</th>
                        <th class="px-5 py-3 text-center">Santri</th>
                        <th class="px-5 py-3 text-center">User</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 rounded-r-lg text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($tenants as $tenant)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
                                        <span class="font-bold text-emerald-600 text-sm">{{ strtoupper(substr($tenant->name, 0, 1)) }}</span>
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $tenant->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-500 font-mono">{{ $tenant->slug }}</td>
                            <td class="px-5 py-4 text-center">
                                <x-badge variant="info" size="sm">{{ $tenant->santri_count }}</x-badge>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <x-badge variant="purple" size="sm">{{ $tenant->users_count }}</x-badge>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <x-badge variant="{{ $tenant->is_active ? 'success' : 'danger' }}" size="sm">
                                        {{ $tenant->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                    @if($tenant->is_trial)
                                        <x-badge variant="warning" size="sm">Trial</x-badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('update', $tenant)
                                        <x-btn href="{{ route('dashboard.super-admin.tenants.edit', $tenant) }}" icon="fa-pen" variant="outline" size="xs">Edit</x-btn>
                                    @endcan
                                    @can('update', $tenant)
                                        <form method="POST" action="{{ $tenant->is_active ? route('dashboard.super-admin.tenants.deactivate', $tenant) : route('dashboard.super-admin.tenants.activate', $tenant) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-lg transition-colors
                                                        {{ $tenant->is_active ? 'bg-amber-100 text-amber-700 hover:bg-amber-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                                                <i class="fa-solid {{ $tenant->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                                {{ $tenant->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-building text-3xl text-gray-300"></i>
                                    <p>Belum ada tenant</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
