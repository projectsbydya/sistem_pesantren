@extends('layouts.dashboard')

@section('title', 'Detail Invoice — ' . $invoice->invoice_number)
@section('page-title', 'Detail Invoice')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-super-admin.page-header
        title="Invoice {{ $invoice->invoice_number }}"
        subtitle="{{ $invoice->tenant->name ?? 'Tenant tidak diketahui' }}"
        backUrl="{{ route('dashboard.super-admin.invoices.index') }}">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                @can('markPaid', $invoice)
                    @if($invoice->isUnpaid())
                        <form method="POST" action="{{ route('dashboard.super-admin.invoices.mark-paid', $invoice) }}" class="inline">
                            @csrf
                            <x-btn type="submit" icon="fa-check" variant="success" size="sm">Tandai Lunas</x-btn>
                        </form>
                    @endif
                @endcan
                @can('cancel', $invoice)
                    @if($invoice->isUnpaid())
                        <form method="POST" action="{{ route('dashboard.super-admin.invoices.cancel', $invoice) }}" class="inline"
                              onsubmit="return confirm('Batalkan invoice ini?');">
                            @csrf
                            <x-btn type="submit" icon="fa-ban" variant="danger" size="sm">Batalkan</x-btn>
                        </form>
                    @endif
                @endcan
            </div>
        </x-slot:actions>
    </x-super-admin.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Invoice Summary -->
        <x-card class="lg:col-span-2" title="Ringkasan Invoice">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Status</span>
                    <x-badge variant="{{ $invoice->getStatusColor() }}" size="md">
                        {{ $invoice->getStatusLabel() }}
                    </x-badge>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Tenant</span>
                    <span class="font-medium text-gray-900">{{ $invoice->tenant->name ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Subscription</span>
                    <span class="font-medium text-gray-900">{{ $invoice->subscription->package_name ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Periode</span>
                    <span class="font-medium text-gray-900">{{ $invoice->period_label ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Jatuh Tempo</span>
                    <span class="font-medium {{ $invoice->isOverdue() ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $invoice->due_date->format('d M Y') }}
                        @if($invoice->isOverdue())
                            <span class="text-xs font-normal text-red-500 ml-1">(Jatuh tempo)</span>
                        @endif
                    </span>
                </div>
                @if($invoice->paid_at)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Dibayar Pada</span>
                        <span class="font-medium text-emerald-700">{{ $invoice->paid_at->format('d M Y H:i') }}</span>
                    </div>
                @endif
                @if($invoice->notes)
                    <div class="pt-4 border-t border-gray-100">
                        <span class="text-sm text-gray-500 block mb-1">Catatan</span>
                        <p class="text-sm text-gray-700">{{ $invoice->notes }}</p>
                    </div>
                @endif
            </div>
        </x-card>

        <!-- Amount Card -->
        <x-card class="bg-gradient-to-br from-emerald-50 to-white" title="Total Tagihan">
            <div class="text-center py-4">
                <p class="text-sm text-gray-500 mb-1">Jumlah yang harus dibayar</p>
                <p class="text-4xl font-bold text-gray-900">{{ $invoice->getFormattedAmount() }}</p>
                <p class="text-xs text-gray-400 mt-2">Dibuat {{ $invoice->created_at->format('d M Y') }}</p>
            </div>
        </x-card>
    </div>

    <!-- Payments -->
    <x-card title="Riwayat Pembayaran" subtitle="{{ $invoice->payments->count() }} pembayaran tercatat">
        @if($invoice->payments->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 rounded-l-lg">ID</th>
                            <th class="px-5 py-3">Metode</th>
                            <th class="px-5 py-3 text-right">Jumlah</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3">Dikonfirmasi</th>
                            <th class="px-5 py-3 rounded-r-lg text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($invoice->payments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 font-medium text-gray-900">#{{ $payment->id }}</td>
                                <td class="px-5 py-4 text-gray-600">{{ $payment->getPaymentMethodLabel() }}</td>
                                <td class="px-5 py-4 text-right font-semibold text-gray-900">{{ $payment->getFormattedAmount() }}</td>
                                <td class="px-5 py-4 text-center">
                                    <x-badge variant="{{ $payment->getStatusColor() }}" size="xs">
                                        {{ $payment->getStatusLabel() }}
                                    </x-badge>
                                </td>
                                <td class="px-5 py-4 text-gray-600">
                                    @if($payment->confirmedBy)
                                        {{ $payment->confirmedBy->name }}
                                        <div class="text-xs text-gray-400">{{ $payment->confirmed_at?->format('d M Y H:i') }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <x-btn href="{{ route('dashboard.super-admin.payments.show', $payment) }}" icon="fa-eye" variant="outline" size="xs">Detail</x-btn>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-10">
                <i class="fa-solid fa-credit-card text-4xl text-gray-300 mb-3"></i>
                <p class="text-sm text-gray-500">Belum ada pembayaran untuk invoice ini.</p>
            </div>
        @endif
    </x-card>
</div>
@endsection
