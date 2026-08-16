@extends('layouts.tenant')

@section('title', 'Penugasan ' . strtoupper($programSlug))
@section('page-title', 'Penugasan ' . strtoupper($programSlug))
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Penugasan {{ strtoupper($programSlug) }}</span>
@endsection

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Penugasan Mengajar</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">Program: {{ $program->name }}</p>
    </div>
    <x-btn href="{{ tenant_route('dashboard.akademik.penugasan.create', ['programSlug' => $programSlug]) }}" variant="primary" icon="fa-plus">
        Tambah Penugasan Mengajar
    </x-btn>
</div>

@if(session('success'))
    <x-alert type="success" class="mb-5">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="error" class="mb-5">{{ session('error') }}</x-alert>
@endif

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    @if($penugasan->isEmpty())
        <x-empty-state
            title="Belum ada penugasan mengajar"
            :message="'Tambahkan penugasan untuk program ' . $program->name"
            :cta-text="'Tambah Penugasan'"
            :cta-route="'dashboard.akademik.penugasan.create'"
            :cta-params="['programSlug' => $programSlug]"
            icon="fa-chalkboard-user"
        />
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ustadz</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kelas</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mata Pelajaran</th>
                        <th class="text-right px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($penugasan as $p)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors group">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ $p->ustadz?->user?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <x-badge variant="default" size="sm">{{ $p->kelas?->name ?? '-' }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $p->subject?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ tenant_route('dashboard.akademik.penugasan.edit', ['programSlug' => $programSlug, 'id' => $p->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors" title="Edit">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </a>
                                    <form action="{{ tenant_route('dashboard.akademik.penugasan.destroy', ['programSlug' => $programSlug, 'id' => $p->id]) }}"
                                          method="POST" onsubmit="return confirm('Hapus penugasan ini?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors" title="Hapus">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                        </button>
                                    </form>
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
