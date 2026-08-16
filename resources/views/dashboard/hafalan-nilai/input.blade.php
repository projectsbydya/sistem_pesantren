@extends('layouts.tenant')

@section('title', 'Input Hafalan & Nilai')
@section('page-title', 'Input Hafalan & Nilai')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.hafalan-nilai.index') }}" class="hover:text-emerald-600">Hafalan & Nilai</a>
    <i class="fa-solid fa-chevron-right text-[8px] mx-2"></i>
    <span class="text-gray-600 dark:text-gray-300">Input</span>
@endsection

@section('content')

<div class="mb-5">
    <a href="{{ tenant_route('dashboard.hafalan-nilai.index') }}"
       class="inline-flex items-center gap-1.5 text-[13px] text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors">
        <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali
    </a>
</div>

{{-- Info card --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-5 py-4 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Kelas & Mata Pelajaran</p>
            <p class="font-bold text-[16px] text-gray-900 dark:text-gray-100">{{ $kelas->name }} — {{ $subject->name }}</p>
            @if($subject->code)
                <p class="text-[12px] text-gray-500 dark:text-gray-400 mt-0.5 font-mono">{{ $subject->code }}</p>
            @endif
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Jenis</p>
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[12px] font-semibold
                    {{ $jenis === 'hafalan' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' }}">
                    {{ \App\Models\HafalanNilai::JENIS_LABELS[$jenis] ?? $jenis }}
                </span>
            </div>
            <div class="text-right">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Tanggal</p>
                <p class="font-semibold text-gray-900 dark:text-gray-100 text-[13px]">
                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
                </p>
            </div>
        </div>
    </div>
</div>

{{-- Date/jenis picker --}}
<form method="GET" action="{{ tenant_route('dashboard.hafalan-nilai.input') }}" class="mb-6">
    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
    <input type="hidden" name="subject_id" value="{{ $subject->id }}">
    <div class="flex items-center gap-3 flex-wrap">
        <label class="text-[12px] font-medium text-gray-600 dark:text-gray-400">Jenis:</label>
        <select name="jenis" class="border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-2.5 py-1.5 text-[12px] focus:ring-1 focus:ring-emerald-500">
            @foreach(\App\Models\HafalanNilai::JENIS as $j)
                <option value="{{ $j }}" {{ $jenis === $j ? 'selected' : '' }}>{{ \App\Models\HafalanNilai::JENIS_LABELS[$j] }}</option>
            @endforeach
        </select>
        <label class="text-[12px] font-medium text-gray-600 dark:text-gray-400">Tanggal:</label>
        <input type="date" name="tanggal" value="{{ $tanggal }}"
               class="border border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-2.5 py-1.5 text-[12px] focus:ring-1 focus:ring-emerald-500">
        <x-btn type="submit" variant="outline" size="sm" icon="fa-search">Terapkan</x-btn>
    </div>
</form>

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
        <p class="text-[14px] font-medium text-gray-700 dark:text-gray-300">Belum ada santri aktif di kelas ini</p>
    </div>
@else
    <form method="POST" action="{{ tenant_route('dashboard.hafalan-nilai.store-bulk') }}">
        @csrf
        <input type="hidden" name="kelas_id"   value="{{ $kelas->id }}">
        <input type="hidden" name="subject_id" value="{{ $subject->id }}">
        <input type="hidden" name="tanggal"    value="{{ $tanggal }}">
        <input type="hidden" name="jenis"      value="{{ $jenis }}">

        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden mb-5">
            <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between flex-wrap gap-2">
                <span class="text-[12px] font-medium text-gray-500 dark:text-gray-400">{{ $santriList->count() }} santri</span>
                <span class="text-[11px] text-gray-400 dark:text-gray-500">
                    Nilai 0–100 · Materi opsional
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Santri</th>
                            <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">Materi</th>
                            <th class="text-left px-4 py-3 text-[11px] font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider w-24">Nilai</th>
                            <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                        @foreach($santriList as $i => $santri)
                            @php $ex = $existing[$santri->id] ?? null; @endphp
                            <input type="hidden" name="records[{{ $i }}][santri_id]" value="{{ $santri->id }}">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $santri->name }}</p>
                                    @if($santri->nis)
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ $santri->nis }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text"
                                           name="records[{{ $i }}][materi]"
                                           value="{{ old("records.{$i}.materi", $ex?->materi) }}"
                                           placeholder="Mis. Al-Baqarah 1-5"
                                           class="w-full text-[12px] border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number"
                                           name="records[{{ $i }}][nilai]"
                                           value="{{ old("records.{$i}.nilai", $ex?->nilai) }}"
                                           min="0" max="100" step="0.01"
                                           placeholder="—"
                                           class="w-full text-[12px] border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                                </td>
                                <td class="px-4 py-3">
                                    <input type="text"
                                           name="records[{{ $i }}][catatan]"
                                           value="{{ old("records.{$i}.catatan", $ex?->catatan) }}"
                                           placeholder="Opsional..."
                                           class="w-full text-[12px] border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ tenant_route('dashboard.hafalan-nilai.index') }}"
               class="text-[13px] text-gray-500 hover:text-gray-700 dark:text-gray-400 transition-colors">Batal</a>
            <x-btn type="submit" variant="primary" icon="fa-floppy-disk">Simpan</x-btn>
        </div>
    </form>
@endif

@endsection
