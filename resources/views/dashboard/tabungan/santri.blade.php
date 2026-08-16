@extends('layouts.tenant')

@section('title', 'Tabungan — ' . $santri->name)

@section('content')
<div class="max-w-3xl mx-auto">

<div class="mb-6">
    <a href="{{ tenant_route('dashboard.tabungan.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">Tabungan {{ $santri->name }}</h1>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $santri->nis ?? '-' }} · {{ $santri->kelas?->name ?? 'Tanpa Kelas' }}</p>
</div>

{{-- Saldo Summary --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-1">Saldo</p>
        <p class="text-xl font-bold text-emerald-700 dark:text-emerald-300">Rp {{ number_format($saldo, 0, ',', '.') }}</p>
    </div>
    <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-1">Total Setoran</p>
        <p class="text-xl font-bold text-blue-700 dark:text-blue-300">Rp {{ number_format($totalSetor, 0, ',', '.') }}</p>
    </div>
    <div class="bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 rounded-xl p-4 text-center">
        <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider mb-1">Total Penarikan</p>
        <p class="text-xl font-bold text-rose-700 dark:text-rose-300">Rp {{ number_format($totalTarik, 0, ',', '.') }}</p>
    </div>
</div>

{{-- Riwayat --}}
<x-card title="Riwayat Transaksi">
    @if($riwayat->isEmpty())
        <div class="py-10 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada transaksi tabungan untuk santri ini.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
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
                    @foreach($riwayat as $t)
                        @php $isSetor = $t->jenis === 'setor'; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $t->tanggal?->format('d/m/Y') }}</td>
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
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $t->keterangan ?: '-' }}</td>
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
    @endif
</x-card>

</div>
@endsection
