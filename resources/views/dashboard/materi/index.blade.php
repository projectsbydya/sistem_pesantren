@extends('layouts.tenant')

@section('title', 'Materi ' . strtoupper($programSlug))
@section('page-title', 'Materi ' . strtoupper($programSlug))
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Materi {{ strtoupper($programSlug) }}</span>
@endsection

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Daftar Materi Pembelajaran</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            {{ \App\Services\TenantService::getTenant()?->name }}
        </p>
    </div>
    @can('create', \App\Models\Materi::class)
        <x-btn href="{{ tenant_route('dashboard.akademik.materi.create', ['programSlug' => $programSlug]) }}" variant="primary" icon="fa-plus">
            Tambah Materi
        </x-btn>
    @endcan
</div>

{{-- Alerts --}}
@if(session('success'))
    <x-alert type="success" class="mb-5">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="error" class="mb-5">{{ session('error') }}</x-alert>
@endif

{{-- Materi List --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    @if($materi->isEmpty())
        <div class="text-center py-14 px-6">
            <i class="fa-solid fa-book-open text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
            <p class="text-[14px] font-medium text-gray-700 dark:text-gray-300">Belum ada materi tersedia</p>
            @can('create', \App\Models\Materi::class)
                <x-btn href="{{ tenant_route('dashboard.akademik.materi.create', ['programSlug' => $programSlug]) }}" variant="outline" size="sm" class="mt-4">
                    Tambah Materi
                </x-btn>
            @endcan
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Judul</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kelas</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mata Pelajaran</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ustadz</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-right px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($materi as $m)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors group">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-mono text-[12px] text-gray-600 dark:text-gray-400">
                                    {{ $m->tanggal->format('d/m/Y') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $m->judul }}</div>
                                @if($m->deskripsi)
                                    <div class="text-[11px] text-gray-500 truncate max-w-xs">{{ Str::limit($m->deskripsi, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-badge variant="default" size="sm">
                                    {{ $m->kelas?->name ?? '-' }}
                                </x-badge>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $m->subject?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $m->ustadzKelas?->ustadz?->user?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <x-badge variant="{{ \App\Models\Materi::STATUS_COLORS[$m->status] }}" size="sm">
                                    {{ \App\Models\Materi::STATUS_LABELS[$m->status] }}
                                </x-badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    @can('update', $m)
                                        <a href="{{ tenant_route('dashboard.akademik.materi.edit', ['programSlug' => $programSlug, 'id' => $m->id]) }}"
                                           class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors" title="Edit">
                                            <i class="fa-solid fa-pen text-[11px]"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $m)
                                        <form action="{{ tenant_route('dashboard.akademik.materi.destroy', ['programSlug' => $programSlug, 'id' => $m->id]) }}" method="POST"
                                              onsubmit="return confirm('Hapus materi ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors" title="Hapus">
                                                <i class="fa-solid fa-trash text-[11px]"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-800">
            {{ $materi->links() }}
        </div>
    @endif
</div>

@endsection
