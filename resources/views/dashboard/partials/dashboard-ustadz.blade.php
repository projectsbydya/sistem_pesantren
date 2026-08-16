@php
use App\Models\Schedule;
use App\Models\AbsensiSantri;
use App\Models\Program;
use Carbon\Carbon;

$ustadz      = $user->ustadz;
$today       = Carbon::today();
$hariIndo    = Schedule::HARI[$today->dayOfWeekIso - 1] ?? null;

// Load active programs for dynamic links from tenant
$tenant = App\Services\TenantService::getTenant();
$activePrograms = $tenant?->activePrograms()->get() ?? collect([]);
$firstProgram = $activePrograms->first();

// Jadwal hari ini milik ustadz ini
$jadwalHariIni = $ustadz
    ? Schedule::with(['ustadzKelas.kelas', 'ustadzKelas.subject', 'ustadzKelas.program', 'program'])
        ->whereIn('ustadz_kelas_id', $ustadz->ustadzKelas()->pluck('id'))
        ->where('hari', $hariIndo)
        ->orderBy('jam_mulai')
        ->get()
    : collect();

// Jadwal minggu ini (semua hari) untuk tampilan ringkasan
$jadwalMingguIni = $ustadz
    ? Schedule::with(['ustadzKelas.kelas', 'ustadzKelas.subject', 'program'])
        ->whereIn('ustadz_kelas_id', $ustadz->ustadzKelas()->pluck('id'))
        ->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Ahad')")
        ->orderBy('jam_mulai')
        ->get()
        ->groupBy('hari')
    : collect();
@endphp

{{-- Header Ustadz --}}
<div class="mb-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-emerald-600 to-teal-600 p-6 text-white shadow-lg">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center shrink-0">
                <i class="fa-solid fa-chalkboard-user text-2xl text-white"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold">{{ $user->name }}</h1>
                <p class="text-emerald-100 text-sm mt-0.5">{{ $tenant?->name }}</p>
                <p class="text-emerald-100 text-xs mt-0.5">{{ $today->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-center px-4 py-2 bg-white/10 rounded-xl">
                <p class="text-2xl font-bold">{{ $jadwalHariIni->count() }}</p>
                <p class="text-xs text-emerald-100 mt-0.5">Jadwal Hari Ini</p>
            </div>
            <div class="text-center px-4 py-2 bg-white/10 rounded-xl">
                <p class="text-2xl font-bold">{{ $ustadz?->ustadzKelas()->count() ?? 0 }}</p>
                <p class="text-xs text-emerald-100 mt-0.5">Kelas Diampu</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- LEFT: Jadwal Hari Ini --}}
    <div class="lg:col-span-2 space-y-6">

        <x-card title="Jadwal Hari Ini" subtitle="{{ $hariIndo ? $hariIndo . ', ' . $today->format('d M Y') : $today->format('d M Y') }}">
            @if($jadwalHariIni->isEmpty())
                <div class="py-10 text-center">
                    <i class="fa-solid fa-calendar-check text-3xl text-gray-300 dark:text-gray-600 mb-3"></i>
                    <p class="text-[14px] font-medium text-gray-600 dark:text-gray-400">Tidak ada jadwal hari ini</p>
                    <p class="text-[12px] text-gray-400 dark:text-gray-500 mt-1">Nikmati hari istirahat Anda</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($jadwalHariIni as $jadwal)
                        @php
                            $program     = $jadwal->program;
                            $programSlug = $program?->slug;
                            $jamMulai    = Carbon::parse($jadwal->jam_mulai);
                            $jamSelesai  = Carbon::parse($jadwal->jam_selesai);
                            $sedangBerlangsung = $today->between(
                                Carbon::today()->setTimeFrom($jamMulai),
                                Carbon::today()->setTimeFrom($jamSelesai)
                            );
                        @endphp
                        <div class="flex items-start gap-4 p-4 rounded-xl border
                            {{ $sedangBerlangsung
                                ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-700'
                                : 'bg-gray-50 dark:bg-gray-800/40 border-gray-200 dark:border-gray-700' }}">
                            <div class="shrink-0 text-center w-16">
                                <p class="text-[13px] font-bold {{ $sedangBerlangsung ? 'text-emerald-700 dark:text-emerald-400' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ substr($jadwal->jam_mulai, 0, 5) }}
                                </p>
                                <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ substr($jadwal->jam_selesai, 0, 5) }}</p>
                                @if($sedangBerlangsung)
                                    <span class="mt-1 inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Live
                                    </span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[14px] font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $jadwal->ustadzKelas?->subject?->name ?? $jadwal->mata_pelajaran ?? '-' }}
                                </p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[12px] text-gray-500 dark:text-gray-400">
                                        <i class="fa-solid fa-chalkboard text-[10px] mr-1"></i>
                                        {{ $jadwal->ustadzKelas?->kelas?->name ?? $jadwal->kelas ?? '-' }}
                                    </span>
                                    <span class="text-gray-300 dark:text-gray-600">·</span>
                                    <span class="text-[11px] font-medium px-2 py-0.5 rounded-full
                                        {{ $program ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400' }}">
                                        {{ strtoupper($programSlug) }}
                                    </span>
                                </div>
                            </div>
                            <div class="shrink-0 flex flex-col gap-1.5">
                                @can('viewAny', \App\Models\AbsensiSantri::class)
                                <a href="{{ tenant_route('dashboard.akademik.absensi.index', ['programSlug' => $programSlug]) }}?jadwal_id={{ $jadwal->id }}&tanggal={{ $today->toDateString() }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-medium bg-emerald-600 hover:bg-emerald-700 text-white transition-colors">
                                    <i class="fa-solid fa-clipboard-list text-[11px]"></i>
                                    Absensi
                                </a>
                                @endcan
                                @can('viewAny', \App\Models\Nilai::class)
                                <a href="{{ tenant_route('dashboard.akademik.nilai.dari-jadwal', ['programSlug' => $programSlug, 'jadwalId' => $jadwal->id]) }}?tanggal={{ $today->toDateString() }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[12px] font-medium bg-blue-600 hover:bg-blue-700 text-white transition-colors">
                                    <i class="fa-solid fa-chart-line text-[11px]"></i>
                                    Nilai
                                </a>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        {{-- Jadwal Minggu Ini --}}
        <x-card title="Jadwal Minggu Ini" subtitle="Semua hari">
            @if($jadwalMingguIni->isEmpty())
                <p class="text-sm text-gray-400 dark:text-gray-500 py-4 text-center">Belum ada jadwal yang ditetapkan.</p>
            @else
                <div class="space-y-2">
                    @foreach(\App\Models\Schedule::HARI as $hari)
                        @if(isset($jadwalMingguIni[$hari]))
                            <div>
                                <p class="text-[11px] font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider px-1 mb-1">{{ $hari }}</p>
                                <div class="space-y-1">
                                    @foreach($jadwalMingguIni[$hari] as $j)
                                        @php $pSlug = $j->program?->slug; @endphp
                                        <div class="flex items-center gap-3 px-3 py-2 rounded-lg bg-gray-50 dark:bg-gray-800/40">
                                            <span class="font-mono text-[12px] text-emerald-700 dark:text-emerald-400 w-24 shrink-0">
                                                {{ substr($j->jam_mulai,0,5) }}–{{ substr($j->jam_selesai,0,5) }}
                                            </span>
                                            <span class="text-[13px] text-gray-800 dark:text-gray-200 flex-1 truncate">
                                                {{ $j->ustadzKelas?->subject?->name ?? $j->mata_pelajaran ?? '-' }}
                                            </span>
                                            <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                                {{ $j->ustadzKelas?->kelas?->name ?? $j->kelas ?? '-' }}
                                            </span>
                                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded
                                                {{ $pSlug ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400' }}">
                                                {{ strtoupper($pSlug) }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </x-card>

    </div>

    {{-- RIGHT: Quick Actions + Info --}}
    <div class="space-y-6">

        <x-card title="Aksi Cepat">
            <div class="space-y-2">
                @can('viewAny', \App\Models\AbsensiSantri::class)
                    @foreach($activePrograms as $program)
                    <x-btn href="{{ tenant_route('dashboard.akademik.absensi.index', ['programSlug' => $program->slug]) }}" variant="{{ $loop->first ? 'primary' : 'secondary' }}" icon="fa-clipboard-list" class="w-full justify-start">
                        Absensi {{ $program->name }}
                    </x-btn>
                    @endforeach
                @endcan
                @can('viewAny', \App\Models\Nilai::class)
                    @if($firstProgram)
                    <x-btn href="{{ tenant_route('dashboard.akademik.nilai.index', ['programSlug' => $firstProgram->slug]) }}" variant="secondary" icon="fa-chart-line" class="w-full justify-start">
                        Input Nilai
                    </x-btn>
                    @endif
                @endcan
                @can('viewAny', \App\Models\AbsensiUstadz::class)
                <x-btn href="{{ tenant_route('dashboard.sdm.absensi-ustadz.index') }}" variant="secondary" icon="fa-clipboard-user" class="w-full justify-start">
                    Absensi Saya
                </x-btn>
                @endcan
            </div>
        </x-card>

        {{-- Profil Ustadz --}}
        @if($ustadz)
        <x-card title="Profil Saya">
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center">
                        <i class="fa-solid fa-user text-emerald-600 dark:text-emerald-400 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ $ustadz->subjects->pluck('name')->implode(', ') ?: 'Pengajar' }}</p>
                    </div>
                </div>
                @if($ustadz->performa !== null)
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[12px] text-gray-500 dark:text-gray-400">Performa</span>
                        <span class="text-[12px] font-semibold text-{{ $ustadz->getPerformaColor() }}-600 dark:text-{{ $ustadz->getPerformaColor() }}-400">
                            {{ $ustadz->performa }}% — {{ $ustadz->getPerformaLabel() }}
                        </span>
                    </div>
                    <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $ustadz->getPerformaColor() }}-500 rounded-full" style="width: {{ $ustadz->performa }}%"></div>
                    </div>
                </div>
                @endif
                <div class="pt-2 border-t border-gray-100 dark:border-gray-800 grid grid-cols-2 gap-2">
                    <div class="text-center py-2 bg-gray-50 dark:bg-gray-800/40 rounded-lg">
                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $ustadz->ustadzKelas()->count() }}</p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500">Kelas Diampu</p>
                    </div>
                    <div class="text-center py-2 bg-gray-50 dark:bg-gray-800/40 rounded-lg">
                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $jadwalMingguIni->flatten()->count() }}</p>
                        <p class="text-[10px] text-gray-400 dark:text-gray-500">Jadwal/Minggu</p>
                    </div>
                </div>
            </div>
        </x-card>
        @endif

    </div>
</div>
