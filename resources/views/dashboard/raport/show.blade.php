@extends('layouts.tenant')

@section('title', strtoupper($programSlug) . ' — Detail E-Raport')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Detail E-Raport</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $raport->santri?->name ?? '-' }} — {{ $raport->kelas?->name ?? '-' }} — {{ ucfirst($raport->semester) }} {{ $raport->tahun_ajaran }}
            </p>
        </div>
        <a href="{{ tenant_route('dashboard.akademik.raport.index', ['programSlug' => $programSlug]) }}"
           class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">
            Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 p-4 text-sm text-emerald-700 dark:text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main raport info --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Informasi Raport</h2>
                    @php
                        $statusColor = match($raport->status) {
                            'published' => 'emerald',
                            'archived' => 'blue',
                            default => 'gray',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-{{ $statusColor }}-100 dark:bg-{{ $statusColor }}-500/10 text-{{ $statusColor }}-800 dark:text-{{ $statusColor }}-400">
                        {{ ucfirst($raport->status) }}
                    </span>
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Santri</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $raport->santri?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Kelas</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $raport->kelas?->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Semester</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($raport->semester) }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Tahun Ajaran</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $raport->tahun_ajaran }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Total Hari Efektif</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $raport->total_hari_efektif }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Absensi</dt>
                        <dd class="font-medium text-gray-900 dark:text-gray-100">
                            Sakit {{ $raport->sakit }} | Izin {{ $raport->izin }} | Alpa {{ $raport->alpa }}
                        </dd>
                    </div>
                </dl>

                @if($raport->catatan_umum)
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800">
                        <dt class="text-gray-500 dark:text-gray-400 text-sm">Catatan Umum</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $raport->catatan_umum }}</dd>
                    </div>
                @endif
            </div>

            {{-- Nilai per subject --}}
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Nilai Akademik</h2>
                @if($raport->nilaiRaport->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data nilai.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                @php
                                    $componentCodes = collect();
                                    $componentLabels = [];
                                    foreach ($raport->nilaiRaport as $nilai) {
                                        foreach ($nilai->nilaiComponents as $component) {
                                            if (! $componentCodes->contains($component->assessment_code)) {
                                                $componentCodes->push($component->assessment_code);
                                                $componentLabels[$component->assessment_code] = $component->assessment_label;
                                            }
                                        }
                                    }
                                @endphp
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Mata Pelajaran</th>
                                    @foreach($componentCodes as $code)
                                        <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $componentLabels[$code] ?? $code }}</th>
                                    @endforeach
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400">Akhir</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-gray-500 dark:text-gray-400">Predikat</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @foreach($raport->nilaiRaport as $nilai)
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $nilai->subject?->name ?? '-' }}</td>
                                        @foreach($componentCodes as $code)
                                            @php
                                                $component = $nilai->nilaiComponents->firstWhere('assessment_code', $code);
                                            @endphp
                                            <td class="px-3 py-2 text-sm text-center text-gray-600 dark:text-gray-400">{{ $component?->score ?? '-' }}</td>
                                        @endforeach
                                        <td class="px-3 py-2 text-sm text-center font-semibold text-gray-900 dark:text-gray-100">{{ $nilai->nilai_akhir }}</td>
                                        <td class="px-3 py-2 text-sm text-center text-gray-900 dark:text-gray-100">{{ $nilai->predikat }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Side actions --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Aksi</h2>
                <div class="space-y-2">
                    @can('update', $raport)
                        <a href="{{ tenant_route('dashboard.akademik.raport.edit', ['programSlug' => $programSlug, 'id' => $raport->id]) }}"
                           class="block w-full text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                            Edit Raport
                        </a>
                    @endcan
                    @can('publish', $raport)
                        <form method="POST" action="{{ tenant_route('dashboard.akademik.raport.publish', ['programSlug' => $programSlug, 'id' => $raport->id]) }}" class="block">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">
                                Terbitkan Raport
                            </button>
                        </form>
                    @endcan
                    <a href="{{ tenant_route('dashboard.akademik.raport.print', ['programSlug' => $programSlug, 'id' => $raport->id]) }}"
                       target="_blank"
                       class="block w-full text-center px-4 py-2 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-800 dark:text-gray-200 text-sm font-medium rounded-lg">
                        Cetak / Print
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
