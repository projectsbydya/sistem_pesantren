@extends('layouts.tenant')

@section('title', 'Detail Pembayaran')

@php
$statusLabels = \App\Models\BillPayment::STATUS_LABELS;
$methodLabels = \App\Models\BillPayment::METHOD_LABELS;
$statusColors = \App\Models\BillPayment::STATUS_COLORS;
$billStatusLabels = \App\Models\Bill::STATUS_LABELS;
$billStatusColors = \App\Models\Bill::STATUS_COLORS;
$color = $statusColors[$payment->status] ?? 'gray';
@endphp

@section('content')
<div class="max-w-3xl mx-auto">

<div class="mb-6">
    <a href="{{ tenant_route('dashboard.payments.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">Detail Pembayaran</h1>
</div>

<div class="space-y-5">
    {{-- Status header --}}
    <x-card>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status Pembayaran</p>
                <x-badge variant="{{ $color === 'emerald' ? 'success' : ($color === 'rose' ? 'danger' : 'warning') }}" size="lg" dot>
                    {{ $statusLabels[$payment->status] ?? $payment->status }}
                </x-badge>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-sm text-gray-500 dark:text-gray-400">Jumlah</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
            </div>
        </div>
    </x-card>

    {{-- Detail grid --}}
    <x-card title="Informasi Pembayaran">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Santri</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $payment->santri?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Jenis Tagihan</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $billStatusLabels[$payment->bill?->type] ?? ($payment->bill?->type ?? '-') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Metode Pembayaran</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $methodLabels[$payment->payment_method] ?? $payment->payment_method }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Tanggal Pembayaran</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $payment->payment_date?->format('d/m/Y') ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Nomor Referensi</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $payment->reference_number ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Diajukan Pada</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $payment->submitted_at?->format('d/m/Y H:i') ?? '-' }}</dd>
            </div>
            @if($payment->verified_at)
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Diverifikasi Oleh</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $payment->verifiedBy?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500 dark:text-gray-400">Tanggal Verifikasi</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-100">{{ $payment->verified_at?->format('d/m/Y H:i') ?? '-' }}</dd>
            </div>
            @endif
        </dl>

        @if($payment->notes)
        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Catatan</p>
            <p class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $payment->notes }}</p>
        </div>
        @endif

        @if($payment->rejection_reason)
        <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
            <p class="text-sm text-rose-500 dark:text-rose-400 mb-1">Alasan Penolakan</p>
            <p class="text-sm text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $payment->rejection_reason }}</p>
        </div>
        @endif
    </x-card>

    {{-- Bill status --}}
    <x-card title="Status Tagihan Terkait">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Tagihan {{ \App\Models\Bill::TYPE_LABELS[$payment->bill?->type] ?? ($payment->bill?->type ?? '-') }}</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 mt-0.5">
                    Rp {{ number_format((float) ($payment->bill?->paid_amount ?? 0), 0, ',', '.') }} / Rp {{ number_format((float) ($payment->bill?->amount ?? 0), 0, ',', '.') }}
                </p>
            </div>
            @php $billColor = $billStatusColors[$payment->bill?->status] ?? 'gray'; @endphp
            <x-badge variant="{{ $billColor === 'emerald' ? 'success' : ($billColor === 'rose' ? 'danger' : 'warning') }}" size="md" dot>
                {{ $billStatusLabels[$payment->bill?->status] ?? ($payment->bill?->status ?? '-') }}
            </x-badge>
        </div>
    </x-card>

    {{-- Proof --}}
    @if($payment->transfer_proof)
        <x-card title="Bukti Pembayaran">
            @if($payment->isDigital())
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Bukti diunggah oleh pemohon.</p>
            @endif
            @if(Str::endsWith(strtolower($payment->transfer_proof), ['.pdf']))
                <a href="{{ Storage::disk('public')->url($payment->transfer_proof) }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                    <i class="fa-solid fa-file-pdf"></i> Lihat PDF
                </a>
            @else
                <a href="{{ Storage::disk('public')->url($payment->transfer_proof) }}" target="_blank" rel="noopener noreferrer">
                    <img src="{{ Storage::disk('public')->url($payment->transfer_proof) }}" alt="Bukti pembayaran" class="max-h-64 rounded-lg border border-gray-200 dark:border-gray-800">
                </a>
            @endif
        </x-card>
    @elseif($payment->isManual())
        <x-card title="Bukti Pembayaran Manual">
            @can('uploadProof', $payment)
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Pembayaran manual belum memiliki bukti. Unggah foto/kwitansi sebagai bukti pembayaran.</p>
                <form method="POST" action="{{ tenant_route('dashboard.payments.proof', ['payment' => $payment->id]) }}" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                    @csrf
                    <input type="file" name="proof" accept="image/*,application/pdf" required
                           class="block w-full sm:w-auto text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-emerald-500/10 dark:file:text-emerald-400">
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                        Unggah Bukti
                    </button>
                </form>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">Bukti pembayaran manual belum diunggah.</p>
            @endcan
        </x-card>
    @endif

    {{-- Admin actions --}}
    @if($payment->isPending())
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
        @can('reject', $payment)
        <form method="POST" action="{{ tenant_route('dashboard.payments.reject', ['payment' => $payment->id]) }}" class="flex-1 sm:flex-initial">
            @csrf
            <input type="text" name="rejection_reason" required placeholder="Alasan penolakan"
                   class="w-full sm:w-auto text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 focus:ring-2 focus:ring-rose-500 mb-2 sm:mb-0 sm:mr-2">
            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-rose-600 text-white text-sm font-semibold rounded-lg hover:bg-rose-700 transition-colors">
                Tolak
            </button>
        </form>
        @endcan
        @can('approve', $payment)
        <form method="POST" action="{{ tenant_route('dashboard.payments.verify', ['payment' => $payment->id]) }}" class="flex-1 sm:flex-initial">
            @csrf
            <button type="submit" class="w-full px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                Setujui Pembayaran
            </button>
        </form>
        @endcan
    </div>
    @endif
</div>

</div>
@endsection
