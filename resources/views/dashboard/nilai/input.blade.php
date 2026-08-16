@php
$currentColor = $assessmentMeta[$assessmentType]->color ?? 'gray';
$baseInputUrl = tenant_route('dashboard.akademik.nilai.input', ['programSlug' => $programSlug])
    . '?kelas_id=' . $kelas->id
    . '&subject_id=' . $subject->id
    . '&tanggal=' . $tanggal;
@endphp

@extends('layouts.tenant')

@section('title', strtoupper($programSlug) . ' — Input ' . ($assessmentTypes[$assessmentType] ?? $assessmentType))

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-5">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Input Nilai {{ strtoupper($programSlug) }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            {{ $kelas->name }} — {{ $subject->name }} — {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('d F Y') }}
        </p>
    </div>

    {{-- Assessment Type Tabs --}}
    <div class="flex flex-wrap gap-2 mb-5">
        @foreach($assessmentTypes as $type => $label)
            @php $color = $assessmentMeta[$type]->color ?? 'gray'; $isActive = $type === $assessmentType; @endphp
            <a href="{{ $baseInputUrl }}&assessment_type={{ $type }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-sm font-semibold transition-colors
                      {{ $isActive
                          ? 'bg-' . $color . '-600 text-white shadow-sm'
                          : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                @if($isActive)<i class="fa-solid fa-circle-check text-xs"></i>@endif
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 p-4 text-sm text-emerald-700 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    @if($santriList->isEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">Tidak ada santri di kelas ini</h3>
        </div>
    @else
        <form method="POST" action="{{ tenant_route('dashboard.akademik.nilai.store', ['programSlug' => $programSlug]) }}">
            @csrf
            <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            <input type="hidden" name="tanggal" value="{{ $tanggal }}">
            <input type="hidden" name="assessment_type" value="{{ $assessmentType }}">

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                {{-- Type indicator header --}}
                <div class="px-4 py-2.5 bg-{{ $currentColor }}-50 dark:bg-{{ $currentColor }}-500/10 border-b border-{{ $currentColor }}-200 dark:border-{{ $currentColor }}-500/20 flex items-center gap-2">
                    <span class="text-xs font-bold uppercase tracking-wide text-{{ $currentColor }}-700 dark:text-{{ $currentColor }}-400">
                        {{ $assessmentTypes[$assessmentType] ?? $assessmentType }}
                    </span>
                    @if(!$existing->isEmpty())
                        <span class="text-xs text-{{ $currentColor }}-600 dark:text-{{ $currentColor }}-400">
                            · {{ $existing->count() }} dari {{ $santriList->count() }} sudah diisi
                        </span>
                    @endif
                </div>

                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase">Santri</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-40">Materi</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-24">Nilai (0–100)</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase w-48">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($santriList as $i => $santri)
                            @php $ex = $existing[$santri->id] ?? null; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $santri->name }}
                                    <input type="hidden" name="records[{{ $i }}][santri_id]" value="{{ $santri->id }}">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="text" name="records[{{ $i }}][materi]" value="{{ old("records.$i.materi", $ex?->materi) }}"
                                           class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-{{ $currentColor }}-500 focus:border-{{ $currentColor }}-500" placeholder="Materi">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" name="records[{{ $i }}][nilai]" value="{{ old("records.$i.nilai", $ex?->nilai) }}" min="0" max="100" step="0.01"
                                           class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-{{ $currentColor }}-500 focus:border-{{ $currentColor }}-500" placeholder="0-100">
                                </td>
                                <td class="px-4 py-2">
                                    <input type="text" name="records[{{ $i }}][catatan]" value="{{ old("records.$i.catatan", $ex?->catatan) }}"
                                           class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 text-sm focus:ring-{{ $currentColor }}-500 focus:border-{{ $currentColor }}-500" placeholder="Catatan">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <a href="{{ tenant_route('dashboard.akademik.nilai.index', ['programSlug' => $programSlug]) }}"
                   class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Kembali
                </a>
                <button type="submit" class="px-6 py-2.5 bg-{{ $currentColor }}-600 hover:bg-{{ $currentColor }}-700 text-white rounded-lg text-sm font-semibold transition-colors">
                    <i class="fa-solid fa-save mr-1"></i> Simpan {{ $assessmentTypes[$assessmentType] ?? $assessmentType }}
                </button>
            </div>
        </form>
    @endif
</div>
@endsection
