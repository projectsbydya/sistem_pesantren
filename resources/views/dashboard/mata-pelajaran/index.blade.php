@extends('layouts.tenant')

@section('title', 'Mata Pelajaran ' . strtoupper($programSlug))
@section('page-title', 'Mata Pelajaran ' . strtoupper($programSlug))
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Akademik / {{ strtoupper($programSlug) }} / Mata Pelajaran</span>
@endsection

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Mata Pelajaran {{ strtoupper($programSlug) }}</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            Program {{ strtoupper($programSlug) }} — {{ \App\Services\TenantService::getTenant()?->name }}
        </p>
    </div>
    @can('create', [\App\Models\Subject::class, $programSlug])
    <x-btn href="{{ tenant_route('dashboard.akademik.subjects.create', ['programSlug' => $programSlug]) }}" variant="primary" icon="fa-plus">Tambah Mata Pelajaran</x-btn>
    @endcan
</div>

@if(session('success'))
    <x-alert type="success" class="mb-5">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="error" class="mb-5">{{ session('error') }}</x-alert>
@endif

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    @if($subjects->isEmpty())
        <div class="text-center py-14 px-6">
            <i class="fa-solid fa-book text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
            <p class="text-[14px] font-medium text-gray-700 dark:text-gray-300">Belum ada mata pelajaran untuk program ini</p>
            @can('create', [\App\Models\Subject::class, $programSlug])
            <x-btn href="{{ tenant_route('dashboard.akademik.subjects.create', ['programSlug' => $programSlug]) }}" variant="outline" size="sm" class="mt-4">Tambah Sekarang</x-btn>
            @endcan
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kode</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deskripsi</th>
                        <th class="text-right px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($subjects as $s)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors group">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ $s->name }}</td>
                            <td class="px-4 py-3">
                                @if($s->code)
                                    <span class="font-mono text-[11px] bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded">{{ $s->code }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                {{ $s->description ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @can('update', $s)
                                    <a href="{{ tenant_route('dashboard.akademik.subjects.edit', ['programSlug' => $programSlug, 'id' => $s->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors" title="Edit">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </a>
                                    @endcan
                                    @can('delete', $s)
                                    <form action="{{ tenant_route('dashboard.akademik.subjects.destroy', ['programSlug' => $programSlug, 'id' => $s->id]) }}" method="POST"
                                          onsubmit="return confirm('Hapus mata pelajaran \"{{ $s->name }}\"?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors" title="Hapus">
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
    @endif
</div>

@endsection
