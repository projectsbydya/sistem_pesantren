@extends('layouts.tenant')

@section('title', 'Jadwal ' . strtoupper($programSlug))
@section('page-title', 'Jadwal ' . strtoupper($programSlug))
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Jadwal {{ strtoupper($programSlug) }}</span>
@endsection

@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Jadwal Pelajaran</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            {{ \App\Services\TenantService::getTenant()?->name }}
        </p>
    </div>
    @can('create', \App\Models\Schedule::class)
        <x-btn href="{{ tenant_route('dashboard.akademik.jadwal.create', ['programSlug' => $programSlug]) }}" variant="primary" icon="fa-plus">
            Tambah Jadwal
        </x-btn>
    @endcan
</div>


{{-- Dependency Warning --}}
@if(isset($warning) && !$warning['can_access'])
    @php
        $checklist = [
            ['label' => 'Program', 'done' => !in_array('program', $warning['missing'] ?? [])],
            ['label' => 'Kelas', 'done' => !in_array('kelas', $warning['missing'] ?? [])],
            ['label' => 'Mata Pelajaran', 'done' => !in_array('subject', $warning['missing'] ?? [])],
            ['label' => 'Penugasan Mengajar', 'done' => !in_array('ustadz_kelas', $warning['missing'] ?? [])],
        ];
    @endphp
    <x-empty-state
        :title="$warning['warning']"
        :message="$warning['message']"
        :checklist="$checklist"
        :cta-text="$warning['cta_text'] ?? null"
        :cta-route="$warning['cta_route'] ?? null"
        :cta-params="$warning['cta_params'] ?? []"
        icon="fa-triangle-exclamation"
        variant="warning"
    />
@else

{{-- Alerts --}}
@if(session('success'))
    <x-alert type="success" class="mb-5">{{ session('success') }}</x-alert>
@endif
@if(session('error'))
    <x-alert type="error" class="mb-5">{{ session('error') }}</x-alert>
@endif

{{-- Day Tabs --}}
@php
    $hariList  = \App\Models\Schedule::HARI;
    $activeHari = request('hari', $hariList[0]);
@endphp

<div class="flex gap-1 mb-5 overflow-x-auto pb-1">
    @foreach($hariList as $hari)
        @php $count = ($grouped[$hari] ?? collect())->count(); @endphp
        <a href="{{ request()->fullUrlWithQuery(['hari' => $hari]) }}"
           class="flex-shrink-0 px-4 py-2 rounded-lg text-[13px] font-medium transition-colors
                  {{ $activeHari === $hari
                     ? 'bg-emerald-600 text-white'
                     : 'bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
            {{ $hari }}
            @if($count > 0)
                <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full text-[11px]
                             {{ $activeHari === $hari ? 'bg-emerald-500 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500' }}">
                    {{ $count }}
                </span>
            @endif
        </a>
    @endforeach
</div>

{{-- Schedule Table for active day --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    @php $daySchedules = $grouped[$activeHari] ?? collect(); @endphp

    @if($daySchedules->isEmpty())
        <x-empty-state
            :title="'Tidak ada jadwal hari ' . $activeHari"
            message="Belum ada jadwal untuk hari ini"
            :cta-text="Auth::user()->can('create', \App\Models\Schedule::class) ? 'Tambah Jadwal' : null"
            :cta-route="Auth::user()->can('create', \App\Models\Schedule::class) ? 'dashboard.akademik.jadwal.create' : null"
            :cta-params="['programSlug' => $programSlug]"
            icon="fa-calendar-xmark"
        />
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800">
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mata Pelajaran</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kelas</th>
                        <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ustadz</th>
                        <th class="text-right px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aktivitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($daySchedules->sortBy('jam_mulai') as $j)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors group">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="font-mono text-[12px] font-semibold text-emerald-700 dark:text-emerald-400">
                                    {{ substr($j->jam_mulai, 0, 5) }}
                                </span>
                                <span class="text-gray-400 mx-1">–</span>
                                <span class="font-mono text-[12px] text-gray-500">{{ substr($j->jam_selesai, 0, 5) }}</span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ $j->ustadzKelas?->subject?->name ?? $j->mata_pelajaran ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <x-badge variant="default" size="sm">
                                    {{ $j->ustadzKelas?->kelas?->name ?? $j->kelas ?? '-' }}
                                </x-badge>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                {{ $j->ustadzKelas?->ustadz?->user?->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Materi shortcut --}}
                                    @can('viewAny', \App\Models\Materi::class)
                                    <a href="{{ tenant_route('dashboard.akademik.materi.dari-jadwal', ['programSlug' => $programSlug, 'jadwalId' => $j->id]) }}?tanggal={{ request('tanggal', today()->toDateString()) }}"
                                       class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-medium bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors"
                                       title="Input Materi">
                                        <i class="fa-solid fa-book-open text-[10px]"></i>
                                        Materi
                                    </a>
                                    @endcan
                                    {{-- Absensi shortcut --}}
                                    @can('recordFor', $j)
                                    <a href="{{ tenant_route('dashboard.akademik.absensi.index', ['programSlug' => $programSlug]) }}?jadwal_id={{ $j->id }}&tanggal={{ request('tanggal', today()->toDateString()) }}"
                                       class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-medium bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors"
                                       title="Input Absensi">
                                        <i class="fa-solid fa-clipboard-list text-[10px]"></i>
                                        Absensi
                                    </a>
                                    @endcan
                                    {{-- Nilai shortcut --}}
                                    @can('recordFor', $j)
                                    <a href="{{ tenant_route('dashboard.akademik.nilai.dari-jadwal', ['programSlug' => $programSlug, 'jadwalId' => $j->id]) }}?tanggal={{ request('tanggal', today()->toDateString()) }}"
                                       class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-colors"
                                       title="Input Nilai">
                                        <i class="fa-solid fa-chart-line text-[10px]"></i>
                                        Nilai
                                    </a>
                                    @endcan
                                    {{-- Edit/Delete --}}
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        @can('update', $j)
                                        <a href="{{ tenant_route('dashboard.akademik.jadwal.edit', ['programSlug' => $programSlug, 'id' => $j->id]) }}"
                                           class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition-colors" title="Edit">
                                            <i class="fa-solid fa-pen text-[11px]"></i>
                                        </a>
                                        @endcan
                                        @can('delete', $j)
                                        <form action="{{ tenant_route('dashboard.akademik.jadwal.destroy', ['programSlug' => $programSlug, 'id' => $j->id]) }}" method="POST"
                                              onsubmit="return confirm('Hapus jadwal ini?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors" title="Hapus">
                                                <i class="fa-solid fa-trash text-[11px]"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endif
</div>

@endsection
