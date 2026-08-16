@extends('layouts.dashboard')

@section('title', 'Detail Plan — ' . $plan->name)
@section('page-title', 'Detail Plan')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <x-super-admin.page-header
        title="{{ $plan->name }}"
        subtitle="Detail paket langganan"
        backUrl="{{ route('dashboard.super-admin.plans.index') }}">
        <x-slot:actions>
            @can('update', $plan)
                <x-btn href="{{ route('dashboard.super-admin.plans.edit', $plan) }}" icon="fa-pen" variant="primary" size="sm">Edit Plan</x-btn>
            @endcan
        </x-slot:actions>
    </x-super-admin.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <!-- Main Info -->
        <x-card class="lg:col-span-2" title="Informasi Plan">
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Status</span>
                    <x-badge variant="{{ $plan->is_active ? 'success' : 'default' }}" size="sm">
                        {{ $plan->is_active ? 'Aktif' : 'Nonaktif' }}
                    </x-badge>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Kode</span>
                    <code class="text-sm font-mono bg-gray-100 px-2 py-1 rounded text-gray-700">{{ $plan->code }}</code>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Harga</span>
                    <span class="text-lg font-bold text-gray-900">{{ $plan->getFormattedPrice() }} <span class="text-sm font-normal text-gray-500">/ {{ $plan->billing_cycle === 'yearly' ? 'tahun' : 'bulan' }}</span></span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Masa Trial</span>
                    <span class="text-sm font-medium text-gray-900">{{ $plan->trial_days }} hari</span>
                </div>
                @if($plan->description)
                    <div class="pt-4 border-t border-gray-100">
                        <span class="text-sm text-gray-500 block mb-1">Deskripsi</span>
                        <p class="text-sm text-gray-700">{{ $plan->description }}</p>
                    </div>
                @endif
            </div>
        </x-card>

        <!-- Limits -->
        <x-card title="Limit Resource">
            <div class="space-y-3">
                @php
                    $limits = [
                        ['label' => 'Santri', 'value' => $plan->santri_limit ?: '∞'],
                        ['label' => 'User', 'value' => $plan->user_limit ?: '∞'],
                        ['label' => 'Cabang', 'value' => $plan->branch_limit ?: '∞'],
                        ['label' => 'Storage', 'value' => ($plan->storage_limit_mb ?: '∞') . ' MB'],
                    ];
                @endphp
                @foreach($limits as $limit)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0 last:pb-0">
                        <span class="text-sm text-gray-500">{{ $limit['label'] }}</span>
                        <span class="font-semibold text-gray-900">{{ $limit['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>

    @if(!empty($plan->features) && is_array($plan->features))
        <x-card title="Fitur yang Diaktifkan">
            <div class="flex flex-wrap gap-2">
                @foreach($plan->features as $feature => $enabled)
                    @if($enabled)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-full text-sm font-medium">
                            <i class="fa-solid fa-check text-xs"></i>
                            {{ is_string($feature) ? $feature : 'Fitur ' . ($loop->iteration) }}
                        </span>
                    @endif
                @endforeach
            </div>
        </x-card>
    @endif

    <x-card title="Subscription yang Menggunakan Plan" subtitle="{{ $plan->subscriptions->count() }} tenant">
        @if($plan->subscriptions->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Tenant</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 rounded-r-lg text-right">Berakhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($plan->subscriptions->take(10) as $subscription)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $subscription->tenant->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <x-badge variant="{{ $subscription->getStatusColor() }}" size="xs">
                                        {{ $subscription->getStatusLabel() }}
                                    </x-badge>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-500">{{ $subscription->ends_at?->format('d M Y') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-gray-500 text-center py-6">Belum ada subscription yang menggunakan plan ini.</p>
        @endif
    </x-card>
</div>
@endsection
