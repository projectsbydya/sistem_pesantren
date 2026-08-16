@extends('layouts.tenant')

@section('title', 'Absensi Santri')
@section('page-title', 'Absensi Santri')
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Absensi Santri</span>
@endsection

@section('content')

{{-- Dependency Warning --}}
@if(isset($warning) && !$warning['can_access'])
    @php
        $checklist = [
            ['label' => 'Program', 'done' => !in_array('program', $warning['missing'] ?? [])],
            ['label' => 'Kelas', 'done' => !in_array('kelas', $warning['missing'] ?? [])],
            ['label' => 'Santri', 'done' => !in_array('santri', $warning['missing'] ?? [])],
            ['label' => 'Jadwal', 'done' => !in_array('jadwal', $warning['missing'] ?? [])],
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

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Absensi Santri</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            Pilih jadwal untuk mengisi absensi — {{ \App\Services\TenantService::getTenant()?->name }}
        </p>
    </div>
    @can('viewAny', \App\Models\AbsensiUstadz::class)
    <a href="{{ tenant_route('dashboard.sdm.absensi-ustadz.index') }}"
       class="inline-flex items-center gap-1.5 text-[12px] font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 transition-colors">
        <i class="fa-solid fa-chalkboard-user text-[11px]"></i>
        Absensi Ustadz
    </a>
    @endcan
</div>

@if(session('success'))
    <x-alert type="success" class="mb-5">{{ session('success') }}</x-alert>
@endif

<form method="GET" action="{{ tenant_route('dashboard.akademik.absensi.index', ['programSlug' => $type]) }}" class="mb-6">
    <div class="flex items-center gap-3 flex-wrap">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal:</label>
        <input type="date" name="tanggal" value="{{ $tanggal }}"
               class="border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
        <x-btn type="submit" variant="outline" size="sm" icon="fa-search">Cari Jadwal</x-btn>
    </div>
</form>

@if($jadwalList->isEmpty())
    <x-empty-state
        :title="'Tidak ada jadwal untuk hari ' . ($hariIndo ?? \Carbon\Carbon::parse($tanggal)->translatedFormat('l'))"
        :message="'Tanggal: ' . \Carbon\Carbon::parse($tanggal)->format('d M Y')"
        icon="fa-calendar-xmark"
    />
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($jadwalList as $jadwal)
            @php
                $classSession = \App\Models\ClassSession::where('schedule_id', $jadwal->id)
                    ->where('session_date', $tanggal)
                    ->first();
                $sessionStatus = $classSession?->status;
                $sessionBlocked = in_array($sessionStatus, [\App\Models\ClassSession::STATUS_CANCELLED, \App\Models\ClassSession::STATUS_HOLIDAY], true);
                $hasActiveSantri = ($jadwal->active_santri_count ?? 0) > 0;
            @endphp
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 hover:border-emerald-300 dark:hover:border-emerald-700 transition-colors">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-gray-100 text-[14px]">{{ $jadwal->subject?->name ?? $jadwal->mata_pelajaran }}</p>
                        <p class="text-[12px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $jadwal->kelas?->name ?? $jadwal->kelas }}</p>
                    </div>
                    <div class="shrink-0 flex flex-col items-end gap-1.5">
                        <span class="text-[11px] font-medium bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 px-2 py-1 rounded-lg">
                            {{ $jadwal->jam_mulai }} – {{ $jadwal->jam_selesai }}
                        </span>
                        @if($classSession)
                            @php $csColor = \App\Models\ClassSession::STATUS_COLORS[$sessionStatus] ?? 'default'; @endphp
                            <span class="text-[10px] font-medium px-2 py-0.5 rounded-full bg-{{ $csColor }}-100 text-{{ $csColor }}-700 dark:bg-{{ $csColor }}-900/30 dark:text-{{ $csColor }}-400">
                                {{ \App\Models\ClassSession::STATUS_LABELS[$sessionStatus] ?? $sessionStatus }}
                            </span>
                        @endif
                    </div>
                </div>
                <p class="text-[12px] text-gray-500 dark:text-gray-400 mb-4">
                    <i class="fa-solid fa-chalkboard-user mr-1"></i>
                    {{ $jadwal->ustadzKelas?->ustadz?->user?->name ?? '—' }}
                </p>
                @if($sessionBlocked)
                    <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-gray-400 dark:text-gray-500 cursor-not-allowed">
                        <i class="fa-solid fa-clipboard-list text-[11px]"></i>
                        Sesi {{ \App\Models\ClassSession::STATUS_LABELS[$sessionStatus] }}
                    </span>
                @elseif(!$hasActiveSantri)
                    <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-emerald-600 opacity-50 cursor-not-allowed"
                          title="Tidak ada santri aktif di kelas ini">
                        <i class="fa-solid fa-clipboard-list text-[11px]"></i>
                        {{ $sessionStatus === \App\Models\ClassSession::STATUS_ONGOING ? 'Lanjutkan Absensi' : 'Isi Absensi' }}
                    </span>
                @elseif(Auth::user()->can('recordFor', $jadwal))
                    <a href="{{ tenant_route('dashboard.akademik.absensi.input', ['programSlug' => $type, 'jadwal_id' => $jadwal->id, 'tanggal' => $tanggal]) }}"
                       class="inline-flex items-center gap-1.5 text-[12px] font-medium text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300 transition-colors">
                        <i class="fa-solid fa-clipboard-list text-[11px]"></i>
                        {{ $sessionStatus === \App\Models\ClassSession::STATUS_ONGOING ? 'Lanjutkan Absensi' : 'Isi Absensi' }}
                    </a>
                @else
                    <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-emerald-600 opacity-50 cursor-not-allowed"
                          title="Jadwal ini bukan penugasan Anda">
                        <i class="fa-solid fa-clipboard-list text-[11px]"></i>
                        Isi Absensi
                    </span>
                @endif
                <span class="mx-2 text-gray-300 dark:text-gray-600">·</span>
                @if($hasActiveSantri && Auth::user()->can('recordFor', $jadwal))
                    <a href="{{ tenant_route('dashboard.akademik.absensi.rekap', ['programSlug' => $type, 'jadwal_id' => $jadwal->id, 'tanggal' => $tanggal]) }}"
                       class="inline-flex items-center gap-1.5 text-[12px] font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 transition-colors">
                        <i class="fa-solid fa-chart-bar text-[11px]"></i>
                        Rekap
                    </a>
                @else
                    <span class="inline-flex items-center gap-1.5 text-[12px] font-medium text-blue-600 opacity-50 cursor-not-allowed"
                          title="{{ $hasActiveSantri ? 'Jadwal ini bukan penugasan Anda' : 'Tidak ada santri aktif di kelas ini' }}">
                        <i class="fa-solid fa-chart-bar text-[11px]"></i>
                        Rekap
                    </span>
                @endif
            </div>
        @endforeach
    </div>
@endif

@endif
@endsection
