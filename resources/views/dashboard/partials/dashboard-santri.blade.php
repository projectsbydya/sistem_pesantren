@php
use Carbon\Carbon;
$santri = $user->santri ?? ($user->parent?->santri()->first() ?? null);
$today  = Carbon::today();
@endphp

{{-- Header Santri/Orang Tua --}}
<div class="mb-6 rounded-2xl bg-gradient-to-r from-blue-600 via-blue-600 to-indigo-600 p-6 text-white shadow-lg">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center shrink-0">
                <i class="fa-solid fa-{{ $nav->isParent() ? 'people-roof' : 'user-graduate' }} text-2xl text-white"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold">{{ $user->name }}</h1>
                <p class="text-blue-100 text-sm mt-0.5">{{ $tenant?->name }}</p>
                <p class="text-blue-100 text-xs mt-0.5">{{ $today->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
        </div>
        @if($santri)
        <div class="flex items-center gap-3">
            <div class="text-center px-4 py-2 bg-white/10 rounded-xl">
                <p class="text-sm font-bold">{{ $santri->name }}</p>
                <p class="text-xs text-blue-100 mt-0.5">{{ $santri->kelas?->name ?? 'Santri' }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">

        <x-card title="Selamat Datang" subtitle="{{ now()->isoFormat('dddd, D MMMM Y') }}">
            <div class="py-6 text-center">
                <i class="fa-solid fa-mosque text-5xl text-emerald-500 dark:text-emerald-400 mb-4"></i>
                <p class="text-[15px] font-semibold text-gray-800 dark:text-gray-200">Assalamu'alaikum, {{ $user->name }}</p>
                <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-1">{{ $tenant?->name }}</p>
            </div>
        </x-card>

    </div>

    <div class="space-y-6">
        @if($santri)
        <x-card title="Profil Santri">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <i class="fa-solid fa-user-graduate text-blue-600 dark:text-blue-400 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">{{ $santri->name }}</p>
                        <p class="text-[11px] text-gray-400">{{ $santri->kelas?->name ?? '-' }}</p>
                    </div>
                </div>
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ tenant_route('dashboard.santri.show', $santri->id) }}"
                       class="inline-flex items-center gap-1.5 text-sm text-emerald-600 dark:text-emerald-400 font-medium hover:underline">
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                        Lihat Detail Profil
                    </a>
                </div>
            </div>
        </x-card>
        @endif

        <x-card title="Live Pengajian">
            <a href="{{ tenant_route('dashboard.live-pengajian.index') }}"
               class="flex items-center gap-3 px-3 py-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors">
                <i class="fa-solid fa-circle-play text-emerald-600 dark:text-emerald-400 text-xl"></i>
                <span class="text-[13px] font-medium text-emerald-700 dark:text-emerald-300">Lihat Live Pengajian</span>
            </a>
        </x-card>
    </div>
</div>
