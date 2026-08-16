@extends('layouts.tenant')

@section('title', 'Keuangan & SPP')

@section('content')
@php $tenant = \App\Services\TenantService::getTenant(); @endphp

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Keuangan & SPP</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">Manajemen tagihan dan pembayaran santri — {{ $tenant?->name }}</p>
    </div>
    @if(Auth::user()->isAdmin())
        <div class="flex items-center gap-2">
            <x-btn href="#" variant="secondary" icon="fa-file-invoice">Input Pembayaran</x-btn>
            <x-btn href="#" variant="primary" icon="fa-plus">Buat Tagihan</x-btn>
        </div>
    @endif
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card 
        title="Total Tunggakan" 
        value="Rp {{ number_format($totalTunggakan ?? 2450000, 0, ',', '.') }}" 
        icon="fa-money-bill-trend-up" 
        color="rose"
        trend="12"
        :trend-up="false" />
    
    <x-stat-card 
        title="Pembayaran Bulan Ini" 
        value="Rp {{ number_format($pembayaranBulanIni ?? 8750000, 0, ',', '.') }}" 
        icon="fa-hand-holding-dollar" 
        color="emerald"
        trend="8"
        :trend-up="true" />
    
    <x-stat-card 
        title="Santri Belum Lunas" 
        value="{{ $santriBelumLunas ?? 32 }}" 
        icon="fa-user-clock" 
        color="amber" />
    
    <x-stat-card 
        title="Lunas Bulan Ini" 
        value="{{ $santriLunas ?? 96 }}" 
        icon="fa-circle-check" 
        color="blue" />
</div>

<!-- Filter Section -->
<x-card class="mb-5">
    <div class="flex flex-col md:flex-row gap-3">
        <div class="flex-1">
            <div class="relative">
                <i class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" placeholder="Cari nama santri..."
                       class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <select class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <option value="">Semua Bulan</option>
                <option value="2024-01">Januari 2024</option>
                <option value="2024-02">Februari 2024</option>
                <option value="2024-03">Maret 2024</option>
            </select>
            <select class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <option value="">Semua Status</option>
                <option value="lunas">Lunas</option>
                <option value="belum">Belum Lunas</option>
                <option value="dicicil">Dicicil</option>
            </select>
            <select class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg focus:ring-2 focus:ring-emerald-500">
                <option value="">Jenis Tagihan</option>
                <option value="spp">SPP</option>
                <option value="daftar">Daftar Ulang</option>
                <option value="buku">Buku</option>
                <option value="lainnya">Lainnya</option>
            </select>
            <x-btn variant="ghost" size="sm" icon="fa-download">Export</x-btn>
        </div>
    </div>
</x-card>

<!-- Tagihan List -->
<x-card title="Daftar Tagihan" subtitle="Menampilkan tagihan aktif">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Santri</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Jenis</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Periode</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Dibayar</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @php
                $tagihans = [
                    ['santri' => 'Ahmad Fauzi', 'jenis' => 'SPP', 'periode' => 'Januari 2024', 'total' => 500000, 'dibayar' => 500000, 'status' => 'lunas'],
                    ['santri' => 'Budi Santoso', 'jenis' => 'SPP', 'periode' => 'Januari 2024', 'total' => 500000, 'dibayar' => 250000, 'status' => 'dicicil'],
                    ['santri' => 'Candra Wijaya', 'jenis' => 'Daftar Ulang', 'periode' => 'Tahun Ajaran 2024', 'total' => 2500000, 'dibayar' => 0, 'status' => 'belum'],
                    ['santri' => 'Dedi Kurniawan', 'jenis' => 'SPP', 'periode' => 'Desember 2023', 'total' => 500000, 'dibayar' => 0, 'status' => 'belum'],
                    ['santri' => 'Eko Prasetyo', 'jenis' => 'Buku Paket', 'periode' => 'Semester 1', 'total' => 350000, 'dibayar' => 350000, 'status' => 'lunas'],
                ];
                @endphp
                
                @foreach($tagihans as $tagihan)
                    @php
                    $sisa = $tagihan['total'] - $tagihan['dibayar'];
                    $progress = ($tagihan['dibayar'] / $tagihan['total']) * 100;
                    $statusConfig = [
                        'lunas' => ['variant' => 'success', 'label' => 'Lunas'],
                        'dicicil' => ['variant' => 'warning', 'label' => 'Dicicil'],
                        'belum' => ['variant' => 'danger', 'label' => 'Belum Bayar'],
                    ][$tagihan['status']];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-sm font-bold">
                                    {{ strtoupper(substr($tagihan['santri'], 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $tagihan['santri'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $tagihan['jenis'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $tagihan['periode'] }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">
                            Rp {{ number_format($tagihan['total'], 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right text-sm text-gray-600 dark:text-gray-400">
                            <div class="flex flex-col items-end gap-1">
                                <span>Rp {{ number_format($tagihan['dibayar'], 0, ',', '.') }}</span>
                                @if($tagihan['status'] === 'dicicil')
                                    <div class="w-20 h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-amber-500 rounded-full" style="width: {{ $progress }}%"></div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $statusConfig['variant'] }}" size="sm" dot>
                                {{ $statusConfig['label'] }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                @if($tagihan['status'] !== 'lunas')
                                    <x-btn variant="primary" size="xs">Bayar</x-btn>
                                @endif
                                <x-btn variant="ghost" size="xs" icon="fa-eye"></x-btn>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-card>

<!-- Santri Dengan Tunggakan (Highlight) -->
<div class="mt-6">
    <x-card title="Santri dengan Tunggakan Tertinggi" subtitle="Perlu perhatian segera" class="border-red-200">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
            $tunggakanSantri = [
                ['name' => 'Candra Wijaya', 'kelas' => '10A', 'tunggakan' => 3500000, 'bulan' => 7],
                ['name' => 'Dedi Kurniawan', 'kelas' => '9B', 'tunggakan' => 2800000, 'bulan' => 5],
                ['name' => 'Fajar Nugroho', 'kelas' => '11A', 'tunggakan' => 2000000, 'bulan' => 4],
            ];
            @endphp
            
            @foreach($tunggakanSantri as $s)
                <div class="flex items-center gap-4 p-4 bg-red-50 dark:bg-red-500/10 rounded-xl border border-red-100 dark:border-red-500/20">
                    <div class="w-12 h-12 rounded-full bg-red-200 dark:bg-red-500/20 text-red-700 dark:text-red-400 flex items-center justify-center text-lg font-bold">
                        {{ strtoupper(substr($s['name'], 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $s['name'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Kelas {{ $s['kelas'] }} • {{ $s['bulan'] }} bulan menunggak</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-bold text-red-600 dark:text-red-400">Rp {{ number_format($s['tunggakan'], 0, ',', '.') }}</p>
                        <x-btn variant="primary" size="xs" class="mt-1">Tagih</x-btn>
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>
</div>

@endsection
