@extends('layouts.tenant')
@section('title', 'Live Pengajian')
@section('content')

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900 dark:text-white">Live Pengajian</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Jadwal & tautan siaran langsung pengajian</p>
    </div>
    @if(auth()->user()->isAdmin())
        <a href="{{ tenant_route('dashboard.live-pengajian.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-lg hover:bg-emerald-700 transition-colors">
            <i class="fa-solid fa-plus text-xs"></i> Tambah Jadwal
        </a>
    @endif
</div>

{{-- Flash --}}
@if(session('success'))
    <x-alert type="success" class="mb-5">{{ session('success') }}</x-alert>
@endif

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $countLive }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sedang Live</p>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $countScheduled }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Terjadwal</p>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 text-center">
        <p class="text-2xl font-bold text-gray-500 dark:text-gray-400">{{ $countEnded }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Selesai</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" class="flex flex-wrap gap-3 mb-5">
    <select name="status" onchange="this.form.submit()"
            class="border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        <option value="">Semua Status</option>
        <option value="live" {{ request('status') === 'live' ? 'selected' : '' }}>Sedang Live</option>
        <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Terjadwal</option>
        <option value="ended" {{ request('status') === 'ended' ? 'selected' : '' }}>Selesai</option>
    </select>
    <select name="platform" onchange="this.form.submit()"
            class="border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
        <option value="">Semua Platform</option>
        <option value="zoom" {{ request('platform') === 'zoom' ? 'selected' : '' }}>Zoom</option>
        <option value="gmeet" {{ request('platform') === 'gmeet' ? 'selected' : '' }}>Google Meet</option>
        <option value="youtube" {{ request('platform') === 'youtube' ? 'selected' : '' }}>YouTube Live</option>
    </select>
    @if(request('status') || request('platform'))
        <a href="{{ tenant_route('dashboard.live-pengajian.index') }}"
           class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-700 rounded-lg transition-colors">
            <i class="fa-solid fa-xmark mr-1"></i> Reset
        </a>
    @endif
</form>

{{-- Cards Grid --}}
@if($items->isEmpty())
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
        <i class="fa-solid fa-video text-4xl text-gray-300 dark:text-gray-700 mb-3"></i>
        <p class="text-gray-500 dark:text-gray-400">Belum ada jadwal live pengajian.</p>
        @if(auth()->user()->isAdmin())
            <a href="{{ tenant_route('dashboard.live-pengajian.create') }}"
               class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                <i class="fa-solid fa-plus text-xs"></i> Tambah Sekarang
            </a>
        @endif
    </div>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($items as $item)
            @php
                $platformColors = ['zoom' => 'blue', 'gmeet' => 'green', 'youtube' => 'red'];
                $platformLabels = \App\Models\LivePengajian::PLATFORM_LABELS;
                $statusLabels   = \App\Models\LivePengajian::STATUS_LABELS;
                $color = $platformColors[$item->platform] ?? 'gray';
            @endphp
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden flex flex-col">
                {{-- Platform header --}}
                <div class="px-4 py-3 flex items-center justify-between
                    {{ $item->platform === 'youtube' ? 'bg-red-50 dark:bg-red-900/20 border-b border-red-100 dark:border-red-900/40'
                    : ($item->platform === 'zoom' ? 'bg-blue-50 dark:bg-blue-900/20 border-b border-blue-100 dark:border-blue-900/40'
                    : 'bg-green-50 dark:bg-green-900/20 border-b border-green-100 dark:border-green-900/40') }}">
                    <div class="flex items-center gap-2">
                        @if($item->platform === 'youtube')
                            <i class="fa-brands fa-youtube text-red-600 dark:text-red-400 text-lg"></i>
                        @elseif($item->platform === 'zoom')
                            <i class="fa-solid fa-video text-blue-600 dark:text-blue-400 text-base"></i>
                        @else
                            <i class="fa-solid fa-video text-green-600 dark:text-green-400 text-base"></i>
                        @endif
                        <span class="text-xs font-semibold
                            {{ $item->platform === 'youtube' ? 'text-red-700 dark:text-red-300'
                            : ($item->platform === 'zoom' ? 'text-blue-700 dark:text-blue-300'
                            : 'text-green-700 dark:text-green-300') }}">
                            {{ $platformLabels[$item->platform] ?? $item->platform }}
                        </span>
                    </div>
                    {{-- Status badge --}}
                    @if($item->status === 'live')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-red-500 text-white animate-pulse">
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span> LIVE
                        </span>
                    @elseif($item->status === 'scheduled')
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                            Terjadwal
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                            Selesai
                        </span>
                    @endif
                </div>

                <div class="p-4 flex-1 flex flex-col gap-3">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm leading-snug">{{ $item->judul }}</h3>

                    @if($item->deskripsi)
                        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ $item->deskripsi }}</p>
                    @endif

                    <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-calendar w-3.5 text-center text-gray-400"></i>
                            {{ $item->jadwal_mulai->format('d M Y, H:i') }}
                            @if($item->jadwal_selesai)
                                – {{ $item->jadwal_selesai->format('H:i') }}
                            @endif
                        </div>
                        @if($item->meeting_id)
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-hashtag w-3.5 text-center text-gray-400"></i>
                                ID: {{ $item->meeting_id }}
                                @if($item->passcode)
                                    &nbsp;| Passcode: {{ $item->passcode }}
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Join button --}}
                    <div class="mt-auto pt-2 flex items-center gap-2">
                        <a href="{{ $item->link_url }}" target="_blank" rel="noopener noreferrer"
                           class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold transition-colors
                               {{ $item->platform === 'youtube' ? 'bg-red-600 hover:bg-red-700 text-white'
                               : ($item->platform === 'zoom' ? 'bg-blue-600 hover:bg-blue-700 text-white'
                               : 'bg-green-600 hover:bg-green-700 text-white') }}">
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                            {{ $item->status === 'live' ? 'Gabung Sekarang' : 'Buka Link' }}
                        </a>

                        @if(auth()->user()->isAdmin())
                            <a href="{{ tenant_route('dashboard.live-pengajian.edit', ['livePengajian' => $item->id]) }}"
                               class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 border border-gray-200 dark:border-gray-700 rounded-lg transition-colors">
                                <i class="fa-solid fa-pen text-xs"></i>
                            </a>
                            <form method="POST" action="{{ tenant_route('dashboard.live-pengajian.destroy', ['livePengajian' => $item->id]) }}"
                                  onsubmit="return confirm('Hapus jadwal ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-rose-600 dark:hover:text-rose-400 border border-gray-200 dark:border-gray-700 rounded-lg transition-colors">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </form>
                        @endif
                    </div>

                    {{-- Admin: quick status change --}}
                    @if(auth()->user()->isAdmin() && $item->status !== 'ended')
                        <div class="flex gap-2 pt-1 border-t border-gray-100 dark:border-gray-800">
                            @if($item->status === 'scheduled')
                                <form method="POST" action="{{ tenant_route('dashboard.live-pengajian.set-status', ['livePengajian' => $item->id]) }}" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="status" value="live">
                                    <button type="submit" class="w-full px-3 py-1.5 text-xs font-semibold bg-red-500 hover:bg-red-600 text-white rounded-lg transition-colors">
                                        <i class="fa-solid fa-circle-dot mr-1"></i> Mulai Live
                                    </button>
                                </form>
                            @elseif($item->status === 'live')
                                <form method="POST" action="{{ tenant_route('dashboard.live-pengajian.set-status', ['livePengajian' => $item->id]) }}" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="status" value="ended">
                                    <button type="submit" class="w-full px-3 py-1.5 text-xs font-semibold bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors">
                                        <i class="fa-solid fa-stop mr-1"></i> Akhiri Live
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $items->links() }}</div>
@endif

@endsection
