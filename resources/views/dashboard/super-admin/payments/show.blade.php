@extends('layouts.dashboard')

@section('title', 'Detail Pembayaran #' . $payment->id)
@section('page-title', 'Detail Pembayaran')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-super-admin.page-header
        title="Pembayaran #{{ $payment->id }}"
        subtitle="Invoice {{ $payment->invoice->invoice_number ?? '-' }} — {{ $payment->invoice->tenant->name ?? '-' }}"
        backUrl="{{ route('dashboard.super-admin.payments.index') }}">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                @can('confirm', $payment)
                    @if($payment->isPending())
                        <form method="POST" action="{{ route('dashboard.super-admin.payments.confirm', $payment) }}" class="inline">
                            @csrf
                            <x-btn type="submit" icon="fa-check" variant="success" size="sm">Konfirmasi</x-btn>
                        </form>
                    @endif
                @endcan
                @can('reject', $payment)
                    @if($payment->isPending())
                        <form method="POST" action="{{ route('dashboard.super-admin.payments.reject', $payment) }}" class="inline">
                            @csrf
                            <x-btn type="submit" icon="fa-times" variant="warning" size="sm">Tolak</x-btn>
                        </form>
                    @endif
                @endcan
                @can('delete', $payment)
                    <form method="POST" action="{{ route('dashboard.super-admin.payments.destroy', $payment) }}" class="inline"
                          onsubmit="return confirm('Hapus pembayaran ini?');">
                        @csrf
                        @method('DELETE')
                        <x-btn type="submit" icon="fa-trash" variant="danger" size="sm">Hapus</x-btn>
                    </form>
                @endcan
            </div>
        </x-slot:actions>
    </x-super-admin.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Payment Summary -->
        <x-card class="lg:col-span-2" title="Detail Pembayaran">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Status</span>
                    <x-badge variant="{{ $payment->getStatusColor() }}" size="md">
                        {{ $payment->getStatusLabel() }}
                    </x-badge>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Metode</span>
                    <span class="font-medium text-gray-900">{{ $payment->getPaymentMethodLabel() }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Invoice</span>
                    <a href="{{ route('dashboard.super-admin.invoices.show', $payment->invoice) }}" class="font-medium text-emerald-600 hover:text-emerald-700">
                        {{ $payment->invoice->invoice_number ?? '-' }}
                    </a>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Tenant</span>
                    <span class="font-medium text-gray-900">{{ $payment->invoice->tenant->name ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Paket</span>
                    <span class="font-medium text-gray-900">{{ $payment->invoice->subscription->package_name ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Dibuat</span>
                    <span class="font-medium text-gray-900">{{ $payment->created_at->format('d M Y H:i') }}</span>
                </div>
                @if($payment->confirmedBy)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Dikonfirmasi Oleh</span>
                        <span class="font-medium text-gray-900">{{ $payment->confirmedBy->name }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Waktu Konfirmasi</span>
                        <span class="font-medium text-gray-900">{{ $payment->confirmed_at?->format('d M Y H:i') ?? '-' }}</span>
                    </div>
                @endif
                @if($payment->transfer_proof)
                    <div class="pt-4 border-t border-gray-100">
                        <span class="text-sm text-gray-500 block mb-1">Bukti / Referensi</span>
                        <p class="text-sm font-mono bg-gray-50 p-2 rounded text-gray-700 break-all">{{ $payment->transfer_proof }}</p>
                    </div>
                @endif
                @if($payment->notes)
                    <div class="pt-4 border-t border-gray-100">
                        <span class="text-sm text-gray-500 block mb-1">Catatan</span>
                        <p class="text-sm text-gray-700">{{ $payment->notes }}</p>
                    </div>
                @endif
            </div>
        </x-card>

        <!-- Amount Card -->
        <x-card class="bg-gradient-to-br from-emerald-50 to-white" title="Jumlah Pembayaran">
            <div class="text-center py-6">
                <p class="text-sm text-gray-500 mb-1">Total</p>
                <p class="text-4xl font-bold text-gray-900">{{ $payment->getFormattedAmount() }}</p>
                <p class="text-xs text-gray-400 mt-3">Status saat ini</p>
                <x-badge variant="{{ $payment->getStatusColor() }}" size="lg" class="mt-2">
                    {{ $payment->getStatusLabel() }}
                </x-badge>
            </div>
        </x-card>
    </div>

    @can('confirm', $payment)
        @if($payment->isPending())
            <x-card title="Aksi Konfirmasi" subtitle="Setujui atau tolak pembayaran ini.">
                <div class="flex flex-col sm:flex-row gap-3">
                    <form method="POST" action="{{ route('dashboard.super-admin.payments.confirm', $payment) }}" class="flex-1">
                        @csrf
                        <x-btn type="submit" icon="fa-check" variant="success" class="w-full justify-center">Konfirmasi Pembayaran</x-btn>
                    </form>
                    <form method="POST" action="{{ route('dashboard.super-admin.payments.reject', $payment) }}" class="flex-1">
                        @csrf
                        <x-btn type="submit" icon="fa-times" variant="warning" class="w-full justify-center">Tolak Pembayaran</x-btn>
                    </form>
                </div>
            </x-card>
        @endif
    @endcan
</div>
@endsection
