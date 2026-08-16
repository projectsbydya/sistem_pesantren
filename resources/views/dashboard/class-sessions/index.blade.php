@extends('layouts.tenant')

@section('title', 'Sesi Kelas ' . strtoupper($programSlug))
@section('page-title', 'Sesi Kelas ' . strtoupper($programSlug))
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Sesi Kelas {{ strtoupper($programSlug) }}</span>
@endsection

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Sesi Kelas</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">Program: {{ $program->name }}</p>
    </div>
    @can('create', \App\Models\ClassSession::class)
        <x-btn href="{{ tenant_route('dashboard.akademik.class-sessions.create', ['programSlug' => $programSlug]) }}" variant="primary" icon="fa-plus">
            Tambah Sesi Kelas
        </x-btn>
    @endcan
</div>

@if(session('success'))
    <x-alert type="success" class="mb-5">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="error" class="mb-5">{{ session('error') }}</x-alert>
@endif

{{-- Filters --}}
<form method="GET" action="{{ tenant_route('dashboard.akademik.class-sessions.index', ['programSlug' => $programSlug]) }}" class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 mb-5">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Status</label>
            <select name="status" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-[13px]">
                <option value="">Semua Status</option>
                @foreach(\App\Models\ClassSession::STATUS as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ \App\Models\ClassSession::STATUS_LABELS[$st] }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1.5">Tanggal</label>
            <input type="date" name="date" value="{{ request('date') }}" class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-[13px]">
        </div>
        <div class="flex items-end">
            <button type="submit" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg text-[13px] font-medium transition-colors">
                <i class="fa-solid fa-filter mr-1.5"></i> Filter
            </button>
            <a href="{{ tenant_route('dashboard.akademik.class-sessions.index', ['programSlug' => $programSlug]) }}" class="ml-2 px-4 py-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 text-[13px]">
                Reset
            </a>
        </div>
    </div>
</form>

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    @if($sessions->isEmpty())
        <x-empty-state
            title="Belum ada sesi kelas"
            :message="'Tambahkan sesi kelas untuk program ' . $program->name"
            :cta-text="Auth::user()->can('create', \App\Models\ClassSession::class) ? 'Tambah Sesi Kelas' : null"
            :cta-route="Auth::user()->can('create', \App\Models\ClassSession::class) ? 'dashboard.akademik.class-sessions.create' : null"
            :cta-params="['programSlug' => $programSlug]"
            icon="fa-chalkboard-user"
        />
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jadwal</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kelas</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ustadz</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="text-right px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($sessions as $s)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors group">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $s->session_date->format('d M Y') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-gray-900 dark:text-gray-100 font-medium">
                                    {{ $s->schedule?->ustadzKelas?->subject?->name ?? $s->schedule?->mata_pelajaran ?? '-' }}
                                </div>
                                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $s->schedule?->hari ?? '-' }} · {{ substr($s->schedule?->jam_mulai ?? '', 0, 5) }}–{{ substr($s->schedule?->jam_selesai ?? '', 0, 5) }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <x-badge variant="default" size="sm">{{ $s->schedule?->ustadzKelas?->kelas?->name ?? $s->schedule?->kelas ?? '-' }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $s->ustadz?->user?->name ?? $s->schedule?->ustadzKelas?->ustadz?->user?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <x-badge variant="{{ $s->status_color }}" size="sm">{{ $s->status_label }}</x-badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @can('update', $s)
                                        <a href="{{ tenant_route('dashboard.akademik.class-sessions.edit', ['programSlug' => $programSlug, 'id' => $s->id]) }}"
                                           class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors" title="Edit">
                                            <i class="fa-solid fa-pen text-[11px]"></i>
                                        </a>
                                    @endcan
                                    @can('delete', $s)
                                        <form action="{{ tenant_route('dashboard.akademik.class-sessions.destroy', ['programSlug' => $programSlug, 'id' => $s->id]) }}"
                                              method="POST" onsubmit="return confirm('Hapus sesi kelas ini?')" class="inline">
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
