@extends('layouts.dashboard')

@section('title', 'Katalog Program')
@section('page-title', 'Katalog Program')

@section('content')
<div class="space-y-6">
    <x-super-admin.page-header
        title="Katalog Program"
        subtitle="Program global yang tersedia untuk semua tenant. Super Admin mengelola katalog ini.">
        <x-slot:actions>
            @can('create', App\Models\Program::class)
                <x-btn href="{{ route('dashboard.super-admin.programs.create') }}" icon="fa-plus" variant="primary">Tambah Program</x-btn>
            @endcan
        </x-slot:actions>
    </x-super-admin.page-header>

    <!-- Flash Messages -->
    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif
    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <!-- Stats Summary -->
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <x-stat-card title="Total Program" value="{{ $programs->count() }}" icon="fa-layer-group" color="blue"/>
        <x-stat-card title="Program Aktif" value="{{ $programs->where('is_active', true)->count() }}" icon="fa-check-circle" color="emerald"/>
        <x-stat-card title="Sedang Digunakan" value="{{ $programs->where('kelas_count', '>', 0)->count() }}" icon="fa-chalkboard" color="purple"/>
    </div>

    <!-- Catalog Table -->
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 font-medium text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-5 py-3 rounded-l-lg">Program</th>
                        <th class="px-5 py-3">Slug</th>
                        <th class="px-5 py-3">Deskripsi</th>
                        <th class="px-5 py-3 text-center">Kelas Aktif</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3 rounded-r-lg text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($programs as $program)
                        <tr class="hover:bg-gray-50 transition-colors {{ $program->is_active ? '' : 'opacity-60' }}">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0
                                        {{ $program->is_active ? 'bg-emerald-100' : 'bg-gray-100' }}">
                                        <span class="font-bold text-sm
                                            {{ $program->is_active ? 'text-emerald-700' : 'text-gray-400' }}">
                                            {{ strtoupper(substr($program->name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $program->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <code class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded font-mono">{{ $program->slug }}</code>
                            </td>
                            <td class="px-5 py-4 text-gray-500 max-w-xs">
                                {{ $program->description ? Str::limit($program->description, 60) : '—' }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($program->kelas_count > 0)
                                    <x-badge variant="info" size="sm">{{ $program->kelas_count }}</x-badge>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <x-badge variant="{{ $program->is_active ? 'success' : 'default' }}" size="sm" dot>
                                    {{ $program->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @can('update', $program)
                                        <x-btn href="{{ route('dashboard.super-admin.programs.edit', $program) }}" variant="outline" size="xs" icon="fa-pen">Edit</x-btn>
                                    @endcan
                                    @can('update', $program)
                                        <form method="POST"
                                              action="{{ route('dashboard.super-admin.programs.toggle-active', $program) }}"
                                              class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-medium rounded-lg transition-colors
                                                        {{ $program->is_active
                                                            ? 'bg-amber-100 text-amber-700 hover:bg-amber-200'
                                                            : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}"
                                                    title="{{ $program->is_active ? 'Nonaktifkan program ini' : 'Aktifkan program ini' }}">
                                                <i class="fa-solid {{ $program->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                {{ $program->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endcan
                                    @can('delete', $program)
                                        <form method="POST"
                                              action="{{ route('dashboard.super-admin.programs.destroy', $program) }}"
                                              class="inline"
                                              onsubmit="return confirm('Yakin ingin menghapus program ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <x-btn type="submit" variant="danger" size="xs" icon="fa-trash">Hapus</x-btn>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-layer-group text-3xl text-gray-300"></i>
                                    <p class="text-sm">Belum ada program dalam katalog.</p>
                                    @can('create', App\Models\Program::class)
                                        <a href="{{ route('dashboard.super-admin.programs.create') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                                            Tambah program pertama
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <p class="text-xs text-gray-400 text-center">
        Program yang sedang digunakan (memiliki kelas aktif) tidak dapat dinonaktifkan atau dihapus.
    </p>
</div>
@endsection
