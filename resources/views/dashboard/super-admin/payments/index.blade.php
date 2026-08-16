@extends('layouts.dashboard')

@section('title', 'Manajemen Pembayaran')
@section('page-title', 'Manajemen Pembayaran')

@php
    $status = request('status');
@endphp

@section('content')
<div class="space-y-6">
    <x-super-admin.page-header
        title="Manajemen Pembayaran"
        subtitle="Konfirmasi dan pantau pembayaran subscription dari seluruh tenant.">
        <x-slot:actions>
            @can('create', App\Models\SaasPayment::class)
                <x-btn href="#record-payment" icon="fa-plus" variant="primary">Catat Pembayaran</x-btn>
            @endcan
        </x-slot:actions>
    </x-super-admin.page-header>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Total Pembayaran" value="{{ $payments->total() }}" icon="fa-credit-card" color="blue"/>
        <x-stat-card title="Dikonfirmasi" value="{{ $payments->getCollection()->where('status', 'confirmed')->count() }}" icon="fa-check-circle" color="emerald"/>
        <x-stat-card title="Menunggu" value="{{ $payments->getCollection()->where('status', 'pending')->count() }}" icon="fa-clock" color="amber"/>
        <x-stat-card title="Ditolak" value="{{ $payments->getCollection()->where('status', 'rejected')->count() }}" icon="fa-times-circle" color="rose"/>
    </div>

    <!-- Filters -->
    <x-card padding="true">
        <form method="GET" action="{{ route('dashboard.super-admin.payments.index') }}" class="flex flex-col sm:flex-row gap-3 items-end">
            <div class="w-full sm:w-56">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status Pembayaran</label>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="">Semua Status</option>
                    @foreach(App\Models\SaasPayment::STATUS_LABELS as $key => $label)
                        <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <x-btn type="submit" icon="fa-filter" variant="secondary" size="md">Filter</x-btn>
            @if($status)
                <x-btn href="{{ route('dashboard.super-admin.payments.index') }}" variant="ghost" size="md">Reset</x-btn>
            @endif
        </form>
    </x-card>

    <!-- Payments Table -->
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 rounded-l-lg">ID</th>
                        <th class="px-5 py-3">Tenant</th>
                        <th class="px-5 py-3">Invoice</th>
                        <th class="px-5 py-3">Metode</th>
                        <th class="px-5 py-3 text-right">Jumlah</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3">Waktu</th>
                        <th class="px-5 py-3 rounded-r-lg text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-4 font-medium text-gray-900">#{{ $payment->id }}</td>
                            <td class="px-5 py-4 text-gray-700">{{ $payment->invoice->tenant->name ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $payment->invoice->invoice_number ?? '-' }}</td>
                            <td class="px-5 py-4 text-gray-600">{{ $payment->getPaymentMethodLabel() }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-gray-900">{{ $payment->getFormattedAmount() }}</td>
                            <td class="px-5 py-4 text-center">
                                <x-badge variant="{{ $payment->getStatusColor() }}" size="sm">
                                    {{ $payment->getStatusLabel() }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $payment->created_at->format('d M Y') }}
                                <div class="text-xs text-gray-400">{{ $payment->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-btn href="{{ route('dashboard.super-admin.payments.show', $payment) }}" icon="fa-eye" variant="outline" size="xs">Detail</x-btn>
                                    @can('delete', $payment)
                                        <form method="POST" action="{{ route('dashboard.super-admin.payments.destroy', $payment) }}" class="inline"
                                              onsubmit="return confirm('Hapus pembayaran ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors" title="Hapus">
                                                <i class="fa-solid fa-trash text-xs"></i>
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-credit-card text-3xl text-gray-300"></i>
                                    <p>Belum ada pembayaran tercatat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $payments->links() }}
            </div>
        @endif
    </x-card>

    @can('create', App\Models\SaasPayment::class)
        @php
            $invoicesForPayment = App\Models\Invoice::with('tenant')->whereIn('status', ['unpaid', 'paid'])->latest()->get();
        @endphp
        <div id="record-payment" class="scroll-mt-6">
            <x-card title="Catat Pembayaran Baru" subtitle="Tambahkan pembayaran manual untuk invoice tenant.">
                <form method="POST" action="{{ route('dashboard.super-admin.payments.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Invoice <span class="text-red-500">*</span></label>
                            <select name="invoice_id" required class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                <option value="">Pilih Invoice</option>
                                @foreach($invoicesForPayment as $inv)
                                    <option value="{{ $inv->id }}" {{ old('invoice_id') == $inv->id ? 'selected' : '' }}>
                                        {{ $inv->invoice_number }} — {{ $inv->tenant->name ?? 'Tenant #' . $inv->tenant_id }} ({{ $inv->getStatusLabel() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('invoice_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jumlah (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" value="{{ old('amount') }}" required min="0"
                                   class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Metode Pembayaran <span class="text-red-500">*</span></label>
                            <select name="payment_method" required class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                @foreach(App\Models\SaasPayment::PAYMENT_METHOD_LABELS as $key => $label)
                                    <option value="{{ $key }}" {{ old('payment_method') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('payment_method')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Bukti Transfer / Referensi</label>
                            <input type="text" name="transfer_proof" value="{{ old('transfer_proof') }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            @error('transfer_proof')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Catatan</label>
                            <textarea name="notes" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">{{ old('notes') }}</textarea>
                            @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <x-btn type="submit" icon="fa-save" variant="primary">Simpan Pembayaran</x-btn>
                    </div>
                </form>
            </x-card>
        </div>
    @endcan
</div>
@endsection
