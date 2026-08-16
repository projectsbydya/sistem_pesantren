@extends('layouts.tenant')

@section('title', 'Data Orang Tua')
@section('page-title', 'Data Orang Tua')
@section('breadcrumb')
    <span>Orang Tua</span>
@endsection

@section('content')

{{-- Alert for credential download --}}
@if(session('show_credentials_download'))
    <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">Akun Berhasil Dibuat!</h4>
                <p class="text-sm text-emerald-700 dark:text-emerald-300 mt-1">{{ session('success') }}</p>
                <div class="mt-3">
                    <a href="{{ tenant_route('dashboard.parent.credentials.download') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                        <i class="fa-solid fa-file-excel"></i>
                        Download File Akun (.XLSX)
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Data Orang Tua</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">Manajemen data wali santri</p>
    </div>
    @if(Auth::user()->isAdmin())
        <x-btn href="{{ tenant_route('dashboard.parent.create') }}" variant="primary" icon="fa-plus">
            Tambah Orang Tua
        </x-btn>
    @endif
</div>

{{-- Stats Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-blue-600 flex items-center justify-center">
                <i class="fa-solid fa-users text-sm"></i>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Orang Tua</p>
                <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $parents->count() }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 flex items-center justify-center">
                <i class="fa-solid fa-user-check text-sm"></i>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aktif</p>
                <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $parents->where('is_active', true)->count() }}</p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-purple-50 dark:bg-purple-900/20 text-purple-600 flex items-center justify-center">
                <i class="fa-solid fa-key text-sm"></i>
            </div>
            <div>
                <p class="text-[11px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Punya Akun Login</p>
                <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $parents->whereNotNull('user_id')->count() }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Parents Table --}}
<x-card>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-100 dark:border-gray-800">
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kontak</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Relasi</th>
                    <th class="text-left px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Santri</th>
                    <th class="text-center px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="text-center px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Akun</th>
                    @if(Auth::user()->isAdmin())
                        <th class="text-right px-4 py-3 text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($parents as $p)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center text-sm font-medium">
                                    {{ strtoupper(substr($p->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100">{{ $p->name }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-mono">{{ $p->user?->email ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-[13px] text-gray-700 dark:text-gray-300">{{ $p->phone ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="default" size="sm">
                                @switch($p->relationship)
                                    @case('father') Ayah @break
                                    @case('mother') Ibu @break
                                    @case('guardian') Wali @break
                                    @default {{ $p->relationship }}
                                @endswitch
                            </x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse($p->santri->take(2) as $s)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                        {{ $s->name }}
                                    </span>
                                @empty
                                    <span class="text-[11px] text-gray-400">Belum ada santri</span>
                                @endforelse
                                @if($p->santri->count() > 2)
                                    <span class="text-[11px] text-gray-400">+{{ $p->santri->count() - 2 }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <x-badge variant="{{ $p->is_active ? 'success' : 'default' }}" size="sm" dot>
                                {{ $p->is_active ? 'Aktif' : 'Nonaktif' }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($p->user)
                                <i class="fa-solid fa-check text-emerald-500 text-sm" title="Punya akun login"></i>
                            @else
                                <i class="fa-solid fa-minus text-gray-300 text-sm" title="Belum punya akun"></i>
                            @endif
                        </td>
                        @if(Auth::user()->isAdmin())
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ tenant_route('dashboard.parent.show', ['id' => $p->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                       title="Lihat">
                                        <i class="fa-solid fa-eye text-[11px]"></i>
                                    </a>
                                    <a href="{{ tenant_route('dashboard.parent.edit', ['id' => $p->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors"
                                       title="Edit">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </a>
                                    <form method="POST" action="{{ tenant_route('dashboard.parent.destroy', ['id' => $p->id]) }}"
                                          onsubmit="return confirm('Hapus {{ $p->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-md transition-colors"
                                                title="Hapus">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center">
                            <i class="fa-solid fa-users text-3xl text-gray-300 dark:text-gray-600 mb-2"></i>
                            <p class="text-[13px] text-gray-500 dark:text-gray-400 mb-3">Belum ada data orang tua</p>
                            @if(Auth::user()->isAdmin())
                                <x-btn href="{{ tenant_route('dashboard.parent.create') }}" variant="primary" size="sm" icon="fa-plus">
                                    Tambah Orang Tua Pertama
                                </x-btn>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-card>

@endsection
