@php
    $absensiIndex = tenant_route('dashboard.akademik.absensi.index', ['programSlug' => $type]);
    $absensiStore = tenant_route('dashboard.akademik.absensi.store', ['programSlug' => $type]);
    $classSession = \App\Models\ClassSession::where('schedule_id', $jadwal->id)
        ->where('session_date', $tanggal)
        ->first();
@endphp
@extends('layouts.tenant')

@section('title', 'Input Absensi Santri')
@section('page-title', 'Input Absensi Santri')
@section('breadcrumb')
    <a href="{{ $absensiIndex }}" class="hover:text-emerald-600">Absensi Santri</a>
    <i class="fa-solid fa-chevron-right text-[8px] mx-2"></i>
    <span class="text-gray-600 dark:text-gray-300">Input</span>
@endsection

@section('content')

<div class="mb-5">
    <a href="{{ $absensiIndex . '?tanggal=' . $tanggal }}"
       class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
        <i class="fa-solid fa-arrow-left text-[10px]"></i>
        Kembali
    </a>
</div>

<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-5 py-4 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Jadwal</p>
            <p class="font-bold text-[16px] text-gray-900 dark:text-gray-100">{{ $jadwal->subject?->name ?? $jadwal->mata_pelajaran }}</p>
            <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
                {{ $jadwal->kelas?->name ?? $jadwal->kelas }} &middot; {{ $jadwal->hari }} &middot; {{ $jadwal->jam_mulai}}–{{ $jadwal->jam_selesai }}
                &middot; {{ $jadwal->ustadzKelas?->ustadz?->user?->name ?? '—' }}
            </p>
        </div>
        <div class="text-right">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Tanggal</p>
            <p class="font-semibold text-gray-900 dark:text-gray-100">
                {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
            </p>
            @if($classSession)
                @php $csColor = \App\Models\ClassSession::STATUS_COLORS[$classSession->status] ?? 'default'; @endphp
                <span class="inline-flex items-center mt-1.5 text-[10px] font-medium px-2 py-0.5 rounded-full bg-{{ $csColor }}-100 text-{{ $csColor }}-700 dark:bg-{{ $csColor }}-900/30 dark:text-{{ $csColor }}-400">
                    {{ \App\Models\ClassSession::STATUS_LABELS[$classSession->status] ?? $classSession->status }}
                </span>
            @endif
        </div>
    </div>
</div>

@php
    $sessionBlocked = $classSession && in_array($classSession->status, [\App\Models\ClassSession::STATUS_CANCELLED, \App\Models\ClassSession::STATUS_HOLIDAY], true);
@endphp

{{-- Class Session status banner --}}
@if($sessionBlocked)
    @php $csColor = \App\Models\ClassSession::STATUS_COLORS[$classSession->status] ?? 'default'; @endphp
    <x-alert type="{{ $csColor === 'danger' ? 'error' : ($csColor === 'warning' ? 'warning' : 'info') }}" class="mb-5">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-info text-[14px]"></i>
            <span class="font-medium">Sesi {{ \App\Models\ClassSession::STATUS_LABELS[$classSession->status] ?? $classSession->status }}</span>
        </div>
        <p class="text-[13px] mt-1">Tidak dapat mengisi absensi untuk sesi yang dibatalkan atau libur.</p>
    </x-alert>
@elseif(!$classSession)
    <x-alert type="info" class="mb-5">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-play text-[14px]"></i>
            <span class="font-medium">Mulai Sesi</span>
        </div>
        <p class="text-[13px] mt-1">Simpan absensi untuk memulai sesi kelas.</p>
    </x-alert>
@elseif($classSession->status === \App\Models\ClassSession::STATUS_ONGOING)
    <x-alert type="warning" class="mb-5">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-spinner fa-spin-pulse text-[14px]"></i>
            <span class="font-medium">Sesi Berlangsung</span>
        </div>
        <p class="text-[13px] mt-1">Sesi sedang aktif. Simpan perubahan absensi kapan saja.</p>
    </x-alert>
@elseif($classSession->status === \App\Models\ClassSession::STATUS_COMPLETED)
    <x-alert type="success" class="mb-5">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-check-circle text-[14px]"></i>
            <span class="font-medium">Sesi Selesai</span>
        </div>
        <p class="text-[13px] mt-1">Sesi telah selesai. Anda masih dapat mengedit absensi.</p>
    </x-alert>
@elseif($classSession->status === \App\Models\ClassSession::STATUS_SCHEDULED)
    <x-alert type="info" class="mb-5">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-clock text-[14px]"></i>
            <span class="font-medium">Sesi Terjadwal</span>
        </div>
        <p class="text-[13px] mt-1">Simpan absensi untuk memulai sesi kelas.</p>
    </x-alert>
@endif

@if($errors->any())
    <x-alert type="error" class="mb-5">
        <ul class="list-disc list-inside space-y-1 text-sm">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </x-alert>
@endif

@if($santriList->isEmpty())
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
        <i class="fa-solid fa-users text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
        <p class="text-[14px] font-medium text-gray-700 dark:text-gray-300">Belum ada santri aktif</p>
    </div>
@else
    <form method="POST" action="{{ $absensiStore }}">
        @csrf
        <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-5">
            <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 flex-wrap">
                <span class="text-[12px] font-medium text-gray-500 dark:text-gray-400 mr-1">Set semua:</span>
                @foreach(\App\Models\AbsensiSantri::STATUS as $s)
                    <button type="button" onclick="setAll('{{ $s }}')"
                            class="px-2.5 py-1 text-[11px] font-semibold rounded-md border transition-colors
                                   {{ $s === 'hadir'  ? 'border-emerald-300 text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:border-emerald-700 dark:hover:bg-emerald-900/30' : '' }}
                                   {{ $s === 'izin'   ? 'border-blue-300 text-blue-700 hover:bg-blue-50 dark:text-blue-400 dark:border-blue-700 dark:hover:bg-blue-900/30' : '' }}
                                   {{ $s === 'sakit'  ? 'border-amber-300 text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:border-amber-700 dark:hover:bg-amber-900/30' : '' }}
                                   {{ $s === 'alpa'   ? 'border-red-300 text-red-700 hover:bg-red-50 dark:text-red-400 dark:border-red-700 dark:hover:bg-red-900/30' : '' }}">
                        {{ ucfirst($s) }}
                    </button>
                @endforeach
                <span class="ml-auto text-[12px] text-gray-400 dark:text-gray-500">{{ $santriList->count() }} santri</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="text-left px-4 py-3 font-semibold text-gray-500 dark:text-gray-400 text-[11px] uppercase tracking-wider">Santri</th>
                            @foreach(\App\Models\AbsensiSantri::STATUS as $s)
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
                        @foreach($santriList as $i => $santri)
                            @php $ex = $existing[$santri->id] ?? null; @endphp
                            <input type="hidden" name="absensi[{{ $i }}][santri_id]" value="{{ $santri->id }}">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $santri->name }}</p>
                                    @if($santri->nis)
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ $santri->nis }}</p>
                                    @endif
                                </td>
                                @foreach(\App\Models\AbsensiSantri::STATUS as $s)
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

        <div class="flex items-center justify-between">
            <a href="{{ $absensiIndex . '?tanggal=' . $tanggal }}"
               class="text-[13px] text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors">
                Batal
            </a>
            <x-btn type="submit" variant="primary" icon="fa-floppy-disk">
                Simpan Absensi
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
