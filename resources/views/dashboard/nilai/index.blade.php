@extends('layouts.tenant')

@section('title', strtoupper($programSlug) . ' — Nilai')

@section('content')
<div class="max-w-6xl mx-auto">

{{-- Dependency Warning --}}
@if(isset($warning) && !$warning['can_access'])
    @php
        $checklist = [
            ['label' => 'Program', 'done' => !in_array('program', $warning['missing'] ?? [])],
            ['label' => 'Kelas', 'done' => !in_array('kelas', $warning['missing'] ?? [])],
            ['label' => 'Mata Pelajaran', 'done' => !in_array('subject', $warning['missing'] ?? [])],
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

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Nilai {{ strtoupper($programSlug) }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pilih kelas, mata pelajaran, dan jenis penilaian</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 p-4 text-sm text-emerald-700 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    @if($kelasList->isEmpty())
        <x-empty-state
            title="Belum ada kelas"
            message="Tambahkan kelas terlebih dahulu untuk memulai input nilai."
            :cta-text="Auth::user()->can('create', \App\Models\Kelas::class) ? 'Tambah Kelas' : null"
            :cta-route="Auth::user()->can('create', \App\Models\Kelas::class) ? 'dashboard.akademik.kelas.create' : null"
            :cta-params="['programSlug' => $programSlug]"
            icon="fa-school"
        />
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($kelasList as $kelas)
                <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5 hover:shadow-md transition-shadow">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ $kelas->name }}</h3>
                    @if($kelas->description)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ $kelas->description }}</p>
                    @endif

                    @if($kelas->subjects->isEmpty())
                        <p class="text-sm text-gray-400 dark:text-gray-500 italic">Belum ada mata pelajaran.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($kelas->subjects as $subject)
                                <div class="rounded-lg border border-gray-100 dark:border-gray-800 overflow-hidden">
                                    <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800/60 text-sm font-medium text-gray-800 dark:text-gray-200">
                                        {{ $subject->name }}
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 p-2">
                                        @can('recordFor', [\App\Models\Nilai::class, $kelas, $subject, $programId])
                                            @foreach($assessmentTypes as $type => $label)
                                                @php $color = $assessmentMeta[$type]->color ?? 'gray'; @endphp
                                                <a href="{{ tenant_route('dashboard.akademik.nilai.input', ['programSlug' => $programSlug]) }}?kelas_id={{ $kelas->id }}&subject_id={{ $subject->id }}&tanggal={{ date('Y-m-d') }}&assessment_type={{ $type }}"
                                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-semibold
                                                          bg-{{ $color }}-50 dark:bg-{{ $color }}-500/10
                                                          text-{{ $color }}-700 dark:text-{{ $color }}-400
                                                          hover:bg-{{ $color }}-100 dark:hover:bg-{{ $color }}-500/20
                                                          border border-{{ $color }}-200 dark:border-{{ $color }}-500/30
                                                          transition-colors">
                                                    <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                                    {{ $label }}
                                                </a>
                                            @endforeach
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">Tidak memiliki akses input</span>
                                        @endcan
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
@endif
</div>
@endsection
