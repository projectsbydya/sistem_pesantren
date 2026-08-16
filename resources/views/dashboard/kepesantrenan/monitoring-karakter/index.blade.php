@extends('layouts.tenant')

@section('title', 'Monitoring Karakter')
@section('page-title', 'Monitoring Karakter')
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Monitoring Karakter</span>
@endsection

@section('content')

@php
$aspekLabels = [
    'akhlak' => 'Akhlak',
    'disiplin' => 'Disiplin',
    'tanggung_jawab' => 'Tanggung Jawab',
    'kerja_sama' => 'Kerja Sama',
    'kejujuran' => 'Kejujuran',
    'kemandirian' => 'Kemandirian',
];
@endphp

{{-- Statistics Cards --}}
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">Total</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $statistics['total'] }}</p>
    </div>
    @foreach($statistics['by_aspek'] as $aspek => $count)
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase">{{ $aspekLabels[$aspek] ?? $aspek }}</p>
        <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $count }}</p>
    </div>
    @endforeach
</div>

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Monitoring Karakter</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            Penilaian karakter dan perilaku santri
        </p>
    </div>
    @can('create', App\Models\MonitoringKarakter::class)
        <x-btn href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.create') }}" variant="primary" icon="fa-plus">
            Tambah Penilaian
        </x-btn>
    @endcan
</div>

{{-- Search & Filters --}}
<form method="GET" action="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.index') }}" class="flex flex-col lg:flex-row gap-3 mb-5">
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
        <select name="aspek" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                       text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
            <option value="">Semua Aspek</option>
            <option value="akhlak" {{ request('aspek') === 'akhlak' ? 'selected' : '' }}>Akhlak</option>
            <option value="disiplin" {{ request('aspek') === 'disiplin' ? 'selected' : '' }}>Disiplin</option>
            <option value="tanggung_jawab" {{ request('aspek') === 'tanggung_jawab' ? 'selected' : '' }}>Tanggung Jawab</option>
            <option value="kerja_sama" {{ request('aspek') === 'kerja_sama' ? 'selected' : '' }}>Kerja Sama</option>
            <option value="kejujuran" {{ request('aspek') === 'kejujuran' ? 'selected' : '' }}>Kejujuran</option>
            <option value="kemandirian" {{ request('aspek') === 'kemandirian' ? 'selected' : '' }}>Kemandirian</option>
        </select>
        <select name="periode" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                       text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
            <option value="">Semua Periode</option>
            <option value="mingguan" {{ request('periode') === 'mingguan' ? 'selected' : '' }}>Mingguan</option>
            <option value="bulanan" {{ request('periode') === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
            <option value="semester" {{ request('periode') === 'semester' ? 'selected' : '' }}>Semester</option>
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300">
        <input type="date" name="to" value="{{ request('to') }}" class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-700 dark:text-gray-300">
        <button type="submit" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
            <i class="fa-solid fa-filter text-xs"></i>
        </button>
        @if(request('search') || request('aspek') || request('periode') || request('from') || request('to'))
            <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.index') }}" class="px-3 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors" title="Reset filter">
                <i class="fa-solid fa-xmark text-xs"></i>
            </a>
        @endif
    </div>
</form>

{{-- Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    @if($monitoring->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-16 px-6">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-heart-pulse text-gray-400 dark:text-gray-500 text-xl"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada data penilaian</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                Mulai dengan menambahkan penilaian karakter santri.
            </p>
            @can('create', App\Models\MonitoringKarakter::class)
                <x-btn href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.create') }}" variant="primary" size="sm" icon="fa-plus">
                    Tambah Penilaian
                </x-btn>
            @endcan
        </div>
    @else
        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Santri</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aspek</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Skor</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Predikat</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Periode</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($monitoring as $m)
                    @php
                        $predikatVariant = match($m->predikat) {
                            'sangat_baik' => 'success',
                            'baik' => 'info',
                            'cukup' => 'warning',
                            'kurang' => 'danger',
                            default => 'default',
                        };
                        $predikatLabel = match($m->predikat) {
                            'sangat_baik' => 'Sangat Baik',
                            'baik' => 'Baik',
                            'cukup' => 'Cukup',
                            'kurang' => 'Kurang',
                            default => $m->predikat,
                        };
                        $skorColor = $m->skor >= 85 ? 'text-emerald-600 dark:text-emerald-400' : ($m->skor >= 70 ? 'text-blue-600 dark:text-blue-400' : ($m->skor >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400'));
                    @endphp
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group">
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400">{{ $m->tanggal?->format('d M Y') ?? '-' }}</span>
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
                            <span class="text-[13px] text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{ $aspekLabels[$m->aspek] ?? $m->aspek }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-[16px] font-bold {{ $skorColor }}">{{ $m->skor }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $predikatVariant }}" size="sm">{{ $predikatLabel }}</x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[12px] text-gray-600 dark:text-gray-400 capitalize">{{ $m->periode ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.show', ['monitoring' => $m->id]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                   title="Lihat">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                </a>
                                @can('update', $m)
                                    <a href="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.edit', ['monitoring' => $m->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors"
                                       title="Edit">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </a>
                                @endcan
                                @can('delete', $m)
                                    <form method="POST" action="{{ tenant_route('dashboard.kepesantrenan.monitoring-karakter.destroy', ['monitoring' => $m->id]) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus penilaian ini?')" class="inline">
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
                Menampilkan {{ $monitoring->firstItem() ?? 0 }} - {{ $monitoring->lastItem() ?? 0 }} dari {{ $monitoring->total() }} penilaian
            </p>
            <div class="flex gap-1">
                {{ $monitoring->links('pagination::simple-tailwind') }}
            </div>
        </div>
    @endif
</div>

@endsection
