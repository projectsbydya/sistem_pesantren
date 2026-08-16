@extends('layouts.dashboard')

@section('title', 'Detail Subscription — ' . ($subscription->tenant->name ?? 'Subscription'))
@section('page-title', 'Detail Subscription')

@php
    $badgeMap = [
        'blue' => 'info', 'emerald' => 'success', 'amber' => 'warning', 'rose' => 'danger', 'gray' => 'default',
    ];
@endphp

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-super-admin.page-header
        title="Detail Subscription"
        subtitle="{{ $subscription->tenant->name ?? 'Unknown Tenant' }} — {{ $subscription->package_name }}"
        backUrl="{{ route('dashboard.super-admin.subscriptions.index') }}">
        <x-slot:actions>
            <div class="flex items-center gap-2">
                @can('update', $subscription)
                    <x-btn href="{{ route('dashboard.super-admin.subscriptions.edit', $subscription) }}" icon="fa-pen" variant="primary" size="sm">Edit</x-btn>
                @endcan
                @can('delete', $subscription)
                    <form method="POST" action="{{ route('dashboard.super-admin.subscriptions.destroy', $subscription) }}" class="inline"
                          onsubmit="return confirm('Hapus subscription ini?');">
                        @csrf
                        @method('DELETE')
                        <x-btn type="submit" icon="fa-trash" variant="danger" size="sm">Hapus</x-btn>
                    </form>
                @endcan
            </div>
        </x-slot:actions>
    </x-super-admin.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Subscription Summary -->
        <x-card class="lg:col-span-2" title="Informasi Subscription">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Status</span>
                    <x-badge variant="{{ $badgeMap[$subscription->getStatusColor()] ?? 'default' }}" size="md">
                        {{ $subscription->getStatusLabel() }}
                    </x-badge>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Tenant</span>
                    <span class="font-medium text-gray-900">{{ $subscription->tenant->name ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Paket</span>
                    <span class="font-medium text-gray-900">{{ $subscription->package_name }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Siklus Billing</span>
                    <span class="font-medium text-gray-900">{{ $subscription->billing_cycle === 'yearly' ? 'Tahunan' : 'Bulanan' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Jumlah</span>
                    <span class="font-medium text-gray-900">Rp {{ number_format($subscription->amount, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Mulai</span>
                    <span class="font-medium text-gray-900">{{ $subscription->starts_at?->format('d M Y H:i') ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Berakhir</span>
                    <span class="font-medium {{ $subscription->isExpired() ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $subscription->ends_at?->format('d M Y H:i') ?? '-' }}
                        @if($subscription->isEndingSoon(7) && !$subscription->isExpired())
                            <span class="text-xs text-amber-600 ml-1">({{ $subscription->getRemainingDays() }} hari lagi)</span>
                        @endif
                    </span>
                </div>
                @if($subscription->trial_ends_at)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Trial Berakhir</span>
                        <span class="font-medium text-gray-900">{{ $subscription->trial_ends_at->format('d M Y') }}</span>
                    </div>
                @endif
                @if($subscription->grace_period_ends_at)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">Grace Period</span>
                        <span class="font-medium text-amber-600">{{ $subscription->grace_period_ends_at->format('d M Y H:i') }}</span>
                    </div>
                @endif
            </div>
        </x-card>

        <!-- Amount Card -->
        <x-card class="bg-gradient-to-br from-blue-50 to-white" title="Tagihan">
            <div class="text-center py-4">
                <p class="text-sm text-gray-500 mb-1">Nilai Subscription</p>
                <p class="text-4xl font-bold text-gray-900">Rp {{ number_format($subscription->amount, 0, ',', '.') }}</p>
                <p class="text-xs text-gray-400 mt-2">per {{ $subscription->billing_cycle === 'yearly' ? 'tahun' : 'bulan' }}</p>
            </div>
        </x-card>
    </div>

    <!-- Actions -->
    <x-card title="Aksi Subscription">
        <div class="flex flex-wrap gap-3">
            @if($subscription->isActive())
                <form method="POST" action="{{ route('dashboard.super-admin.subscriptions.suspend', $subscription) }}" class="inline">
                    @csrf
                    <x-btn type="submit" icon="fa-pause" variant="warning">Suspend</x-btn>
                </form>
                <form method="POST" action="{{ route('dashboard.super-admin.subscriptions.renew', $subscription) }}" class="inline">
                    @csrf
                    <x-btn type="submit" icon="fa-sync" variant="primary">Renew</x-btn>
                </form>
                <form method="POST" action="{{ route('dashboard.super-admin.subscriptions.cancel', $subscription) }}" class="inline">
                    @csrf
                    <x-btn type="submit" icon="fa-ban" variant="danger">Batalkan</x-btn>
                </form>
            @elseif($subscription->isSuspended() || $subscription->isExpired() || $subscription->isTrialExpired())
                <form method="POST" action="{{ route('dashboard.super-admin.subscriptions.activate', $subscription) }}" class="inline">
                    @csrf
                    <x-btn type="submit" icon="fa-play" variant="success">Aktifkan</x-btn>
                </form>
            @elseif($subscription->isTrial())
                <form method="POST" action="{{ route('dashboard.super-admin.subscriptions.convert-trial', $subscription) }}" class="inline">
                    @csrf
                    <x-btn type="submit" icon="fa-arrow-right" variant="primary">Konversi Trial</x-btn>
                </form>
            @endif
        </div>
    </x-card>

    <!-- Invoices -->
    @php
        $subscription->loadMissing('invoices');
    @endphp
    <x-card title="Invoice Terkait" subtitle="{{ $subscription->invoices->count() }} invoice">
        @if($subscription->invoices->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Invoice</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3">Jatuh Tempo</th>
                            <th class="px-4 py-3 rounded-r-lg text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($subscription->invoices->take(10) as $inv)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $inv->invoice_number }}</td>
                                <td class="px-4 py-3 text-right">{{ $inv->getFormattedAmount() }}</td>
                                <td class="px-4 py-3 text-center">
                                    <x-badge variant="{{ $inv->getStatusColor() }}" size="xs">{{ $inv->getStatusLabel() }}</x-badge>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $inv->due_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <x-btn href="{{ route('dashboard.super-admin.invoices.show', $inv) }}" icon="fa-eye" variant="outline" size="xs">Detail</x-btn>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 text-center py-8">Belum ada invoice untuk subscription ini.</p>
        @endif
    </x-card>
</div>
@endsection
