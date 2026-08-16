@extends('layouts.tenant')

@section('title', 'Edit Pembayaran')

@php
$methodLabels = \App\Models\BillPayment::METHOD_LABELS;
$isPending = $payment->isPending();
@endphp

@section('content')
<div class="max-w-2xl mx-auto">

<div class="mb-6">
    <a href="{{ tenant_route('dashboard.payments.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors">
        <i class="fa-solid fa-arrow-left"></i> Kembali
    </a>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mt-2">Edit Pembayaran</h1>
</div>

@if(! $isPending)
<div class="mb-5 px-4 py-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
    <p class="text-sm text-amber-800 dark:text-amber-200"><i class="fa-solid fa-triangle-exclamation mr-1.5"></i> Pembayaran yang sudah diproses tidak dapat diubah.</p>
</div>
@endif

<x-card>
    @if($errors->any())
        <x-alert type="error" class="mb-5">
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </x-alert>
    @endif

    <form method="POST" action="{{ tenant_route('dashboard.payments.update', ['payment' => $payment->id]) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tagihan <span class="text-red-500">*</span></label>
            <select name="bill_id" required {{ ! $isPending ? 'disabled' : '' }}
                    class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors disabled:opacity-60">
                <option value="">Pilih Tagihan</option>
                @foreach($billList as $bill)
                    <option value="{{ $bill->id }}"
                        {{ old('bill_id', $payment->bill_id) == $bill->id ? 'selected' : '' }}>
                        {{ $bill->santri?->name ?? '-' }} — {{ \App\Models\Bill::TYPE_LABELS[$bill->type] ?? $bill->type }}
                        (Rp {{ number_format((float) $bill->amount, 0, ',', '.') }})
                    </option>
                @endforeach
            </select>
            @error('bill_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            @if(! $isPending)<input type="hidden" name="bill_id" value="{{ $payment->bill_id }}">@endif
        </div>

        <input type="hidden" name="santri_id" value="{{ old('santri_id', $payment->santri_id) }}">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Jumlah (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" value="{{ old('amount', $payment->amount) }}" required min="1" step="0.01" {{ ! $isPending ? 'readonly' : '' }}
                       class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors read-only:bg-gray-100 dark:read-only:bg-gray-800">
                @error('amount')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Tanggal Pembayaran <span class="text-red-500">*</span></label>
                <input type="date" name="payment_date" value="{{ old('payment_date', $payment->payment_date?->format('Y-m-d')) }}" required {{ ! $isPending ? 'readonly' : '' }}
                       class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors read-only:bg-gray-100 dark:read-only:bg-gray-800">
                @error('payment_date')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Metode Pembayaran <span class="text-red-500">*</span></label>
            <select name="payment_method" required {{ ! $isPending ? 'disabled' : '' }}
                    class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors disabled:opacity-60">
                <option value="">Pilih Metode</option>
                @foreach($methodLabels as $val => $label)
                    <option value="{{ $val }}" {{ old('payment_method', $payment->payment_method) === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @error('payment_method')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            @if(! $isPending)<input type="hidden" name="payment_method" value="{{ $payment->payment_method }}">@endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nomor Referensi</label>
            <input type="text" name="reference_number" value="{{ old('reference_number', $payment->reference_number) }}" {{ ! $isPending ? 'readonly' : '' }}
                   class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors read-only:bg-gray-100 dark:read-only:bg-gray-800"
                   placeholder="Contoh: TRX-123456">
            @error('reference_number')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Bukti Transfer</label>
            @if($payment->transfer_proof)
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Bukti saat ini: <a href="{{ Storage::disk('public')->url($payment->transfer_proof) }}" target="_blank" rel="noopener noreferrer" class="text-emerald-600 hover:underline">Lihat</a></p>
            @endif
            <input type="file" name="transfer_proof" accept="image/*,.pdf" {{ ! $isPending ? 'disabled' : '' }}
                   class="block w-full text-sm text-gray-700 dark:text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-gray-800 dark:file:text-emerald-400 disabled:opacity-60">
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kosongkan jika tidak ingin mengubah bukti.</p>
            @error('transfer_proof')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Catatan</label>
            <textarea name="notes" rows="3" {{ ! $isPending ? 'readonly' : '' }}
                      class="w-full border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors read-only:bg-gray-100 dark:read-only:bg-gray-800"
                      placeholder="Catatan tambahan untuk admin/bendahara">{{ old('notes', $payment->notes) }}</textarea>
            @error('notes')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ tenant_route('dashboard.payments.index') }}"
               class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Batal</a>
            @if($isPending)
                <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
                    <i class="fa-solid fa-save mr-1.5"></i> Simpan Perubahan
                </button>
            @endif
        </div>
    </form>
</x-card>

</div>
@endsection
