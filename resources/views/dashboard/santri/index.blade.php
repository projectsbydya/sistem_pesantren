@extends('layouts.tenant')

@section('title', 'Data Santri')
@section('page-title', 'Data Santri')
@section('breadcrumb')
    <span class="text-gray-600 dark:text-gray-300">Santri</span>
@endsection

@section('content')

{{-- Credentials download banner --}}
@if(session('show_credentials_download'))
    <div class="mb-6 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-check-circle text-emerald-500 mt-0.5"></i>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">Akun Berhasil Dibuat!</h4>
                <p class="text-sm text-emerald-700 dark:text-emerald-300 mt-1">{{ session('success') }}</p>
                <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">File berisi akun santri dan orang tua. Password wajib diganti saat login pertama.</p>
                <div class="mt-3">
                    <a href="{{ tenant_route('dashboard.santri.credentials.download') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                        <i class="fa-solid fa-file-excel"></i>
                        Download File Akun (.XLSX)
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- Page Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Data Santri</h1>
        <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-0.5">
            Kelola data santri {{ \App\Services\TenantService::getTenant()?->name }}
        </p>
    </div>
    @can('create', App\Models\Santri::class)
        <x-btn href="{{ tenant_route('dashboard.santri.create') }}" variant="primary" icon="fa-plus">
            Tambah Santri
        </x-btn>
    @endcan
</div>

{{-- Search & Filters --}}
<div class="flex flex-col sm:flex-row gap-3 mb-5">
    <div class="flex-1">
        <div class="relative">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 text-xs"></i>
            <input type="text" placeholder="Cari nama, NIS, atau kelas..."
                   class="w-full pl-9 pr-4 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                          text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500
                          focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 dark:focus:border-emerald-500 transition-colors">
        </div>
    </div>
    <div class="flex gap-2">
        <select class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                       text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
            <option value="">Semua Jenjang</option>
            <option value="raudhah">Raudhah</option>
            <option value="ibtidaiyah">Ibtidaiyah</option>
            <option value="tsanawiyah">Tsanawiyah</option>
            <option value="aliyah">Aliyah</option>
        </select>
        <select class="px-3 py-2 text-[13px] bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg
                       text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-colors">
            <option value="">Semua Status</option>
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
        </select>
    </div>
</div>

{{-- Table --}}
<div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden transition-colors">
    @if($santri->isEmpty())
        {{-- Empty State --}}
        <div class="text-center py-16 px-6">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                <i class="fa-solid fa-users text-gray-400 dark:text-gray-500 text-xl"></i>
            </div>
            <h3 class="text-[15px] font-semibold text-gray-900 dark:text-gray-100 mb-1">Belum ada data santri</h3>
            <p class="text-[13px] text-gray-500 dark:text-gray-400 mb-5 max-w-sm mx-auto">
                Mulai dengan menambahkan data santri pertama untuk pesantren ini.
            </p>
            @can('create', App\Models\Santri::class)
                <x-btn href="{{ tenant_route('dashboard.santri.create') }}" variant="primary" size="sm" icon="fa-plus">
                    Tambah Santri
                </x-btn>
            @endcan
        </div>
    @else
        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">NIS</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gender</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenjang</th>
                        <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Terdaftar</th>
                        <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($santri as $s)
                    <tr class="border-b border-gray-50 dark:border-gray-800/50 hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors group">
                        <td class="px-4 py-3">
                            <span class="font-mono text-[12px] text-gray-500 dark:text-gray-400">{{ $s->nis }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-[12px] font-bold shrink-0">
                                    {{ strtoupper(substr($s->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100 truncate">{{ $s->name }}</p>
                                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-mono truncate">{{ $s->user?->email ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $s->gender === 'L' ? 'info' : 'pink' }}" size="sm">
                                {{ $s->gender === 'L' ? 'L' : 'P' }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3">
                            <x-badge variant="{{ $s->status === 'active' ? 'success' : 'default' }}" size="sm" dot>
                                {{ $s->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-[13px] text-gray-600 dark:text-gray-400">{{ $s->school_level ?? '-' }}</td>
                        <td class="px-4 py-3 text-[12px] text-gray-400 dark:text-gray-500">{{ $s->created_at?->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ tenant_route('dashboard.santri.show', ['id' => $s->id]) }}"
                                   class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition-colors"
                                   title="Lihat">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                </a>
                                @can('update', $s)
                                    <a href="{{ tenant_route('dashboard.santri.edit', ['id' => $s->id]) }}"
                                       class="p-1.5 text-gray-400 hover:text-amber-600 dark:hover:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-500/10 rounded-md transition-colors"
                                       title="Edit">
                                        <i class="fa-solid fa-pen text-[11px]"></i>
                                    </a>
                                @endcan
                                @can('delete', $s)
                                    <form method="POST" action="{{ tenant_route('dashboard.santri.destroy', ['id' => $s->id]) }}"
                                          onsubmit="return confirm('Hapus {{ $s->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-md transition-colors"
                                                title="Hapus">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <p class="text-[12px] text-gray-400 dark:text-gray-500">
                Menampilkan <span class="font-medium text-gray-600 dark:text-gray-300">{{ $santri->count() }}</span> santri
            </p>
        </div>
    @endif
</div>

@endsection
