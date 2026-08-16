@extends('layouts.tenant')

@section('title', 'Tagihan')

@php
$subtitle = Gate::allows('create', App\Models\Bill::class)
    ? 'Manajemen tagihan SPP dan pembayaran santri'
    : 'Daftar tagihan dan status pembayaran';
@endphp

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Tagihan</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">{{ $subtitle }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ tenant_route('dashboard.payments.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <i class="fa-solid fa-file-invoice-dollar text-emerald-600"></i> Riwayat Pembayaran
        </a>
        @can('create', App\Models\Bill::class)
        <a href="{{ tenant_route('dashboard.spp.export', request()->query()) }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-semibold rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <i class="fa-solid fa-file-excel text-emerald-600"></i> Export Excel
        </a>
        <a href="{{ tenant_route('dashboard.spp.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
            <i class="fa-solid fa-plus"></i> Buat Tagihan
        </a>
        @endcan
    </div>
</div>

{{-- Stats -- admin only --}}
@can('create', App\Models\Bill::class)
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card title="Total Tunggakan" value="Rp {{ number_format($totalUnpaid, 0, ',', '.') }}" icon="fa-money-bill-trend-up" color="rose" />
    <x-stat-card title="Diterima Bulan Ini" value="Rp {{ number_format($totalPaidThisMonth, 0, ',', '.') }}" icon="fa-hand-holding-dollar" color="emerald" />
    <x-stat-card title="Santri Belum Lunas" value="{{ $countUnpaid }}" icon="fa-user-clock" color="amber" />
    <x-stat-card title="Lunas Bulan Ini" value="{{ $countPaidThisMonth }}" icon="fa-circle-check" color="blue" />
</div>
@endcan

{{-- Filters --}}
<x-card class="mb-5">
    <form method="GET" class="flex flex-col md:flex-row gap-3">
        @can('create', App\Models\Bill::class)
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
        @endcan
        <select name="type" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
            <option value="">Semua Jenis</option>
            @foreach(\App\Models\Bill::TYPE_LABELS as $val => $label)
                <option value="{{ $val }}" {{ request('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
            <option value="">Semua Status</option>
            @foreach(\App\Models\Bill::STATUS_LABELS as $val => $label)
                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">Filter</button>
        @if(request()->hasAny(['santri_id','kelas_id','type','status']))
            <a href="{{ tenant_route('dashboard.spp.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Reset</a>
        @endif
    </form>
</x-card>

<x-card title="Daftar Tagihan">
    @if($bills->isEmpty())
        @can('create', App\Models\Bill::class)
            <x-empty-state
                title="Belum ada tagihan"
                message="Buat tagihan baru untuk memulai."
                icon="fa-file-invoice"
                :cta-route="'dashboard.spp.create'"
                cta-text="Buat Tagihan"
            />
        @else
            <x-empty-state
                title="Belum ada tagihan"
                message="Saat ini tidak ada tagihan yang perlu ditangani."
                icon="fa-file-invoice"
            />
        @endcan
    @else
        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Santri</th>
                        @can('create', App\Models\Bill::class)
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">No HP Ortu</th>
                        @endcan
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Jenis</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Jatuh Tempo</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Jumlah</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Dibayar</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($bills as $bill)
                        @php
                            $firstParent = $bill->santri?->parents?->first();
                            $billColor = \App\Models\Bill::STATUS_COLORS[$bill->status] ?? 'gray';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-sm font-bold flex-shrink-0">
                                        {{ strtoupper(substr($bill->santri?->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $bill->santri?->name ?? '-' }}</p>
                                        @can('create', App\Models\Bill::class)
                                            @if($firstParent)
                                                <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ $firstParent->name }}</p>
                                            @endif
                                        @endcan
                                    </div>
                                </div>
                            </td>
                            @can('create', App\Models\Bill::class)
                                <td class="px-4 py-3 text-sm">
                                    @if(isset($waUrls[$bill->id]))
                                        <a href="{{ $waUrls[$bill->id] }}" target="_blank" rel="noopener noreferrer"
                                           class="inline-flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 transition-colors">
                                            <i class="fa-brands fa-whatsapp text-base"></i>
                                            <span class="text-xs">{{ $firstParent?->phone }}</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                                    @endif
                                </td>
                            @endcan
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                {{ \App\Models\Bill::TYPE_LABELS[$bill->type] ?? $bill->type }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                {{ $bill->due_date?->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-gray-100 text-sm">
                                Rp {{ number_format((float) $bill->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                                Rp {{ number_format((float) $bill->paid_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                <x-badge variant="{{ $billColor === 'emerald' ? 'success' : ($billColor === 'rose' ? 'danger' : 'warning') }}" size="sm" dot>
                                    {{ \App\Models\Bill::STATUS_LABELS[$bill->status] ?? $bill->status }}
                                </x-badge>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ tenant_route('dashboard.spp.show', ['bill' => $bill->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors rounded" title="Detail">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </a>

                                    @can('create', [App\Models\BillPayment::class, $bill])
                                        <a href="{{ tenant_route('dashboard.payments.create', ['bill_id' => $bill->id]) }}"
                                           class="px-2 py-1 bg-emerald-600 text-white text-xs font-semibold rounded-md hover:bg-emerald-700 transition-colors">
                                            Bayar
                                        </a>
                                    @endcan

                                    @can('update', $bill)
                                    <a href="{{ tenant_route('dashboard.spp.edit', ['bill' => $bill->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors rounded" title="Edit">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>
                                    @endcan
                                    @can('delete', $bill)
                                    <form method="POST" action="{{ tenant_route('dashboard.spp.destroy', ['bill' => $bill->id]) }}" class="inline"
                                          onsubmit="return confirm('Hapus tagihan ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 transition-colors rounded" title="Hapus">
                                            <i class="fa-solid fa-trash text-xs"></i>
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

        {{-- Mobile cards --}}
        <div class="md:hidden space-y-3">
            @foreach($bills as $bill)
                @php
                    $firstParent = $bill->santri?->parents?->first();
                    $billColor = \App\Models\Bill::STATUS_COLORS[$bill->status] ?? 'gray';
                @endphp
                <div class="border border-gray-200 dark:border-gray-800 rounded-lg p-4 bg-white dark:bg-gray-900">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-sm font-bold flex-shrink-0">
                                {{ strtoupper(substr($bill->santri?->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-gray-100 text-sm">{{ $bill->santri?->name ?? '-' }}</p>
                                @can('create', App\Models\Bill::class)
                                    @if($firstParent)
                                        <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ $firstParent->name }}</p>
                                    @endif
                                @endcan
                            </div>
                        </div>
                        <x-badge variant="{{ $billColor === 'emerald' ? 'success' : ($billColor === 'rose' ? 'danger' : 'warning') }}" size="sm" dot>
                            {{ \App\Models\Bill::STATUS_LABELS[$bill->status] ?? $bill->status }}
                        </x-badge>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-400 mb-3">
                        <div>Jenis</div>
                        <div class="text-right">{{ \App\Models\Bill::TYPE_LABELS[$bill->type] ?? $bill->type }}</div>
                        <div>Jatuh Tempo</div>
                        <div class="text-right">{{ $bill->due_date?->format('d/m/Y') }}</div>
                        <div>Jumlah</div>
                        <div class="text-right font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format((float) $bill->amount, 0, ',', '.') }}</div>
                        <div>Dibayar</div>
                        <div class="text-right">Rp {{ number_format((float) $bill->paid_amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ tenant_route('dashboard.spp.show', ['bill' => $bill->id]) }}"
                           class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-700 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">Detail</a>
                        @can('create', [App\Models\BillPayment::class, $bill])
                            <a href="{{ tenant_route('dashboard.payments.create', ['bill_id' => $bill->id]) }}"
                               class="px-2 py-1 text-xs bg-emerald-600 text-white rounded-md hover:bg-emerald-700">Bayar</a>
                        @endcan
                        @can('update', $bill)
                        <a href="{{ tenant_route('dashboard.spp.edit', ['bill' => $bill->id]) }}" class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-700 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">Edit</a>
                        @endcan
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $bills->links() }}</div>
    @endif
</x-card>

<script>
    // Kelas → Santri filter narrowing. Selecting a Kelas limits the Santri
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

@endsection
