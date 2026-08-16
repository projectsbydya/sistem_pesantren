@extends('layouts.tenant')

@section('title', $kamar->name)
@section('page-title', 'Detail Kamar')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.kamar.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Kamar</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">{{ $kamar->name }}</span>
@endsection

@section('content')

{{-- Header Card --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                <i class="fa-solid fa-bed text-emerald-600 dark:text-emerald-400 text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $kamar->name }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    @php
                        $statusVariant = match($kamar->status) {
                            'aktif' => 'success',
                            'penuh' => 'warning',
                            'nonaktif' => 'default',
                            default => 'default',
                        };
                        $statusLabel = match($kamar->status) {
                            'aktif' => 'Aktif',
                            'penuh' => 'Penuh',
                            'nonaktif' => 'Nonaktif',
                            default => $kamar->status,
                        };
                    @endphp
                    <x-badge variant="{{ $statusVariant }}" size="sm" dot>{{ $statusLabel }}</x-badge>
                    @if($kamar->lokasi)
                        <span class="text-[12px] text-gray-500 dark:text-gray-400">
                            <i class="fa-solid fa-location-dot mr-1"></i>{{ $kamar->lokasi }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('update', $kamar)
                <a href="{{ tenant_route('dashboard.kepesantrenan.kamar.edit', ['kamar' => $kamar->id]) }}"
                   class="px-4 py-2 text-[13px] font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    <i class="fa-solid fa-pen mr-1.5"></i>
                    Edit
                </a>
            @endcan
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
        <div>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Kapasitas</p>
            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $kamar->kapasitas }}</p>
        </div>
        <div>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Terisi</p>
            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $kamar->santri->count() }}</p>
        </div>
        <div>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Tersedia</p>
            @php
                $sisa = $kamar->kapasitas - $kamar->santri->count();
                $sisaClass = $sisa === 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400';
            @endphp
            <p class="text-lg font-bold {{ $sisaClass }}">{{ $sisa }}</p>
        </div>
        <div>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Okupansi</p>
            @php
                $okupansi = $kamar->kapasitas > 0 ? round(($kamar->santri->count() / $kamar->kapasitas) * 100, 1) : 0;
            @endphp
            <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $okupansi }}%</p>
        </div>
    </div>

    @if($kamar->fasilitas)
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-2">Fasilitas</p>
            <p class="text-[13px] text-gray-700 dark:text-gray-300">{{ $kamar->fasilitas }}</p>
        </div>
    @endif

    @if($kamar->description)
        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
            <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-2">Deskripsi</p>
            <p class="text-[13px] text-gray-700 dark:text-gray-300">{{ $kamar->description }}</p>
        </div>
    @endif
</div>

{{-- Penghuni Section --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
        <div>
            <h2 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100">Penghuni Kamar</h2>
            <p class="text-[12px] text-gray-500 dark:text-gray-400 mt-0.5">
                Daftar santri yang menempati kamar ini
            </p>
        </div>
        @can('create', App\Models\PenempatanKamar::class)
            <a href="{{ tenant_route('dashboard.kepesantrenan.penempatan.create', ['kamar_id' => $kamar->id]) }}"
               class="px-3 py-1.5 text-[12px] font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-lg
                      hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors {{ $sisa === 0 ? 'opacity-50 pointer-events-none' : '' }}">
                <i class="fa-solid fa-user-plus mr-1"></i>
                Tambah Penghuni
            </a>
        @endcan
    </div>

    @if($kamar->santri->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-12 px-6">
            <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-users text-gray-400 dark:text-gray-500 text-lg"></i>
            </div>
            <h3 class="text-[14px] font-medium text-gray-900 dark:text-gray-100 mb-1">Belum ada penghuni</h3>
            <p class="text-[12px] text-gray-500 dark:text-gray-400">
                Kamar ini masih kosong.
            </p>
        </div>
    @else
        {{-- Occupants Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama Santri</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">NIS</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gender</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kamar->santri as $santri)
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[12px] font-bold shrink-0">
                                    {{ strtoupper(substr($santri->name, 0, 1)) }}
                                </div>
                                <span class="text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $santri->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-[13px] text-gray-600 dark:text-gray-400">{{ $santri->nis ?? '-' }}</td>
                        <td class="px-6 py-3">
                            <x-badge variant="{{ $santri->gender === 'L' ? 'info' : 'pink' }}" size="sm">
                                {{ $santri->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ tenant_route('dashboard.santri.show', ['id' => $santri->id]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                   title="Lihat Santri">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
