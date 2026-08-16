@extends('layouts.tenant')

@php
$isAdminView = Gate::allows('create', App\Models\Tabungan::class);
$subtitle = $isAdminView
    ? 'Manajemen setoran dan penarikan tabungan santri'
    : 'Riwayat setoran, penarikan, dan saldo tabungan';
@endphp

@section('title', $isAdminView ? 'Tabungan Santri' : 'Tabungan')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $isAdminView ? 'Tabungan Santri' : 'Tabungan' }}</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $subtitle }}</p>
    </div>
    @can('create', App\Models\Tabungan::class)
    <div class="flex items-center gap-2">
        <a href="{{ tenant_route('dashboard.tabungan.export', request()->query()) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <i class="fa-solid fa-file-excel text-emerald-600"></i> Export Excel
        </a>
        <a href="{{ tenant_route('dashboard.tabungan.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
            <i class="fa-solid fa-plus"></i> Catat Transaksi
        </a>
    </div>
    @endcan
</div>

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 {{ $isAdminView ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-4 mb-6">
    <x-stat-card title="Total Saldo" value="Rp {{ number_format($totalSaldo, 0, ',', '.') }}" icon="fa-piggy-bank" color="emerald" />
    <x-stat-card title="Total Setoran" value="Rp {{ number_format($totalSetor, 0, ',', '.') }}" icon="fa-arrow-down" color="blue" />
    <x-stat-card title="Total Penarikan" value="Rp {{ number_format($totalTarik, 0, ',', '.') }}" icon="fa-arrow-up" color="amber" />
    @can('create', App\Models\Tabungan::class)
    <x-stat-card title="Santri Menabung" value="{{ $jumlahSantri }}" icon="fa-users" color="purple" />
    @endcan
</div>

{{-- Filter --}}
<x-card class="mb-5">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        @can('create', App\Models\Tabungan::class)
        <div class="flex-1">
            <select name="kelas_id" id="filter_kelas_id" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <option value="">Semua Kelas</option>
                @foreach($kelasList as $kelas)
                    <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}
                            data-santri="{{ base64_encode(json_encode($kelas->santri->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values())) }}">
                        {{ $kelas->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex-1">
            <select name="santri_id" id="filter_santri_id" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <option value="">Semua Santri</option>
                @foreach($santriList as $santri)
                    <option value="{{ $santri->id }}" {{ request('santri_id') == $santri->id ? 'selected' : '' }}>{{ $santri->name }}</option>
                @endforeach
            </select>
        </div>
        @else
            @if(auth()->user()->parent && $santriList->count() > 1)
            <div class="flex-1">
                <select name="santri_id" id="filter_santri_id" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
                    @foreach($santriList as $santri)
                        <option value="{{ $santri->id }}" {{ $selectedSantriId == $santri->id ? 'selected' : '' }}>{{ $santri->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        @endcan
        <select name="jenis" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
            <option value="">Semua Jenis</option>
            <option value="setor" {{ request('jenis') === 'setor' ? 'selected' : '' }}>Setoran</option>
            <option value="tarik" {{ request('jenis') === 'tarik' ? 'selected' : '' }}>Penarikan</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">Filter</button>
        @if(request()->hasAny(['santri_id','kelas_id','jenis']))
            <a href="{{ tenant_route('dashboard.tabungan.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Reset</a>
        @endif
    </form>
</x-card>

{{-- Tabel --}}
<x-card title="Riwayat Transaksi">
    @if($tabungans->isEmpty())
        <div class="py-12 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-piggy-bank text-gray-400 dark:text-gray-500 text-2xl"></i>
            </div>
            <h3 class="text-base font-semibold text-gray-700 dark:text-gray-300 mb-1">Belum ada transaksi tabungan</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $isAdminView ? 'Catat setoran atau penarikan tabungan santri.' : 'Saat ini tidak ada riwayat transaksi tabungan.' }}
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        @can('create', App\Models\Tabungan::class)
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Santri</th>
                        @endcan
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Jenis</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Keterangan</th>
                        @can('create', App\Models\Tabungan::class)
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Aksi</th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($tabungans as $t)
                        @php $isSetor = $t->jenis === 'setor'; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            @can('create', App\Models\Tabungan::class)
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-sm font-bold flex-shrink-0">
                                        {{ strtoupper(substr($t->santri?->name ?? '?', 0, 1)) }}
                                    </div>
                                    <a href="{{ tenant_route('dashboard.tabungan.santri', ['santri' => $t->santri_id]) }}"
                                       class="font-medium text-gray-900 dark:text-gray-100 text-sm hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">
                                        {{ $t->santri?->name ?? '-' }}
                                    </a>
                                </div>
                            </td>
                            @endcan
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $t->tanggal?->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $isSetor ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-500/20 text-rose-700 dark:text-rose-400' }}">
                                    <i class="fa-solid {{ $isSetor ? 'fa-arrow-down' : 'fa-arrow-up' }} text-[10px]"></i>
                                    {{ \App\Models\Tabungan::JENIS_LABELS[$t->jenis] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-sm {{ $isSetor ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $isSetor ? '+' : '-' }} Rp {{ number_format($t->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $t->keterangan ?: '-' }}
                            </td>
                            @can('create', App\Models\Tabungan::class)
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    @can('update', $t)
                                    <a href="{{ tenant_route('dashboard.tabungan.edit', ['tabungan' => $t->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>
                                    @endcan
                                    @can('delete', $t)
                                    <form method="POST" action="{{ tenant_route('dashboard.tabungan.destroy', ['tabungan' => $t->id]) }}" class="inline"
                                          onsubmit="return confirm('Hapus transaksi ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors rounded">
                                            <i class="fa-solid fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                            @endcan
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $tabungans->links() }}</div>
    @endif
</x-card>

@can('create', App\Models\Tabungan::class)
<script>
    // Kelas -> Santri filter narrowing. Selecting a Kelas limits the Santri
    // dropdown to santri enrolled in that class; the currently selected
    // santri (if any, from the reloaded query string) is kept selected when
    // it still belongs to the chosen class.
    (function () {
        const kelasSelect = document.getElementById('filter_kelas_id');
        const santriSelect = document.getElementById('filter_santri_id');
        if (!kelasSelect || !santriSelect) return;

        const currentSantriId = '{{ request('santri_id') }}';

        kelasSelect.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            const santriList = selected.value
                ? JSON.parse(atob(selected.dataset.santri || '') || '[]')
                : @json($santriList->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values());

            santriSelect.innerHTML = '<option value="">Semua Santri</option>';
            santriList.forEach(function (s) {
                const option = document.createElement('option');
                option.value = s.id;
                option.textContent = s.name;
                if (String(s.id) === currentSantriId) option.selected = true;
                santriSelect.appendChild(option);
            });
        });
    })();
</script>
@endcan

@endsection
