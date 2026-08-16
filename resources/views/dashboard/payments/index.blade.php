@extends('layouts.tenant')

@section('title', 'Riwayat Pembayaran')

@php
$statusLabels = \App\Models\BillPayment::STATUS_LABELS;
$methodLabels = \App\Models\BillPayment::METHOD_LABELS;
$statusColors = \App\Models\BillPayment::STATUS_COLORS;
$filterStatus = $filterStatus ?? request('status', 'all');
@endphp

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Riwayat Pembayaran</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">Riwayat dan verifikasi pembayaran tagihan santri</p>
    </div>
</div>

{{-- Status filters --}}
<div class="flex flex-wrap gap-2 mb-5">
    <a href="{{ tenant_route('dashboard.payments.index') }}"
       class="px-3 py-1.5 text-xs font-medium rounded-full border transition-colors
              {{ $filterStatus === 'all' || $filterStatus === null ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
        Semua
    </a>
    @foreach($statusLabels as $status => $label)
    <a href="{{ tenant_route('dashboard.payments.index', ['status' => $status]) }}"
       class="px-3 py-1.5 text-xs font-medium rounded-full border transition-colors
              {{ $filterStatus === $status ? 'bg-' . ($statusColors[$status] ?? 'gray') . '-600 border-' . ($statusColors[$status] ?? 'gray') . '-600 text-white' : 'bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

<x-card title="Daftar Pembayaran">
    @if($payments->isEmpty())
        <x-empty-state
            title="Belum ada pembayaran"
            message="Pembayaran yang diajukan akan muncul di sini."
            icon="fa-file-invoice-dollar"
        />
    @else
        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-800/50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Santri</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Tagihan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Jumlah</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Metode</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($payments as $payment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $payment->santri?->name ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ \App\Models\Bill::TYPE_LABELS[$payment->bill?->type] ?? ($payment->bill?->type ?? '-') }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">
                            Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                            {{ $methodLabels[$payment->payment_method] ?? $payment->payment_method }}
                        </td>
                        <td class="px-4 py-3">
                            @php $color = $statusColors[$payment->status] ?? 'gray'; @endphp
                            <x-badge variant="{{ $color === 'emerald' ? 'success' : ($color === 'rose' ? 'danger' : 'warning') }}" size="sm" dot>
                                {{ $statusLabels[$payment->status] ?? $payment->status }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                            {{ $payment->payment_date?->format('d/m/Y') ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ tenant_route('dashboard.payments.show', ['payment' => $payment->id]) }}"
                                   class="p-1.5 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded transition-colors" title="Detail">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>
                                @if($payment->isPending())
                                    @can('update', $payment)
                                    <a href="{{ tenant_route('dashboard.payments.edit', ['payment' => $payment->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 rounded transition-colors" title="Edit">
                                        <i class="fa-solid fa-pen text-xs"></i>
                                    </a>
                                    @endcan
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden space-y-3">
            @foreach($payments as $payment)
            <div class="border border-gray-200 dark:border-gray-800 rounded-lg p-4 bg-white dark:bg-gray-900">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $payment->santri?->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ \App\Models\Bill::TYPE_LABELS[$payment->bill?->type] ?? ($payment->bill?->type ?? '-') }}</p>
                    </div>
                    @php $color = $statusColors[$payment->status] ?? 'gray'; @endphp
                    <x-badge variant="{{ $color === 'emerald' ? 'success' : ($color === 'rose' ? 'danger' : 'warning') }}" size="sm" dot>
                        {{ $statusLabels[$payment->status] ?? $payment->status }}
                    </x-badge>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-400 mb-3">
                    <div>Jumlah</div>
                    <div class="text-right font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</div>
                    <div>Metode</div>
                    <div class="text-right">{{ $methodLabels[$payment->payment_method] ?? $payment->payment_method }}</div>
                    <div>Tanggal</div>
                    <div class="text-right">{{ $payment->payment_date?->format('d/m/Y') ?? '-' }}</div>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <a href="{{ tenant_route('dashboard.payments.show', ['payment' => $payment->id]) }}"
                       class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-700 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">Detail</a>
                    @if($payment->isPending())
                        @can('update', $payment)
                        <a href="{{ tenant_route('dashboard.payments.edit', ['payment' => $payment->id]) }}"
                           class="px-2 py-1 text-xs border border-gray-300 dark:border-gray-700 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800">Edit</a>
                        @endcan
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $payments->links() }}</div>
    @endif
</x-card>
@endsection
