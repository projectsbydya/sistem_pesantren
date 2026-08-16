@extends('layouts.dashboard')

@section('title', 'Manajemen Invoice')
@section('page-title', 'Manajemen Invoice')

@php
    $status = request('status');
    $tenantId = request('tenant_id');
    $search = request('search');
    $totalCount = $invoices->total();
@endphp

@section('content')
<div class="space-y-6">
    <x-super-admin.page-header
        title="Manajemen Invoice"
        subtitle="Kelola invoice dan status pembayaran untuk seluruh tenant.">
        <x-slot:actions>
            @can('create', App\Models\Invoice::class)
                <x-btn href="#create-invoice" icon="fa-plus" variant="primary">Buat Invoice</x-btn>
            @endcan
        </x-slot:actions>
    </x-super-admin.page-header>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <x-stat-card title="Total Invoice" value="{{ $totalCount }}" icon="fa-file-invoice" color="blue"/>
        <x-stat-card title="Lunas" value="{{ $invoices->getCollection()->where('status', 'paid')->count() }}" icon="fa-check-circle" color="emerald"/>
        <x-stat-card title="Belum Lunas" value="{{ $invoices->getCollection()->where('status', 'unpaid')->count() }}" icon="fa-clock" color="amber"/>
        <x-stat-card title="Jatuh Tempo" value="{{ $invoices->getCollection()->filter(fn($i) => $i->isOverdue())->count() }}" icon="fa-exclamation-circle" color="rose"/>
    </div>

    <!-- Filters -->
    <x-card padding="true">
        <form method="GET" action="{{ route('dashboard.super-admin.invoices.index') }}" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari Invoice</label>
                <input type="text" name="search" value="{{ $search }}" placeholder="Nomor invoice / nama tenant"
                       class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
            </div>
            <div class="w-full md:w-48">
                <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="">Semua Status</option>
                    @foreach(App\Models\Invoice::STATUS_LABELS as $key => $label)
                        <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-56">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tenant</label>
                <select name="tenant_id" class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    <option value="">Semua Tenant</option>
                    @foreach($tenants as $t)
                        <option value="{{ $t->id }}" {{ (string)$tenantId === (string)$t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <x-btn type="submit" icon="fa-filter" variant="secondary" size="md">Filter</x-btn>
            </div>
            @if($status || $tenantId || $search)
                <div class="flex items-end">
                    <x-btn href="{{ route('dashboard.super-admin.invoices.index') }}" variant="ghost" size="md">Reset</x-btn>
                </div>
            @endif
        </form>
    </x-card>

    <!-- Invoices Table -->
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 rounded-l-lg">Invoice</th>
                        <th class="px-5 py-3">Tenant</th>
                        <th class="px-5 py-3">Paket</th>
                        <th class="px-5 py-3 text-right">Jumlah</th>
                        <th class="px-5 py-3 text-center">Status</th>
                        <th class="px-5 py-3">Jatuh Tempo</th>
                        <th class="px-5 py-3 rounded-r-lg text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($invoices as $invoice)
                        <tr class="hover:bg-gray-50 transition-colors {{ $invoice->isOverdue() ? 'bg-red-50/30' : '' }}">
                            <td class="px-5 py-4 font-medium text-gray-900">
                                {{ $invoice->invoice_number }}
                            </td>
                            <td class="px-5 py-4 text-gray-700">
                                {{ $invoice->tenant->name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ $invoice->subscription->package_name ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-right font-semibold text-gray-900">
                                {{ $invoice->getFormattedAmount() }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <x-badge variant="{{ $invoice->getStatusColor() }}" size="sm">
                                    {{ $invoice->getStatusLabel() }}
                                </x-badge>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                @if($invoice->isOverdue())
                                    <span class="text-red-600 font-medium">{{ $invoice->due_date->format('d M Y') }}</span>
                                @else
                                    {{ $invoice->due_date->format('d M Y') }}
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-btn href="{{ route('dashboard.super-admin.invoices.show', $invoice) }}" icon="fa-eye" variant="outline" size="xs">Detail</x-btn>
                                    @can('cancel', $invoice)
                                        @if($invoice->isUnpaid())
                                            <form method="POST" action="{{ route('dashboard.super-admin.invoices.cancel', $invoice) }}" class="inline"
                                                  onsubmit="return confirm('Batalkan invoice ini?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200 transition-colors" title="Batalkan">
                                                    <i class="fa-solid fa-ban text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                    @can('markPaid', $invoice)
                                        @if($invoice->isUnpaid())
                                            <form method="POST" action="{{ route('dashboard.super-admin.invoices.mark-paid', $invoice) }}" class="inline"
                                                  onsubmit="return confirm('Tandai invoice ini sebagai lunas?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-100 text-emerald-600 hover:bg-emerald-200 transition-colors" title="Tandai Lunas">
                                                    <i class="fa-solid fa-check text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-solid fa-file-invoice text-3xl text-gray-300"></i>
                                    <p>Belum ada invoice</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($invoices->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">
                {{ $invoices->links() }}
            </div>
        @endif
    </x-card>

    @can('create', App\Models\Invoice::class)
        @php
            $subscriptions = App\Models\Subscription::with('tenant')->latest()->get();
        @endphp
        <div id="create-invoice" class="scroll-mt-6">
            <x-card title="Buat Invoice Baru" subtitle="Generate invoice untuk subscription tenant.">
                <form method="POST" action="{{ route('dashboard.super-admin.invoices.store') }}" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Subscription <span class="text-red-500">*</span></label>
                            <select name="subscription_id" required class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                <option value="">Pilih Subscription</option>
                                @foreach($subscriptions as $sub)
                                    <option value="{{ $sub->id }}" {{ old('subscription_id') == $sub->id ? 'selected' : '' }}>
                                        {{ $sub->tenant->name ?? 'Tenant #' . $sub->tenant_id }} — {{ $sub->package_name }} ({{ $sub->getStatusLabel() }})
                                    </option>
                                @endforeach
                            </select>
                            @error('subscription_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jumlah (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" value="{{ old('amount') }}" required min="0"
                                   class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jatuh Tempo <span class="text-red-500">*</span></label>
                            <input type="date" name="due_date" value="{{ old('due_date', now()->addDays(7)->format('Y-m-d')) }}" required
                                   class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            @error('due_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Periode</label>
                            <input type="text" name="period_label" value="{{ old('period_label') }}" placeholder="Contoh: Juni 2026"
                                   class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            @error('period_label')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Catatan</label>
                            <textarea name="notes" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 text-sm">{{ old('notes') }}</textarea>
                            @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <x-btn type="submit" icon="fa-save" variant="primary">Simpan Invoice</x-btn>
                    </div>
                </form>
            </x-card>
        </div>
    @endcan
</div>
@endsection
