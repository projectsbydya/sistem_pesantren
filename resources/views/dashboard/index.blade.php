@extends('layouts.tenant')

@section('title', 'Dashboard')

@section('content')
@php
$user   = Auth::user();
$tenant = App\Services\TenantService::getTenant();
$nav    = App\Services\NavigationGateService::forUser($user);

$firstProgram     = $tenant?->activePrograms()->first();
$firstProgramSlug = $firstProgram?->slug ?? null;
$tenantId         = $tenant?->id;

// Get all active programs with their setup status.
// Delegates to TenantSetupService::getProgramProgress() (registry-driven:
// Kelas → Mapel → Ustadz → Penugasan → Jadwal) — no duplicated/hardcoded
// step logic here, so this stays in sync with the onboarding wizard.
$allPrograms = $tenant?->activePrograms()->get() ?? collect();
$programSetupStatus = [];
foreach ($allPrograms as $program) {
    $programSetupStatus[$program->id] = \App\Services\TenantSetupService::getProgramProgress($program->id, $tenantId);
}

$totalSantri  = $tenantId ? \App\Models\Santri::where('tenant_id', $tenantId)->count()  : 0;
$totalUstadz  = $tenantId ? \App\Models\Ustadz::where('tenant_id', $tenantId)->where('status', \App\Models\Ustadz::STATUS_ACTIVE)->count() : 0;
$totalKelas   = $tenantId ? \App\Models\Kelas::where('tenant_id', $tenantId)->count()   : 0;
$totalMapel   = $tenantId ? \App\Models\Subject::where('tenant_id', $tenantId)->count() : 0;
$totalJadwal  = $tenantId ? \App\Models\Schedule::where('tenant_id', $tenantId)->count(): 0;

$totalTunggakan = $tenantId
    ? \App\Models\Bill::where('tenant_id', $tenantId)->where('status', '!=', 'paid')->sum('amount')
    : 0;
$totalPaidMonth = $tenantId
    ? \App\Models\Bill::where('tenant_id', $tenantId)->where('status', 'paid')
        ->whereMonth('updated_at', now()->month)->whereYear('updated_at', now()->year)->sum('amount')
    : 0;
$totalTagihan = $tenantId
    ? \App\Models\Bill::where('tenant_id', $tenantId)->sum('amount')
    : 0;

$santriTerbaru = $tenantId
    ? \App\Models\Santri::where('tenant_id', $tenantId)->latest()->take(5)->get()
    : collect();

$actualProgress = \App\Services\TenantSetupService::getActualProgress();
// Gate on the REQUIRED step chain only (Kelas → Mapel → Ustadz → Penugasan →
// Jadwal). Do NOT use percentage here: percentage also counts optional
// post-onboarding steps (e.g. Santri) which may never reach 100%, which
// would otherwise keep the "Setup Akademik"/"Program Pesantren" widgets
// showing forever even after the user finishes onboarding.
$showOnboarding = !($actualProgress['is_complete'] ?? false);

$hariMap = [0=>'Ahad',1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu'];
$hariIni = $hariMap[now()->dayOfWeek] ?? 'Senin';
$jadwalHariIni = $tenantId
    ? \App\Models\Schedule::where('tenant_id', $tenantId)
        ->where('hari', $hariIni)
        ->with(['kelas', 'subject', 'ustadz.user'])
        ->orderBy('jam_mulai')
        ->take(5)
        ->get()
    : collect();
@endphp

{{-- ===== USTADZ DASHBOARD ===== --}}
@if($nav->isUstadz())
@include('dashboard.partials.dashboard-ustadz')
@elseif($nav->isStudent() || $nav->isParent())
@include('dashboard.partials.dashboard-santri')
@else

{{-- ===== PESANTREN HEADER CARD ===== --}}
<div class="mb-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-emerald-600 to-teal-600 p-6 text-white shadow-lg">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center shrink-0">
                <i class="fa-solid fa-mosque text-2xl text-white"></i>
            </div>
            <div>
                <div class="flex items-center gap-2 mb-0.5">
                    <h1 class="text-xl font-bold">{{ $tenant?->name ?? '' }}</h1>
                    @if($tenant?->is_active)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/20 text-xs font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-300"></span> Aktif
                        </span>
                    @endif
                </div>
                <p class="text-emerald-100 text-sm">{{ $tenant?->slug ?? '' }} • {{ ucfirst($user->role ?? 'Admin') }}</p>
                <p class="text-emerald-100 text-xs mt-0.5">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-center px-4 py-2 bg-white/10 rounded-xl">
                <p class="text-2xl font-bold">{{ $totalSantri }}</p>
                <p class="text-xs text-emerald-100 mt-0.5">Total Santri</p>
            </div>
            <div class="text-center px-4 py-2 bg-white/10 rounded-xl">
                <p class="text-2xl font-bold">{{ $totalUstadz }}</p>
                <p class="text-xs text-emerald-100 mt-0.5">Ustadz Aktif</p>
            </div>
            @if(($tenant?->is_trial ?? false) && $tenant->trial_ends_at && \Carbon\Carbon::parse($tenant->trial_ends_at)->isFuture())
                @php
                    $trialEnd  = \Carbon\Carbon::parse($tenant->trial_ends_at);
                    $diff      = now()->diff($trialEnd);
                @endphp
                <div class="text-center px-4 py-2 bg-amber-500/30 rounded-xl border border-amber-300/30">
                    <p class="text-xs text-amber-100 font-medium mb-0.5">Trial berakhir</p>
                    <p class="text-xs font-bold text-white tabular-nums">
                        {{ $diff->days }}h {{ $diff->h }}j {{ $diff->i }}m {{ $diff->s }}d
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ===== SETUP PROGRESS WIDGET ===== --}}
@if($showOnboarding)
    @include('dashboard.partials.setup-progress')
@endif

{{-- ===== PROGRAMS STATUS ===== --}}
@if($showOnboarding && $allPrograms->count() > 0)
<div class="mb-6">
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Program Pesantren</h2>
        @if($allPrograms->count() > 1)
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ $allPrograms->count() }} program aktif</span>
        @endif
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($allPrograms as $program)
            @php
                $status = $programSetupStatus[$program->id] ?? ['is_complete' => false, 'percentage' => 0];
            @endphp
            <div class="p-4 rounded-xl border-2 {{ $status['is_complete'] ? 'border-emerald-200 bg-emerald-50/50 dark:bg-emerald-500/10 dark:border-emerald-500/30' : 'border-gray-200 bg-white dark:bg-gray-800 dark:border-gray-600' }}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $status['is_complete'] ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-blue-100 dark:bg-blue-500/20 text-blue-600 dark:text-blue-400' }}">
                            <i class="fa-solid {{ $status['is_complete'] ? 'fa-check-circle' : 'fa-book-open' }}"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $program->name }}</h3>
                            @if($status['is_complete'])
                                <span class="text-xs text-emerald-600 dark:text-emerald-400">Setup lengkap</span>
                            @else
                                <span class="text-xs text-amber-600 dark:text-amber-400">Setup belum lengkap</span>
                            @endif
                        </div>
                    </div>
                    @if($status['is_complete'])
                        <i class="fa-solid fa-check-circle text-emerald-500 dark:text-emerald-400"></i>
                    @else
                        <i class="fa-solid fa-clock text-amber-500 dark:text-amber-400"></i>
                    @endif
                </div>
                
                {{-- Progress Bar --}}
                <div class="mb-3">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-gray-600 dark:text-gray-400">Progress Setup</span>
                        <span class="text-xs font-semibold {{ $status['is_complete'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-600 dark:text-gray-400' }}">{{ $status['percentage'] }}%</span>
                    </div>
                    <div class="h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden">
                        <div class="h-full {{ $status['is_complete'] ? 'bg-emerald-500' : 'bg-blue-500' }} rounded-full transition-all duration-500" style="width: {{ $status['percentage'] }}%"></div>
                    </div>
                </div>
                
                {{-- Stats --}}
                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                    <div class="p-2 rounded-lg {{ $status['is_complete'] ? 'bg-white dark:bg-gray-700' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                        <div class="font-semibold {{ $status['is_complete'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300' }}">{{ $status['kelas_count'] }}</div>
                        <div class="text-gray-500 dark:text-gray-400">Kelas</div>
                    </div>
                    <div class="p-2 rounded-lg {{ $status['is_complete'] ? 'bg-white dark:bg-gray-700' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                        <div class="font-semibold {{ $status['is_complete'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300' }}">{{ $status['subject_count'] }}</div>
                        <div class="text-gray-500 dark:text-gray-400">Mapel</div>
                    </div>
                    <div class="p-2 rounded-lg {{ $status['is_complete'] ? 'bg-white dark:bg-gray-700' : 'bg-gray-50 dark:bg-gray-700/50' }}">
                        <div class="font-semibold {{ $status['is_complete'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300' }}">{{ $status['schedule_count'] }}</div>
                        <div class="text-gray-500 dark:text-gray-400">Jadwal</div>
                    </div>
                </div>
                
                {{-- Action Button --}}
                @if(!$status['is_complete'])
                    <a href="{{ route('dashboard.onboarding.program-setup.start', $program->id) }}" class="mt-3 block w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg text-center transition-colors">
                        <i class="fa-solid fa-play mr-1"></i> Lanjutkan Setup
                    </a>
                @else
                    <a href="{{ tenant_route('dashboard.akademik.kelas.index', $program->slug) }}" class="mt-3 block w-full py-2 px-4 bg-emerald-100 dark:bg-emerald-500/20 hover:bg-emerald-200 dark:hover:bg-emerald-500/30 text-emerald-700 dark:text-emerald-300 text-sm font-medium rounded-lg text-center transition-colors">
                        <i class="fa-solid fa-eye mr-1"></i> Lihat Detail
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- ===== STATS GRID ===== --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @if($nav->canViewSantri())
    <x-stat-card
        title="Total Santri"
        value="{{ $totalSantri }}"
        icon="fa-users"
        color="emerald"
        :href="tenant_route('dashboard.santri.index')" />
    @endif

    @if($nav->canViewSantri())
    <x-stat-card
        title="Ustadz Aktif"
        value="{{ $totalUstadz }}"
        icon="fa-chalkboard-user"
        color="blue"
        :href="tenant_route('dashboard.ustadz.index')" />
    @endif

    <x-stat-card
        title="Kelas"
        value="{{ $totalKelas }}"
        icon="fa-chalkboard"
        color="teal"
        :href="$firstProgramSlug ? tenant_route('dashboard.akademik.kelas.index', $firstProgramSlug) : '#'" />

    <x-stat-card
        title="Mata Pelajaran"
        value="{{ $totalMapel }}"
        icon="fa-book"
        color="purple"
        :href="$firstProgramSlug ? tenant_route('dashboard.akademik.subjects.index', $firstProgramSlug) : '#'" />
</div>

{{-- ===== MAIN GRID ===== --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Jadwal Hari Ini --}}
        <x-card title="Jadwal Hari Ini" subtitle="{{ now()->isoFormat('dddd, D MMMM Y') }}">
            @if($jadwalHariIni->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">Tidak ada jadwal hari ini.</p>
            @else
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($jadwalHariIni as $jadwal)
                        <div class="flex items-center gap-3 py-2.5">
                            <div class="w-16 text-center shrink-0">
                                <p class="text-xs font-semibold text-emerald-600">{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}</p>
                                <p class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</p>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $jadwal->subject?->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $jadwal->kelas?->name ?? '-' }} • {{ $jadwal->ustadz?->user?->name ?? '-' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            <x-slot:footer>
                @if($firstProgramSlug)
                    <a href="{{ tenant_route('dashboard.akademik.jadwal.index', $firstProgramSlug) }}" class="text-sm text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 font-medium">
                        Lihat semua jadwal →
                    </a>
                @endif
            </x-slot:footer>
        </x-card>

        {{-- Ringkasan Keuangan --}}
        @if($nav->canViewSpp())
        <x-card title="Ringkasan Keuangan" subtitle="{{ now()->isoFormat('MMMM Y') }}">
            <x-slot:actions>
                <a href="{{ tenant_route('dashboard.keuangan.tagihan') }}" class="text-xs text-emerald-600 dark:text-emerald-400 font-medium hover:underline">Lihat semua</a>
            </x-slot:actions>
            <div class="grid grid-cols-2 gap-3">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                    <div class="w-9 h-9 rounded-lg text-gray-600 bg-gray-100 dark:bg-gray-800 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-file-invoice-dollar text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Total Tagihan</p>
                        <p class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                    <div class="w-9 h-9 rounded-lg text-emerald-600 bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-check text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Dibayar Bulan Ini</p>
                        <p class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($totalPaidMonth, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                    <div class="w-9 h-9 rounded-lg text-red-600 bg-red-50 dark:bg-red-500/10 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Tunggakan</p>
                        <p class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                    <div class="w-9 h-9 rounded-lg text-blue-600 bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-users text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-500 dark:text-gray-400">Total Santri</p>
                        <p class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">{{ $totalSantri }}</p>
                    </div>
                </div>
            </div>
        </x-card>
        @endif

        {{-- Santri Terbaru --}}
        @if($nav->canViewSantri() && $santriTerbaru->isNotEmpty())
        <x-card title="Santri Terbaru" subtitle="Baru saja didaftarkan">
            <x-slot:actions>
                <a href="{{ tenant_route('dashboard.santri.index') }}" class="text-xs text-emerald-600 dark:text-emerald-400 font-medium hover:underline">Lihat semua</a>
            </x-slot:actions>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($santriTerbaru as $santri)
                    <div class="flex items-center gap-3 py-2.5">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0">
                            {{ strtoupper(substr($santri->name, 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[13px] font-medium text-gray-900 dark:text-gray-100 truncate">{{ $santri->name }}</p>
                            <p class="text-[11px] text-gray-500">{{ $santri->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
        @endif

    </div>

    {{-- RIGHT --}}
    <div class="space-y-6">

        {{-- Quick Actions --}}
        @if($nav->canCreateSantri() || $nav->canCreateUstadz() || $nav->canViewAbsensiSantri() || $nav->canViewSpp())
        <x-card title="Aksi Cepat">
            <div class="space-y-2">
                @if($nav->canCreateSantri())
                <x-btn href="{{ tenant_route('dashboard.santri.create') }}" variant="primary" icon="fa-user-plus" class="w-full justify-start">
                    Tambah Santri
                </x-btn>
                @endif
                @if($nav->canCreateUstadz())
                <x-btn href="{{ tenant_route('dashboard.ustadz.create') }}" variant="secondary" icon="fa-chalkboard-user" class="w-full justify-start">
                    Tambah Ustadz
                </x-btn>
                @endif
                @if($firstProgramSlug && $nav->canViewAbsensiSantri())
                <x-btn href="{{ tenant_route('dashboard.akademik.absensi.index', $firstProgramSlug) }}" variant="secondary" icon="fa-clipboard-check" class="w-full justify-start">
                    Input Absensi
                </x-btn>
                @endif
                @if($nav->canViewSpp())
                <x-btn href="{{ tenant_route('dashboard.keuangan.tagihan') }}" variant="secondary" icon="fa-money-bill-wave" class="w-full justify-start">
                    Kelola Keuangan
                </x-btn>
                @endif
            </div>
        </x-card>
        @endif

        {{-- Langkah Berikutnya (Post-Onboarding Checklist) --}}
        @if($totalUstadz === 0 || $firstProgramSlug && \App\Models\UstadzKelas::where('tenant_id', $tenantId)->count() === 0 || $totalSantri === 0)
        <x-card title="Langkah Berikutnya" subtitle="Lengkapi data untuk operasional penuh">
            <div class="space-y-3">
                @if($totalUstadz === 0)
                    <a href="{{ tenant_route('dashboard.ustadz.create') }}" class="flex items-center gap-3 p-3 rounded-xl bg-violet-50 dark:bg-violet-500/10 border border-violet-200 dark:border-violet-500/30 hover:bg-violet-100 dark:hover:bg-violet-500/20 transition">
                        <div class="w-8 h-8 rounded-full bg-white dark:bg-gray-700 border border-violet-300 dark:border-violet-500/30 flex items-center justify-center">
                            <i class="fas fa-square text-violet-400 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-violet-900 dark:text-violet-300">Tambah Ustadz</p>
                            <p class="text-xs text-violet-600 dark:text-violet-400">Belum ada ustadz terdaftar</p>
                        </div>
                        <i class="fas fa-chevron-right text-violet-400 text-xs"></i>
                    </a>
                @else
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center">
                            <i class="fas fa-check text-emerald-600 dark:text-emerald-400 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 line-through">Tambah Ustadz</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $totalUstadz }} ustadz terdaftar</p>
                        </div>
                    </div>
                @endif

                @php
                    $ustadzKelasCount = \App\Models\UstadzKelas::where('tenant_id', $tenantId)->count();
                @endphp
                @if($ustadzKelasCount === 0 && $firstProgramSlug)
                    <a href="{{ tenant_route('dashboard.akademik.penugasan.create', $firstProgramSlug) }}" class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 hover:bg-blue-100 dark:hover:bg-blue-500/20 transition">
                        <div class="w-8 h-8 rounded-full bg-white dark:bg-gray-700 border border-blue-300 dark:border-blue-500/30 flex items-center justify-center">
                            <i class="fas fa-square text-blue-400 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-300">Assign Ustadz ke Kelas</p>
                            <p class="text-xs text-blue-600 dark:text-blue-400">Tentukan pengajar untuk setiap kelas</p>
                        </div>
                        <i class="fas fa-chevron-right text-blue-400 text-xs"></i>
                    </a>
                @elseif($ustadzKelasCount > 0)
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center">
                            <i class="fas fa-check text-emerald-600 dark:text-emerald-400 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 line-through">Assign Ustadz ke Kelas</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $ustadzKelasCount }} penugasan aktif</p>
                        </div>
                    </div>
                @endif

                @if($totalSantri === 0)
                    <a href="{{ tenant_route('dashboard.santri.create') }}" class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition">
                        <div class="w-8 h-8 rounded-full bg-white dark:bg-gray-700 border border-emerald-300 dark:border-emerald-500/30 flex items-center justify-center">
                            <i class="fas fa-square text-emerald-400 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-emerald-900 dark:text-emerald-300">Tambah Santri</p>
                            <p class="text-xs text-emerald-600 dark:text-emerald-400">Belum ada santri terdaftar</p>
                        </div>
                        <i class="fas fa-chevron-right text-emerald-400 text-xs"></i>
                    </a>
                @else
                    <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center">
                            <i class="fas fa-check text-emerald-600 dark:text-emerald-400 text-xs"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 line-through">Tambah Santri</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $totalSantri }} santri terdaftar</p>
                        </div>
                    </div>
                @endif
            </div>
        </x-card>
        @endif

        {{-- Ringkasan Akademik --}}
        <x-card title="Ringkasan Akademik">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <i class="fa-solid fa-chalkboard text-teal-500 w-4 text-center"></i> Kelas
                    </span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $totalKelas }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <i class="fa-solid fa-book text-blue-500 w-4 text-center"></i> Mata Pelajaran
                    </span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $totalMapel }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-days text-amber-500 w-4 text-center"></i> Jadwal
                    </span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $totalJadwal }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400 flex items-center gap-2">
                        <i class="fa-solid fa-chalkboard-user text-violet-500 w-4 text-center"></i> Ustadz
                    </span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $totalUstadz }}</span>
                </div>
            </div>
        </x-card>

        {{-- Kalender Mini --}}
        <x-card title="Kalender" subtitle="{{ now()->isoFormat('MMMM Y') }}">
            <div class="grid grid-cols-7 gap-0.5 text-center">
                @foreach(['M','S','S','R','K','J','S'] as $day)
                    <div class="py-1.5 text-[10px] font-semibold text-gray-400 uppercase">{{ $day }}</div>
                @endforeach
                @php $daysInMonth = now()->daysInMonth; $offset = now()->startOfMonth()->dayOfWeek; @endphp
                @for($i = 0; $i < $offset; $i++)
                    <div></div>
                @endfor
                @for($i = 1; $i <= $daysInMonth; $i++)
                    @php $isToday = $i == now()->day; @endphp
                    <div class="py-1.5 text-[12px] rounded-lg cursor-default
                        {{ $isToday ? 'bg-emerald-600 text-white font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                        {{ $i }}
                    </div>
                @endfor
            </div>
        </x-card>

    </div>
</div>
@endif
@endsection
