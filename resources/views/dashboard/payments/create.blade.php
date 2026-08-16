@extends('layouts.tenant')

@section('title', 'Ajukan Pembayaran')

@php
$methodLabels = \App\Models\BillPayment::METHOD_LABELS;
@endphp

@section('content')
<div class="max-w-2xl mx-auto">

<div class="mb-6">
    <a href="{{ tenant_route('dashboard.payments.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">Ajukan Pembayaran</h1>
</div>

<x-card>
    @if($errors->any())
        <x-alert type="error" class="mb-5">
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </x-alert>
    @endif

    <form method="POST" action="{{ tenant_route('dashboard.payments.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tagihan <span class="text-red-500">*</span></label>
            <select name="bill_id" required
                    class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                <option value="">Pilih Tagihan</option>
                @foreach($billList as $bill)
                    <option value="{{ $bill->id }}" data-santri="{{ $bill->santri_id }}"
                        {{ old('bill_id', $selectedBill?->id) == $bill->id ? 'selected' : '' }}>
                        {{ $bill->santri?->name ?? '-' }} — {{ \App\Models\Bill::TYPE_LABELS[$bill->type] ?? $bill->type }}
                        (Rp {{ number_format((float) $bill->amount, 0, ',', '.') }})
                    </option>
                @endforeach
            </select>
            @error('bill_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <input type="hidden" name="santri_id" value="{{ old('santri_id', $selectedBill?->santri_id) }}">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jumlah (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" value="{{ old('amount') }}" required min="1" step="0.01"
                       class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                       placeholder="500000">
                @error('amount')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Pembayaran <span class="text-red-500">*</span></label>
                <input type="date" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required
                       class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                @error('payment_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Metode Pembayaran <span class="text-red-500">*</span></label>
            <select name="payment_method" required
                    class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors">
                <option value="">Pilih Metode</option>
                @foreach($methodLabels as $val => $label)
                    <option value="{{ $val }}" {{ old('payment_method') === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('payment_method')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nomor Referensi</label>
            <input type="text" name="reference_number" value="{{ old('reference_number') }}"
                   class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                   placeholder="Contoh: TRX-123456">
            @error('reference_number')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Bukti Transfer</label>
            <input type="file" name="transfer_proof" accept="image/*,.pdf"
                   class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-gray-800 dark:file:text-emerald-400">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Format gambar atau PDF (maks. 2 MB).</p>
            @error('transfer_proof')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Catatan</label>
            <textarea name="notes" rows="3"
                      class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors"
                      placeholder="Catatan tambahan untuk admin/bendahara">{{ old('notes') }}</textarea>
            @error('notes')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ tenant_route('dashboard.payments.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Batal</a>
            <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                <i class="fa-solid fa-paper-plane mr-1.5"></i> Ajukan Pembayaran
            </button>
        </div>
    </form>
</x-card>

</div>
@endsection
