@extends('layouts.tenant')

@section('title', 'Detail Tagihan')

@php
$statusLabels = \App\Models\Bill::STATUS_LABELS;
$statusColors = \App\Models\Bill::STATUS_COLORS;
$paymentStatusLabels = \App\Models\BillPayment::STATUS_LABELS;
$paymentStatusColors = \App\Models\BillPayment::STATUS_COLORS;
$methodLabels = \App\Models\BillPayment::METHOD_LABELS;
$billColor = $statusColors[$bill->status] ?? 'gray';
$outstanding = max(0, (float) $bill->amount - (float) $bill->paid_amount);
@endphp

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ tenant_route('dashboard.spp.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Tagihan
        </a>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">Detail Tagihan</h1>
    </div>

    <div class="space-y-5">
        {{-- Bill summary --}}
        <x-card title="Informasi Tagihan">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-5">
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Santri</p>
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $bill->santri?->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Jenis Tagihan</p>
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ \App\Models\Bill::TYPE_LABELS[$bill->type] ?? $bill->type }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Jatuh Tempo</p>
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $bill->due_date?->format('d/m/Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500 dark:text-gray-400">Status</p>
                    <x-badge variant="{{ $billColor === 'emerald' ? 'success' : ($billColor === 'rose' ? 'danger' : 'warning') }}" size="sm" dot>
                        {{ $statusLabels[$bill->status] ?? $bill->status }}
                    </x-badge>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Total Tagihan</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format((float) $bill->amount, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Sisa Bayar</p>
                    <p class="text-lg font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format($outstanding, 0, ',', '.') }}</p>
                </div>
            </div>
        </x-card>

        {{-- Payment history --}}
        <x-card title="Riwayat Pembayaran">
            @if($bill->billPayments->isEmpty())
                <x-empty-state
                    title="Belum ada pembayaran"
                    message="Pembayaran yang diajukan untuk tagihan ini akan muncul di sini."
                    icon="fa-file-invoice-dollar"
                />
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Metode</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Jumlah</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Catatan</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Bukti</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($bill->billPayments as $payment)
                                @php
                                    $color = $paymentStatusColors[$payment->status] ?? 'gray';
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $payment->submitted_at?->format('d/m/Y H:i') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $methodLabels[$payment->payment_method] ?? $payment->payment_method }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">
                                        Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-badge variant="{{ $color === 'emerald' ? 'success' : ($color === 'rose' ? 'danger' : 'warning') }}" size="sm" dot>
                                            {{ $paymentStatusLabels[$payment->status] ?? $payment->status }}
                                        </x-badge>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                        {{ $payment->notes ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($payment->transfer_proof)
                                            @if(\Illuminate\Support\Str::endsWith(strtolower($payment->transfer_proof), ['.pdf']))
                                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($payment->transfer_proof) }}" target="_blank" rel="noopener noreferrer"
                                                   class="inline-flex items-center gap-1 text-xs text-emerald-600 hover:text-emerald-700 font-medium">
                                                    <i class="fa-solid fa-file-pdf"></i> PDF
                                                </a>
                                            @else
                                                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($payment->transfer_proof) }}" target="_blank" rel="noopener noreferrer">
                                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($payment->transfer_proof) }}" alt="Bukti" class="h-10 w-10 rounded object-cover border border-gray-200 dark:border-gray-800 mx-auto">
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ tenant_route('dashboard.payments.show', ['payment' => $payment->id]) }}"
                                           class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-emerald-700 bg-emerald-50 dark:bg-emerald-500/10 dark:text-emerald-400 rounded-md hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                                            <i class="fa-solid fa-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
</div>
@endsection
