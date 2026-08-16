@extends('layouts.tenant')

@section('title', 'Detail Jadwal')
@section('page-title', 'Detail Jadwal')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.jadwal.index') }}" class="hover:text-emerald-600">Jadwal</a>
    <i class="fa-solid fa-chevron-right text-[8px]"></i>
    <span class="text-gray-600 dark:text-gray-300">{{ $schedule->mata_pelajaran }}</span>
@endsection

@section('content')

<div class="mb-6">
    <a href="{{ tenant_route('dashboard.jadwal.index') }}" class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
        <i class="fa-solid fa-arrow-left text-[10px]"></i>
        Kembali ke jadwal
    </a>
</div>

{{-- Profile Header --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-6 transition-colors">
    <div class="px-5 py-5 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="w-14 h-14 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl shrink-0">
            <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="flex-1 min-w-0">
            <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $schedule->mata_pelajaran }}</h1>
            <div class="flex items-center gap-2 mt-1 flex-wrap">
                <x-badge variant="info" size="sm">{{ $schedule->hari }}</x-badge>
                <span class="font-mono text-[12px] text-gray-500 dark:text-gray-400">
                    {{ substr($schedule->jam_mulai, 0, 5) }} – {{ substr($schedule->jam_selesai, 0, 5) }}
                </span>
                <span class="text-gray-300 dark:text-gray-600">·</span>
                <x-badge variant="default" size="sm">{{ $schedule->kelas }}</x-badge>
            </div>
        </div>
        @can('update', $schedule)
            <x-btn href="{{ tenant_route('dashboard.jadwal.edit', ['id' => $schedule->id]) }}" variant="outline" size="sm" icon="fa-pen">
                Edit
            </x-btn>
        @endcan
    </div>
</div>

{{-- Detail Card --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-800">
        <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">Informasi Jadwal</h3>
    </div>
    <div class="p-5">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Mata Pelajaran</dt>
                <dd class="text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $schedule->mata_pelajaran }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Kelas</dt>
                <dd class="text-[13px] text-gray-900 dark:text-gray-100">{{ $schedule->kelas }}</dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Hari</dt>
                <dd><x-badge variant="info" size="sm">{{ $schedule->hari }}</x-badge></dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Waktu</dt>
                <dd class="font-mono text-[13px] text-gray-900 dark:text-gray-100">
                    {{ substr($schedule->jam_mulai, 0, 5) }} – {{ substr($schedule->jam_selesai, 0, 5) }}
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Ustadz</dt>
                <dd class="text-[13px] text-gray-900 dark:text-gray-100">
                    {{ $schedule->ustadzKelas?->ustadz?->user?->name ?? '-' }}
                    @if($schedule->ustadzKelas?->ustadz?->user?->email)
                        <span class="font-mono text-[11px] text-gray-400 ml-2">{{ $schedule->ustadzKelas->ustadz->user->email }}</span>
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Dibuat</dt>
                <dd class="text-[12px] text-gray-400 dark:text-gray-500">{{ $schedule->created_at?->format('d M Y H:i') }}</dd>
            </div>
        </dl>
    </div>
</div>

@endsection
