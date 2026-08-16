@extends('layouts.tenant')

@section('title', 'Hafalan & Nilai')
@section('page-title', 'Hafalan & Nilai')
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Hafalan & Nilai</span>
@endsection

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Hafalan & Nilai</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            Pilih kelas dan mata pelajaran untuk input — {{ \App\Services\TenantService::getTenant()?->name }}
        </p>
    </div>
</div>

@if(session('success'))
    <x-alert type="success" class="mb-5">{{ session('success') }}</x-alert>
@endif

@if($kelasList->isEmpty())
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
        <i class="fa-solid fa-school text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
        <p class="text-[14px] font-medium text-gray-700 dark:text-gray-300">Belum ada kelas yang tersedia</p>
        @can('create', \App\Models\Kelas::class)
        <a href="{{ tenant_route('dashboard.kelas.create') }}"
           class="inline-flex items-center gap-1.5 mt-4 text-[13px] text-emerald-600 hover:text-emerald-700 font-medium transition-colors">
            <i class="fa-solid fa-plus text-[11px]"></i> Tambah Kelas
        </a>
        @endcan
    </div>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($kelasList as $kelas)
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-5">
                <div class="flex items-start justify-between gap-2 mb-4">
                    <div>
                        <p class="font-bold text-[15px] text-gray-900 dark:text-gray-100">{{ $kelas->name }}</p>
                        @if($kelas->description)
                            <p class="text-[12px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $kelas->description }}</p>
                        @endif
                    </div>
                    <span class="shrink-0 text-[11px] bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 px-2 py-1 rounded-lg font-medium">
                        {{ $kelas->subjects->count() }} mapel
                    </span>
                </div>

                @if($kelas->subjects->isEmpty())
                    <p class="text-[12px] text-gray-400 dark:text-gray-500 italic mb-3">Belum ada mata pelajaran</p>
                @else
                    <div class="space-y-1 mb-4">
                        @foreach($kelas->subjects as $subject)
                            <div class="flex items-center justify-between gap-2 py-1.5 px-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                                <span class="text-[12px] font-medium text-gray-700 dark:text-gray-300">{{ $subject->name }}</span>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <a href="{{ tenant_route('dashboard.hafalan-nilai.input', [
                                            'kelas_id'   => $kelas->id,
                                            'subject_id' => $subject->id,
                                            'tanggal'    => today()->toDateString(),
                                            'jenis'      => 'hafalan',
                                        ]) }}"
                                       class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-400 transition-colors">
                                        Hafalan
                                    </a>
                                    <a href="{{ tenant_route('dashboard.hafalan-nilai.input', [
                                            'kelas_id'   => $kelas->id,
                                            'subject_id' => $subject->id,
                                            'tanggal'    => today()->toDateString(),
                                            'jenis'      => 'nilai',
                                        ]) }}"
                                       class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700 hover:bg-blue-200 dark:bg-blue-900/40 dark:text-blue-400 transition-colors">
                                        Nilai
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif

@endsection
