@extends('layouts.tenant')

@section('title', 'Riwayat Perizinan Santri')
@section('page-title', 'Riwayat Perizinan')
@section('breadcrumb')
    <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-emerald-600">Perizinan</a>
    <span class="text-gray-400 mx-2">/</span>
    <span class="text-gray-600 dark:text-gray-300">Riwayat</span>
@endsection

@section('content')

@php
$santriName = $santri?->name ?? 'Santri';
@endphp

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Riwayat Perizinan</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            {{ $santriName }}
            @if($santri?->nis)
                <span class="font-mono text-gray-400 dark:text-gray-500">({{ $santri->nis }})</span>
            @endif
        </p>
    </div>
    <x-btn href="{{ tenant_route('dashboard.kepesantrenan.perizinan.index') }}" variant="secondary" size="sm" icon="fa-arrow-left">
        Kembali
    </x-btn>
</div>

{{-- Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    @if($perizinan->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-16 px-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-rotate-left text-gray-400 dark:text-gray-500 text-xl"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada riwayat perizinan</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                Tidak ada pengajuan izin untuk santri ini.
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Periode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alasan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($perizinan as $p)
                    @php
                        $jenisLabel = $p->jenis === \App\Models\Perizinan::JENIS_PULANG ? 'Pulang' : 'Keluar';
                        $jenisVariant = $p->jenis === \App\Models\Perizinan::JENIS_PULANG ? 'info' : 'default';
                        $statusVariant = match($p->status) {
                            \App\Models\Perizinan::STATUS_PENDING => 'warning',
                            \App\Models\Perizinan::STATUS_DISETUJUI => 'success',
                            \App\Models\Perizinan::STATUS_DITOLAK => 'danger',
                            \App\Models\Perizinan::STATUS_KEMBALI => 'blue',
                            default => 'default',
                        };
                        $statusLabel = match($p->status) {
                            \App\Models\Perizinan::STATUS_PENDING => 'Pending',
                            \App\Models\Perizinan::STATUS_DISETUJUI => 'Disetujui',
                            \App\Models\Perizinan::STATUS_DITOLAK => 'Ditolak',
                            \App\Models\Perizinan::STATUS_KEMBALI => 'Kembali',
                            default => $p->status,
                        };
                    @endphp
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group">
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $jenisVariant }}" size="sm">{{ $jenisLabel }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">
                                {{ $p->tanggal_mulai?->format('d M Y') ?? '-' }}
                                @if($p->tanggal_selesai)
                                    <span class="text-gray-400">- {{ $p->tanggal_selesai?->format('d M Y') }}</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[13px] text-gray-700 dark:text-gray-300 truncate max-w-[200px] block" title="{{ $p->alasan }}">{{ $p->alasan }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $statusVariant }}" size="sm" dot>{{ $statusLabel }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.show', ['perizinan' => $p->id]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                   title="Lihat">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <p class="text-[12px] text-gray-400 dark:text-gray-500">
                Menampilkan {{ $perizinan->firstItem() ?? 0 }} - {{ $perizinan->lastItem() ?? 0 }} dari {{ $perizinan->total() }} izin
            </p>
            <div class="flex gap-1">
                {{ $perizinan->links('pagination::simple-tailwind') }}
            </div>
        </div>
    @endif
</div>

@endsection
