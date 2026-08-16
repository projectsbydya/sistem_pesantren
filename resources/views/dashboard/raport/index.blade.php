@extends('layouts.tenant')

@section('title', strtoupper($programSlug) . ' — E-Raport')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">E-Raport {{ strtoupper($programSlug) }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola raport elektronik santri per semester</p>
        </div>
        @can('create', App\Models\Raport::class)
        <a href="{{ tenant_route('dashboard.akademik.raport.create', ['programSlug' => $programSlug]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i class="fa-solid fa-plus"></i>
            Buat Raport
        </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 p-4 text-sm text-emerald-700 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ tenant_route('dashboard.akademik.raport.index', ['programSlug' => $programSlug]) }}"
          class="mb-6 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Semester</label>
                <select name="semester" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
                    <option value="ganjil" {{ $semester === 'ganjil' ? 'selected' : '' }}>Ganjil</option>
                    <option value="genap" {{ $semester === 'genap' ? 'selected' : '' }}>Genap</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" value="{{ $tahunAjaran }}"
                       class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm"
                       placeholder="2025/2026">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Kelas</label>
                <select name="kelas_id" class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
                    <option value="">Semua Kelas</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}" {{ (int) $kelasId === (int) $kelas->id ? 'selected' : '' }}>
                            {{ $kelas->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-4 flex justify-end gap-2">
            <a href="{{ tenant_route('dashboard.akademik.raport.index', ['programSlug' => $programSlug]) }}"
               class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
                Reset
            </a>
            <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">
                Filter
            </button>
        </div>
    </form>

    @if($raport->isEmpty())
        <x-empty-state
            title="Belum ada raport"
            message="Raport untuk semester ini belum dibuat."
            icon="fa-file-invoice"
        />
    @else
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Santri</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kelas</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Semester</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach($raport as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30">
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                {{ $item->santri?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ $item->kelas?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ ucfirst($item->semester) }} {{ $item->tahun_ajaran }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusColor = match($item->status) {
                                        'published' => 'emerald',
                                        'archived' => 'blue',
                                        default => 'gray',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $statusColor }}-100 dark:bg-{{ $statusColor }}-500/10 text-{{ $statusColor }}-800 dark:text-{{ $statusColor }}-400">
                                    {{ $item->status ? ucfirst($item->status) : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ tenant_route('dashboard.akademik.raport.show', ['programSlug' => $programSlug, 'id' => $item->id]) }}"
                                       class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline">
                                        Detail
                                    </a>
                                    @can('update', $item)
                                    <a href="{{ tenant_route('dashboard.akademik.raport.edit', ['programSlug' => $programSlug, 'id' => $item->id]) }}"
                                       class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                        Edit
                                    </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $raport->links() }}
        </div>
    @endif
</div>
@endsection
