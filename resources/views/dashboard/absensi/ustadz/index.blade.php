@extends('layouts.tenant')

@section('title', 'Absensi Ustadz')
@section('page-title', 'Absensi Ustadz')
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Absensi Ustadz</span>
@endsection

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Absensi Ustadz</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            Input kehadiran ustadz harian — {{ \App\Services\TenantService::getTenant()?->name }}
        </p>
    </div>
</div>

@if(session('success'))
    <x-alert type="success" class="mb-5">{{ session('success') }}</x-alert>
@endif

{{-- Date picker --}}
<form method="GET" action="{{ tenant_route('dashboard.sdm.absensi-ustadz.index') }}" class="mb-4">
    <div class="flex items-center gap-3 flex-wrap">
        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal:</label>
        <input type="date" name="tanggal" value="{{ $tanggal }}"
               class="border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
        <x-btn type="submit" variant="outline" size="sm" icon="fa-search">Tampilkan</x-btn>
        <a href="{{ tenant_route('dashboard.sdm.absensi-ustadz.rekap', ['tanggal' => $tanggal]) }}"
           class="inline-flex items-center gap-1.5 text-[12px] font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 transition-colors ml-2">
            <i class="fa-solid fa-chart-bar text-[11px]"></i>
            Rekap
        </a>
    </div>
</form>

<form method="GET" action="{{ tenant_route('dashboard.sdm.absensi-ustadz.index') }}" class="mb-6">
    <input type="hidden" name="tanggal" value="{{ $tanggal }}">
    <div class="max-w-3xl">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jadwal:</label>
        <select name="schedule_id" onchange="this.form.submit()"
                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
            <option value="">Pilih jadwal</option>
            @foreach($jadwalList as $item)
                <option value="{{ $item->id }}" {{ $jadwalId === $item->id ? 'selected' : '' }}>
                    {{ $item->program?->name ?? '-' }} · {{ $item->jam_mulai }}–{{ $item->jam_selesai }} · {{ $item->ustadzKelas?->kelas?->name ?? '-' }} · {{ $item->ustadzKelas?->subject?->name ?? '-' }}
                </option>
            @endforeach
        </select>
        <p class="mt-1.5 text-[12px] text-gray-500 dark:text-gray-400">Ustadz ditentukan otomatis dari jadwal yang dipilih.</p>
    </div>
</form>

@if(!$jadwal && $jadwalId !== null && $existing->isNotEmpty())
    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl border border-amber-200 dark:border-amber-800 p-8 text-center">
        <i class="fa-solid fa-triangle-exclamation text-3xl text-amber-500 mb-3"></i>
        <p class="text-[14px] font-medium text-amber-900 dark:text-amber-200">Absensi historis belum dipetakan ke jadwal</p>
        <p class="text-[12px] text-amber-700 dark:text-amber-300 mt-1">Data tetap tersedia di rekap dan tidak dapat diedit dari halaman jadwal.</p>
    </div>
@elseif($jadwalList->isEmpty() && !$jadwal)
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
        <i class="fa-solid fa-calendar-xmark text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
        <p class="text-[14px] font-medium text-gray-700 dark:text-gray-300">Tidak ada jadwal dengan penugasan ustadz pada tanggal ini</p>
    </div>
@elseif(!$jadwal)
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
        <i class="fa-solid fa-calendar-check text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
        <p class="text-[14px] font-medium text-gray-700 dark:text-gray-300">Pilih jadwal untuk mencatat absensi ustadz</p>
    </div>
@else
    @php
        $attendanceTeacher = $ustadzList->first();
        $hasExistingAttendance = $existing->isNotEmpty();
    @endphp
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3 mb-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Ustadz {{ $hasExistingAttendance ? 'Tercatat' : 'Terjadwal' }}</p>
                <p class="text-[14px] font-semibold text-gray-900 dark:text-gray-100 mt-0.5">{{ $attendanceTeacher?->user?->name ?? '—' }}</p>
            </div>
            @if($hasExistingAttendance)
                <span class="inline-flex items-center self-start sm:self-auto gap-1.5 text-[11px] font-medium px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400">
                    <i class="fa-solid fa-clock-rotate-left text-[10px]"></i>
                    Snapshot absensi
                </span>
            @endif
        </div>
    </div>
    <form method="POST" action="{{ tenant_route('dashboard.sdm.absensi-ustadz.store-bulk') }}">
        @csrf
        <input type="hidden" name="schedule_id" value="{{ $jadwal->id }}">
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        @if($errors->any())
            <x-alert type="error" class="mb-5">
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </x-alert>
        @endif

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-5">
            {{-- Toolbar --}}
            <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 flex-wrap">
                <span class="text-[12px] font-medium text-gray-500 dark:text-gray-400 mr-1">Set semua:</span>
                @foreach(\App\Models\AbsensiUstadz::STATUS as $s)
                    <button type="button" onclick="setAll('{{ $s }}')"
                            class="px-2.5 py-1 text-[11px] font-semibold rounded-md border transition-colors
                                   {{ $s === 'hadir'  ? 'border-emerald-300 text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:border-emerald-700 dark:hover:bg-emerald-900/30' : '' }}
                                   {{ $s === 'izin'   ? 'border-blue-300 text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:border-blue-700 dark:hover:bg-blue-900/30' : '' }}
                                   {{ $s === 'sakit'  ? 'border-amber-300 text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:border-amber-700 dark:hover:bg-amber-900/30' : '' }}
                                   {{ $s === 'alpa'   ? 'border-red-300 text-red-700 hover:bg-red-50 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/30' : '' }}">
                        {{ ucfirst($s) }}
                    </button>
                @endforeach
                <span class="ml-auto text-[12px] text-gray-400 dark:text-gray-500">
                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
                    &middot; {{ $jadwal->ustadzKelas?->subject?->name ?? '-' }}
                    &middot; {{ $jadwal->ustadzKelas?->kelas?->name ?? '-' }}
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="text-left px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-[11px] uppercase tracking-wider">Ustadz</th>
                            @foreach(\App\Models\AbsensiUstadz::STATUS as $s)
                                <th class="px-3 py-3 font-semibold text-[11px] uppercase tracking-wider
                                           {{ $s === 'hadir' ? 'text-emerald-600 dark:text-emerald-400' : '' }}
                                           {{ $s === 'izin'  ? 'text-blue-600 dark:text-blue-400' : '' }}
                                           {{ $s === 'sakit' ? 'text-amber-600 dark:text-amber-400' : '' }}
                                           {{ $s === 'alpa'  ? 'text-red-600 dark:text-red-400' : '' }}">
                                    {{ ucfirst($s) }}
                                </th>
                            @endforeach
                            <th class="text-left px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-[11px] uppercase tracking-wider">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($ustadzList as $i => $ustadz)
                            @php $ex = $existing[$ustadz->id] ?? null; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $ustadz->user?->name ?? '—' }}</p>
                                    @if($hasExistingAttendance)
                                        <p class="text-[11px] text-blue-600 dark:text-blue-400 mt-0.5">Ustadz tercatat saat absensi dibuat</p>
                                    @endif
                                    @php
                                        $subjectNames = $ustadz->subjects->pluck('name')->implode(', ');
                                    @endphp
                                    @if($subjectNames)
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ $subjectNames }}</p>
                                    @endif
                                </td>
                                @foreach(\App\Models\AbsensiUstadz::STATUS as $s)
                                    <td class="px-3 py-3 text-center">
                                        <input type="radio"
                                               name="absensi[{{ $i }}][status]"
                                               value="{{ $s }}"
                                               class="w-4 h-4 cursor-pointer
                                                      {{ $s === 'hadir' ? 'accent-emerald-600' : '' }}
                                                      {{ $s === 'izin'  ? 'accent-blue-600' : '' }}
                                                      {{ $s === 'sakit' ? 'accent-amber-500' : '' }}
                                                      {{ $s === 'alpa'  ? 'accent-red-600' : '' }}"
                                               {{ ($ex ? $ex->status : 'hadir') === $s ? 'checked' : '' }}>
                                    </td>
                                @endforeach
                                <td class="px-4 py-3">
                                    <input type="text"
                                           name="absensi[{{ $i }}][catatan]"
                                           value="{{ old("absensi.{$i}.catatan", $ex?->catatan) }}"
                                           placeholder="Opsional..."
                                           class="w-full text-[12px] border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-end">
            <x-btn type="submit" variant="primary" icon="fa-floppy-disk">
                Simpan Absensi Ustadz
            </x-btn>
        </div>
    </form>
@endif

@push('scripts')
<script>
function setAll(status) {
    document.querySelectorAll('input[type="radio"][value="' + status + '"]').forEach(r => r.checked = true);
}
</script>
@endpush

@endsection
