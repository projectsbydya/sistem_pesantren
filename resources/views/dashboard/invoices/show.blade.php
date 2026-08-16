@extends('layouts.tenant')

@section('title', 'Invoice ' . $invoice->invoice_number)
@section('page-title', 'Detail Invoice')

@section('breadcrumb')
    Invoice
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Invoice Header Card --}}
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-800 overflow-hidden">

        {{-- Top stripe --}}
        <div class="h-1.5 w-full
            @if($invoice->status === 'paid') bg-emerald-500
            @elseif($invoice->status === 'unpaid') bg-amber-400
            @elseif($invoice->status === 'failed') bg-rose-500
            @else bg-gray-400
            @endif">
        </div>

        <div class="p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">

                {{-- Left: Invoice info --}}
                <div>
                    <p class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                        Invoice
                    </p>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $invoice->invoice_number }}
                    </h1>
                    @if($invoice->period_label)
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Periode: {{ $invoice->period_label }}
                        </p>
                    @endif
                </div>

                {{-- Right: Status badge --}}
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold
                        @if($invoice->status === 'paid') bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400
                        @elseif($invoice->status === 'unpaid') bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400
                        @elseif($invoice->status === 'failed') bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400
                        @else bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400
                        @endif">
                        <span class="w-1.5 h-1.5 rounded-full
                            @if($invoice->status === 'paid') bg-emerald-500
                            @elseif($invoice->status === 'unpaid') bg-amber-400
                            @elseif($invoice->status === 'failed') bg-rose-500
                            @else bg-gray-400
                            @endif">
                        </span>
                        {{ $invoice->getStatusLabel() }}
                    </span>
                </div>
            </div>

            {{-- Divider --}}
            <div class="my-6 border-t border-gray-100 dark:border-gray-800"></div>

            {{-- Details grid --}}
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                        Total Tagihan
                    </dt>
                    <dd class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $invoice->getFormattedAmount() }}
                    </dd>
                </div>

                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                        Jatuh Tempo
                    </dt>
                    <dd class="text-base font-semibold
                        {{ $invoice->isOverdue() ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">
                        {{ $invoice->due_date->translatedFormat('d F Y') }}
                        @if($invoice->isOverdue())
                            <span class="ml-1 text-xs font-medium text-rose-500">(Terlambat)</span>
                        @endif
                    </dd>
                </div>

                @if($invoice->subscription?->plan)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                            Paket Langganan
                        </dt>
                        <dd class="text-base font-medium text-gray-900 dark:text-white">
                            {{ $invoice->subscription->plan->name }}
                        </dd>
                    </div>
                @endif

                @if($invoice->paid_at)
                    <div>
                        <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                            Dibayar Pada
                        </dt>
                        <dd class="text-base font-medium text-gray-900 dark:text-white">
                            {{ $invoice->paid_at->translatedFormat('d F Y, H:i') }}
                        </dd>
                    </div>
                @endif

                @if($invoice->notes)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1">
                            Catatan
                        </dt>
                        <dd class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $invoice->notes }}
                        </dd>
                    </div>
                @endif
            </dl>

            {{-- Pay Now button --}}
            @if($invoice->status === 'unpaid')
                <div class="mt-8">
                    @can('pay', $invoice)
                        <form method="POST"
                              action="{{ tenant_route('dashboard.invoices.pay', ['invoice' => $invoice->id]) }}"
                              onsubmit="this.querySelector('button').disabled = true">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800
                                           text-white text-sm font-semibold rounded-xl shadow-sm transition-colors duration-150
                                           disabled:opacity-60 disabled:cursor-not-allowed">
                                <i class="fa-solid fa-credit-card text-sm"></i>
                                Bayar Sekarang
                            </button>
                        </form>
                    @endcan
                </div>
            @endif
        </div>
    </div>

    {{-- Payment History --}}
    @if($invoice->payments->isNotEmpty())
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm ring-1 ring-gray-200 dark:ring-gray-800">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                    Riwayat Pembayaran
                </h2>
            </div>

            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($invoice->payments as $payment)
                    <li class="px-6 py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0
                                @if($payment->status === 'confirmed') bg-emerald-50 dark:bg-emerald-500/10
                                @elseif($payment->status === 'rejected') bg-rose-50 dark:bg-rose-500/10
                                @else bg-amber-50 dark:bg-amber-500/10
                                @endif">
                                <i class="fa-solid text-sm
                                    @if($payment->status === 'confirmed') fa-check text-emerald-500
                                    @elseif($payment->status === 'rejected') fa-xmark text-rose-500
                                    @else fa-clock text-amber-500
                                    @endif">
                                </i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $payment->payment_method ?? 'Online' }}
                                </p>
                                @if($payment->external_id)
                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate">
                                        Ref: {{ $payment->external_id }}
                                    </p>
                                @elseif($payment->reference_id)
                                    <p class="text-xs text-gray-400 dark:text-gray-500 truncate">
                                        Ref: {{ $payment->reference_id }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-3 flex-shrink-0">
                            {{-- Payment link button if available and pending --}}
                            @if($payment->hasPaymentLink() && $payment->isPending())
                                <a href="{{ $payment->payment_url }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium
                                          text-emerald-700 dark:text-emerald-400
                                          bg-emerald-50 dark:bg-emerald-500/10
                                          hover:bg-emerald-100 dark:hover:bg-emerald-500/20
                                          rounded-lg transition-colors">
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                    Lanjutkan
                                </a>
                            @endif

                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                @if($payment->status === 'confirmed') bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400
                                @elseif($payment->status === 'rejected') bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400
                                @else bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400
                                @endif">
                                {{ $payment->getStatusLabel() }}
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

</div>
@endsection
