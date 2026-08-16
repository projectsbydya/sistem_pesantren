@extends('layouts.tenant')

@section('title', 'Mutasi Kamar')
@section('page-title', 'Mutasi Kamar')
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Mutasi Kamar</span>
@endsection

@section('content')

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Mutasi Kamar</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            Audit log perpindahan santri antar kamar
        </p>
    </div>
</div>

{{-- Statistics Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Total Mutasi</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $statistics['total_mutasi'] ?? 0 }}</p>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Perpindahan Aktif</p>
        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $mutasi->total() }}</p>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 md:col-span-2">
        <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase mb-2">Top Alasan Mutasi</p>
        @if(!empty($statistics['by_alasan']))
            <div class="flex flex-wrap gap-2">
                @foreach($statistics['by_alasan'] as $alasan => $count)
                    <span class="px-2 py-1 bg-gray-100 dark:bg-gray-800 rounded text-[11px] text-gray-600 dark:text-gray-400">
                        {{ $alasan }} ({{ $count }})
                    </span>
                @endforeach
            </div>
        @else
            <p class="text-[12px] text-gray-400 dark:text-gray-500">Belum ada data</p>
        @endif
    </div>
</div>

{{-- Search & Filters --}}
<form method="GET" action="{{ tenant_route('dashboard.kepesantrenan.mutasi.index') }}" class="flex flex-col lg:flex-row gap-3 mb-5">
    <div class="flex-1">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama santri atau NIS..."
                   class="w-full pl-9 pr-4 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                          focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors">
        </div>
    </div>
    <div class="flex gap-2 flex-wrap">
        <select name="kamar_id" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                       text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
            <option value="">Semua Kamar</option>
            @foreach($kamarList as $k)
                <option value="{{ $k->id }}" {{ request('kamar_id') == $k->id ? 'selected' : '' }}>{{ $k->name }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}"
               class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                      text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors"
               placeholder="Dari">
        <input type="date" name="to" value="{{ request('to') }}"
               class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                      text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors"
               placeholder="Sampai">
        <button type="submit" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
            <i class="fa-solid fa-filter text-xs"></i>
        </button>
        @if(request('search') || request('kamar_id') || request('from') || request('to'))
            <a href="{{ tenant_route('dashboard.kepesantrenan.mutasi.index') }}" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors" title="Reset filter">
                <i class="fa-solid fa-xmark text-xs"></i>
            </a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    @if($mutasi->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-16 px-6">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-people-arrows text-gray-400 dark:text-gray-500 text-xl"></i>
            </div>
            <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada mutasi</h3>
            <p class="text-[13px] text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                Belum ada catatan perpindahan santri antar kamar. Mutasi akan tercatat otomatis saat santri dipindahkan.
            </p>
        </div>
    @else
        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Santri</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Dari Kamar</th>
                        <th class="px-4 py-3 text-center text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider"></th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Ke Kamar</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Alasan</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mutasi as $m)
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group">
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">
                                {{ $m->tanggal_mutasi?->format('d M Y') ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[12px] font-bold shrink-0">
                                    {{ strtoupper(substr($m->santri->name ?? 'S', 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100 truncate">{{ $m->santri->name ?? 'Unknown' }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-mono truncate">{{ $m->santri->nis ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-bed text-gray-400 dark:text-gray-500 text-xs"></i>
                                <span class="text-[13px] text-gray-700 dark:text-gray-300">{{ $m->kamarAsal->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <i class="fa-solid fa-arrow-right text-emerald-500 dark:text-emerald-400 text-sm"></i>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-bed text-emerald-500 dark:text-emerald-400 text-xs"></i>
                                <span class="text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $m->kamarTujuan->name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                                {{ $m->alasan }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ tenant_route('dashboard.kepesantrenan.mutasi.show', ['mutasi' => $m->id]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                   title="Lihat Detail">
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
                Menampilkan {{ $mutasi->firstItem() ?? 0 }} - {{ $mutasi->lastItem() ?? 0 }} dari {{ $mutasi->total() }} mutasi
            </p>
            <div class="flex gap-1">
                {{ $mutasi->links('pagination::simple-tailwind') }}
            </div>
        </div>
    @endif
</div>

@endsection
