@extends('layouts.tenant')

@section('title', 'Data Perizinan')
@section('page-title', 'Data Perizinan')
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Perizinan</span>
@endsection

@section('content')

@php
$isRequester = auth()->user()->santri || auth()->user()->parent;
@endphp

{{-- Statistics Cards --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <x-stat-card title="Total" value="{{ $statistics['total'] }}" icon="fa-file-signature" color="emerald" />
    <x-stat-card title="Pending" value="{{ $statistics['pending'] }}" icon="fa-clock" color="amber" />
    <x-stat-card title="Disetujui" value="{{ $statistics['disetujui'] }}" icon="fa-check" color="rose" />
    <x-stat-card title="Ditolak" value="{{ $statistics['ditolak'] }}" icon="fa-xmark" color="rose" />
    <x-stat-card title="Kembali" value="{{ $statistics['kembali'] }}" icon="fa-rotate-left" color="blue" />
</div>

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Data Perizinan</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            Kelola pengajuan izin keluar/pulang santri
        </p>
    </div>
    <div class="flex gap-2">
        @if(!$isRequester)
            <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.pending') }}"
               class="px-4 py-2 text-[13px] font-medium text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/20 rounded-lg hover:bg-amber-100 dark:hover:bg-amber-500/20 transition-colors">
                <i class="fa-solid fa-clock mr-1.5"></i>
                Pending ({{ $statistics['pending'] }})
            </a>
        @endif
        @can('create', App\Models\Perizinan::class)
            <x-btn href="{{ tenant_route('dashboard.kepesantrenan.perizinan.create') }}" variant="primary" icon="fa-plus">
                Ajukan Izin
            </x-btn>
        @endcan
    </div>
</div>

{{-- Search & Filters --}}
<form method="GET" action="{{ tenant_route('dashboard.kepesantrenan.perizinan.index') }}" class="flex flex-col lg:flex-row gap-3 mb-5">
    @if(!$isRequester)
        <div class="flex-1">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama santri atau NIS..."
                       class="w-full pl-9 pr-4 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                              focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors">
            </div>
        </div>
    @endif
    <div class="flex gap-2 flex-wrap">
        <select name="jenis" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                       text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
            <option value="">Semua Jenis</option>
            <option value="{{ \App\Models\Perizinan::JENIS_PULANG }}" {{ request('jenis') === \App\Models\Perizinan::JENIS_PULANG ? 'selected' : '' }}>Pulang</option>
            <option value="{{ \App\Models\Perizinan::JENIS_KELUAR }}" {{ request('jenis') === \App\Models\Perizinan::JENIS_KELUAR ? 'selected' : '' }}>Keluar</option>
        </select>
        <select name="status" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                       text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
            <option value="">Semua Status</option>
            <option value="{{ \App\Models\Perizinan::STATUS_PENDING }}" {{ request('status') === \App\Models\Perizinan::STATUS_PENDING ? 'selected' : '' }}>Pending</option>
            <option value="{{ \App\Models\Perizinan::STATUS_DISETUJUI }}" {{ request('status') === \App\Models\Perizinan::STATUS_DISETUJUI ? 'selected' : '' }}>Disetujui</option>
            <option value="{{ \App\Models\Perizinan::STATUS_DITOLAK }}" {{ request('status') === \App\Models\Perizinan::STATUS_DITOLAK ? 'selected' : '' }}>Ditolak</option>
            <option value="{{ \App\Models\Perizinan::STATUS_KEMBALI }}" {{ request('status') === \App\Models\Perizinan::STATUS_KEMBALI ? 'selected' : '' }}>Kembali</option>
        </select>
        @if(!$isRequester)
            <input type="date" name="from" value="{{ request('from') }}" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300">
            <input type="date" name="to" value="{{ request('to') }}" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300">
        @endif
        <button type="submit" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
            <i class="fa-solid fa-filter text-xs"></i>
        </button>
        @if(request('search') || request('jenis') || request('status') || request('from') || request('to'))
            <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.index') }}" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors" title="Reset filter">
                <i class="fa-solid fa-xmark text-xs"></i>
            </a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    @if($perizinan->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-16 px-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-file-signature text-gray-400 dark:text-gray-500 text-xl"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada data perizinan</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                Belum ada pengajuan izin santri.
            </p>
            @can('create', App\Models\Perizinan::class)
                <x-btn href="{{ tenant_route('dashboard.kepesantrenan.perizinan.create') }}" variant="primary" size="sm" icon="fa-plus">
                    Ajukan Izin
                </x-btn>
            @endcan
        </div>
    @else
        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        @if(!$isRequester)
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Santri</th>
                        @endif
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
                        @if(!$isRequester)
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[12px] font-bold shrink-0">
                                        {{ strtoupper(substr($p->santri->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100 truncate">{{ $p->santri->name ?? 'Unknown' }}</p>
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500 font-mono truncate">{{ $p->santri->nis ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                        @endif
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
                            <span class="text-[13px] text-gray-700 dark:text-gray-300 truncate max-w-[150px] block" title="{{ $p->alasan }}">{{ $p->alasan }}</span>
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
                                @if($p->status === \App\Models\Perizinan::STATUS_PENDING)
                                    @can('update', $p)
                                        <a href="{{ tenant_route('dashboard.kepesantrenan.perizinan.edit', ['perizinan' => $p->id]) }}"
                                           class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors"
                                           title="Edit">
                                            <i class="fa-solid fa-pen text-[11px]"></i>
                                        </a>
                                    @endcan
                                @endif
                                @can('delete', $p)
                                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.perizinan.destroy', ['perizinan' => $p->id]) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus pengajuan ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-md transition-colors"
                                                title="Hapus">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                        </button>
                                    </form>
                                @endcan
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
                Menampilkan {{ $perizinan->firstItem() ?? 0 }} - {{ $perizinan->lastItem() ?? 0 }} dari {{ $perizinan->total() }} pengajuan
            </p>
            <div class="flex gap-1">
                {{ $perizinan->links('pagination::simple-tailwind') }}
            </div>
        </div>
    @endif
</div>

@endsection
