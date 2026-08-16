@extends('layouts.tenant')

@section('title', 'Setup Pesantren - Wizard')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">

        {{-- Header --}}
        <div class="mb-8 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-emerald-100 dark:bg-emerald-500/20 mb-4">
                <i class="fas fa-school text-emerald-600 dark:text-emerald-400 text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Setup Akademik Pesantren</h1>
            @if($currentProgram)
                <p class="text-gray-500 dark:text-gray-400 mt-1">Program: <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $currentProgram->name }}</span></p>
            @else
                <p class="text-gray-500 dark:text-gray-400 mt-1">Siapkan data dasar untuk mulai menggunakan sistem</p>
            @endif
        </div>

        {{-- Wizard Step Indicator — order/labels come from OnboardingStepRegistry (config/onboarding.php) --}}
        <div class="flex items-center justify-center mb-8">
            @php $programSlug = $currentProgram?->slug; @endphp
            @foreach ($wizardSteps as $i => $ws)
                @php
                    $isActive   = $ws['key'] === $step;
                    $isDone     = \App\Services\OnboardingStepRegistry::isStepComplete($ws['key'], $actualProgress, $programSlug);
                    $isUnlocked = \App\Services\OnboardingStepRegistry::isUnlocked($ws['key'], $actualProgress, $programSlug);
                @endphp

                {{-- Step bubble --}}
                <div class="flex flex-col items-center">
                    @if ($isUnlocked)
                        <a href="{{ route('dashboard.onboarding.wizard', ['step' => $ws['key']]) }}"
                           class="flex items-center justify-center w-10 h-10 rounded-full border-2 text-sm font-semibold transition-all
                               {{ $isActive  ? 'bg-emerald-600 border-emerald-600 text-white shadow-md scale-110' : '' }}
                               {{ $isDone && !$isActive ? 'bg-emerald-100 border-emerald-400 text-emerald-700' : '' }}
                               {{ !$isActive && !$isDone ? 'bg-white border-gray-300 text-gray-400' : '' }}">
                            @if ($isDone && !$isActive)
                                <i class="fas fa-check text-xs"></i>
                            @else
                                {{ $i + 1 }}
                            @endif
                        </a>
                    @else
                        <span title="Selesaikan langkah sebelumnya terlebih dahulu"
                              class="flex items-center justify-center w-10 h-10 rounded-full border-2 border-dashed border-gray-200 dark:border-gray-700 text-gray-300 dark:text-gray-600 cursor-not-allowed">
                            <i class="fas fa-lock text-xs"></i>
                        </span>
                    @endif
                    <span class="mt-1 text-xs font-medium
                        {{ $isActive ? 'text-emerald-700' : ($isDone ? 'text-emerald-600' : ($isUnlocked ? 'text-gray-400' : 'text-gray-300 dark:text-gray-600')) }}">
                        {{ $ws['label'] }}
                        @if (!$ws['required'])
                            <span class="text-gray-400 dark:text-gray-500">(opsional)</span>
                        @endif
                    </span>
                </div>

                @if (!$loop->last)
                    <div class="flex-1 h-0.5 mx-2 mt-[-14px]
                        {{ $isDone ? 'bg-emerald-400 dark:bg-emerald-500/50' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                @endif
            @endforeach
        </div>

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mb-4 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 p-4 flex items-start gap-3">
                <i class="fas fa-check-circle text-emerald-500 dark:text-emerald-400 mt-0.5"></i>
                <p class="text-emerald-800 dark:text-emerald-300 text-sm">{{ session('success') }}</p>
            </div>
        @endif
        @if (session('info'))
            <div class="mb-4 rounded-lg bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 p-4 flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-500 dark:text-blue-400 mt-0.5"></i>
                <p class="text-blue-800 dark:text-blue-300 text-sm">{{ session('info') }}</p>
            </div>
        @endif
        @if (session('warning'))
            <div class="mb-4 rounded-lg bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 p-4 flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-amber-500 dark:text-amber-400 mt-0.5"></i>
                <p class="text-amber-800 dark:text-amber-300 text-sm">{{ session('warning') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 p-4 flex items-start gap-3">
                <i class="fas fa-times-circle text-red-500 dark:text-red-400 mt-0.5"></i>
                <p class="text-red-800 dark:text-red-300 text-sm">{{ session('error') }}</p>
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 p-4">
                <ul class="list-disc list-inside text-red-700 dark:text-red-400 text-sm space-y-1">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ─── STEP: KELAS ─────────────────────────────────────────────── --}}
        @if ($step === 'kelas')
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-chalkboard text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-white font-bold text-lg">Buat Kelas</h2>
                        <p class="text-emerald-100 text-sm">Tambahkan kelas untuk setiap program yang dipilih</p>
                    </div>
                </div>
            </div>

            <form id="form-kelas" action="{{ route('dashboard.onboarding.wizard.store-kelas') }}" method="POST" class="p-6 pb-0">
                @csrf

                {{-- Existing kelas summary --}}
                @if ($kelasList->isNotEmpty())
                    <div class="mb-5 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30">
                        <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300 mb-2">
                            <i class="fas fa-check-circle mr-1"></i> {{ $kelasList->count() }} kelas sudah ada:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($kelasList as $k)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white dark:bg-gray-700 border border-emerald-300 dark:border-emerald-500/30 text-xs font-medium text-emerald-800 dark:text-emerald-300">
                                    {{ $k->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Bulk kelas rows --}}
                <div id="kelas-rows" class="space-y-3">
                    <div class="kelas-row grid grid-cols-12 gap-2 items-start">
                        <div class="col-span-4">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Program</label>
                            @if($currentProgram)
                                <input type="hidden" name="kelas[0][program_id]" value="{{ $currentProgram->id }}">
                                <div class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm bg-gray-50 dark:bg-gray-600 text-gray-700 dark:text-gray-300">
                                    <i class="fas fa-book-open mr-2"></i>{{ $currentProgram->name }}
                                </div>
                            @else
                                <select name="kelas[0][program_id]"
                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                                    @foreach ($tenantPrograms as $tp)
                                        <option value="{{ $tp->program->id }}">{{ $tp->program->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-span-5">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Nama Kelas <span class="text-red-500">*</span></label>
                            <input type="text" name="kelas[0][name]" placeholder=""
                                   class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                        <div class="col-span-3">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Keterangan</label>
                            <input type="text" name="kelas[0][description]" placeholder=""
                                   class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                        </div>
                    </div>
                </div>

                <button type="button" id="add-kelas-row"
                        class="mt-4 inline-flex items-center gap-2 text-sm text-emerald-700 dark:text-emerald-400 hover:text-emerald-900 dark:hover:text-emerald-300 font-medium">
                    <i class="fas fa-plus-circle"></i> Tambah Baris Kelas
                </button>
            </form>

            <div class="px-6 py-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    @if($currentProgram)
                        <a href="{{ route('dashboard.onboarding.program-setup-queue') }}" 
                           class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Program
                        </a>
                    @endif
                    <form action="{{ route('dashboard.onboarding.wizard.skip-step') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="step" value="kelas">
                        @if ($actualProgress['hasKelas'])
                            <button type="submit" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                Lewati <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        @else
                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">Kelas wajib diisi</span>
                        @endif
                    </form>
                </div>
                <button type="submit" form="form-kelas"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition">
                    Simpan Kelas <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        @endif

        {{-- ─── STEP: MAPEL ─────────────────────────────────────────────── --}}
        @if ($step === 'mapel')
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-500 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-book text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-white font-bold text-lg">Mata Pelajaran</h2>
                        <p class="text-blue-100 text-sm">Tambahkan mata pelajaran untuk setiap program</p>
                    </div>
                </div>
            </div>

            <form id="form-mapel" action="{{ route('dashboard.onboarding.wizard.store-mapel') }}" method="POST" class="p-6 pb-0">
                @csrf

                @if ($subjectsList->isNotEmpty())
                    <div class="mb-5 p-4 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30">
                        <p class="text-sm font-semibold text-blue-800 dark:text-blue-300 mb-2">
                            <i class="fas fa-check-circle mr-1"></i> {{ $subjectsList->count() }} mata pelajaran sudah ada:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($subjectsList as $s)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white dark:bg-gray-700 border border-blue-300 dark:border-blue-500/30 text-xs font-medium text-blue-800 dark:text-blue-300">
                                    {{ $s->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div id="mapel-rows" class="space-y-3">
                    <div class="mapel-row grid grid-cols-12 gap-2 items-start">
                        <div class="col-span-4">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Program</label>
                            @if($currentProgram)
                                <input type="hidden" name="mapel[0][program_id]" value="{{ $currentProgram->id }}">
                                <div class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm bg-gray-50 dark:bg-gray-600 text-gray-700 dark:text-gray-300">
                                    <i class="fas fa-book-open mr-2"></i>{{ $currentProgram->name }}
                                </div>
                            @else
                                <select name="mapel[0][program_id]"
                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-blue-500 focus:border-blue-500">
                                    @foreach ($tenantPrograms as $tp)
                                        <option value="{{ $tp->program->id }}">{{ $tp->program->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                        <div class="col-span-3">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Nama Mapel <span class="text-red-500">*</span></label>
                            <input type="text" name="mapel[0][name]" placeholder=""
                                   class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Kode</label>
                            <input type="text" name="mapel[0][code]" placeholder=""
                                   class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="col-span-3">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Keterangan</label>
                            <input type="text" name="mapel[0][description]" placeholder=""
                                   class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <button type="button" id="add-mapel-row"
                        class="mt-4 inline-flex items-center gap-2 text-sm text-blue-700 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 font-medium">
                    <i class="fas fa-plus-circle"></i> Tambah Baris Mata Pelajaran
                </button>
            </form>

            <div class="px-6 py-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    @if($currentProgram)
                        <a href="{{ route('dashboard.onboarding.program-setup-queue') }}" 
                           class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Program
                        </a>
                    @else
                        <a href="{{ route('dashboard.onboarding.wizard', ['step' => $previousStep ?? 'kelas']) }}"
                           class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    @endif
                    <form action="{{ route('dashboard.onboarding.wizard.skip-step') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="step" value="mapel">
                        @if ($actualProgress['hasSubject'])
                            <button type="submit" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                Lewati <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        @else
                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">Mapel wajib diisi</span>
                        @endif
                    </form>
                </div>
                <button type="submit" form="form-mapel"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition">
                    Simpan Mapel <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        @endif

        {{-- ─── STEP: USTADZ (Teacher) ──────────────────────────────────── --}}
        @if ($step === 'ustadz')
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-500 to-fuchsia-500 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-chalkboard-user text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-white font-bold text-lg">Tambah Ustadz</h2>
                        <p class="text-purple-100 text-sm">Ustadz harus ada sebelum membuat penugasan mengajar</p>
                    </div>
                </div>
            </div>

            <form id="form-ustadz" action="{{ route('dashboard.onboarding.wizard.store-ustadz') }}" method="POST" class="p-6 pb-0">
                @csrf

                @if ($ustadzList->isNotEmpty())
                    <div class="mb-5 p-4 rounded-xl bg-purple-50 dark:bg-purple-500/10 border border-purple-200 dark:border-purple-500/30">
                        <p class="text-sm font-semibold text-purple-800 dark:text-purple-300 mb-2">
                            <i class="fas fa-check-circle mr-1"></i> {{ $ustadzList->count() }} ustadz sudah ada:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($ustadzList as $u)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white dark:bg-gray-700 border border-purple-300 dark:border-purple-500/30 text-xs font-medium text-purple-800 dark:text-purple-300">
                                    {{ $u->user?->name ?? '-' }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-12 gap-3">
                    <div class="col-span-12 sm:col-span-5">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Nama Ustadz <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Email (opsional)</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Telepon (opsional)</label>
                        <input type="text" name="phone" value="{{ old('phone') }}"
                               class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-3">Akun login untuk ustadz akan dibuat otomatis.</p>
            </form>

            <div class="px-6 py-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard.onboarding.wizard', ['step' => $previousStep ?? 'mapel']) }}"
                       class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <form action="{{ route('dashboard.onboarding.wizard.skip-step') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="step" value="ustadz">
                        @if ($actualProgress['hasUstadz'])
                            <button type="submit" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                Lewati <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        @else
                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">Ustadz wajib diisi</span>
                        @endif
                    </form>
                </div>
                <button type="submit" form="form-ustadz"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold rounded-lg transition">
                    Simpan Ustadz <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        @endif

        {{-- ─── STEP: PENUGASAN (Teaching Assignment) ──────────────────── --}}
        @if ($step === 'penugasan')
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-violet-500 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-user-tie text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-white font-bold text-lg">Buat Penugasan Mengajar</h2>
                        <p class="text-indigo-100 text-sm">Tugaskan ustadz ke kelas dan mata pelajaran sebelum membuat jadwal</p>
                    </div>
                </div>
            </div>

            <form id="form-penugasan" action="{{ route('dashboard.onboarding.wizard.store-penugasan') }}" method="POST" class="p-6 pb-0">
                @csrf

                @if ($penugasanList->isNotEmpty())
                    <div class="mb-5 p-4 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-200 dark:border-indigo-500/30">
                        <p class="text-sm font-semibold text-indigo-800 dark:text-indigo-300 mb-2">
                            <i class="fas fa-check-circle mr-1"></i> {{ $penugasanList->count() }} penugasan sudah ada:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($penugasanList as $p)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white dark:bg-gray-700 border border-indigo-300 dark:border-indigo-500/30 text-xs font-medium text-indigo-800 dark:text-indigo-300">
                                    {{ $p->ustadz?->user?->name ?? '-' }} → {{ $p->kelas?->name ?? '-' }} ({{ $p->subject?->name ?? '-' }})
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div id="penugasan-rows" class="space-y-3">
                    <div class="penugasan-row grid grid-cols-12 gap-2 items-start">
                        <div class="col-span-4">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Ustadz <span class="text-red-500">*</span></label>
                            <select name="penugasan[0][ustadz_id]"
                                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Pilih Ustadz</option>
                                @foreach ($ustadzList as $u)
                                    <option value="{{ $u->id }}">{{ $u->user?->name ?? '-' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-4">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Kelas <span class="text-red-500">*</span></label>
                            <select name="penugasan[0][kelas_id]"
                                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Pilih Kelas</option>
                                @foreach ($kelasList as $k)
                                    <option value="{{ $k->id }}">{{ $k->name }} ({{ $k->program?->name ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-4">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Mapel <span class="text-red-500">*</span></label>
                            <select name="penugasan[0][subject_id]"
                                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Pilih Mapel</option>
                                @foreach ($subjectsList as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->program?->name ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <button type="button" id="add-penugasan-row"
                        class="mt-4 inline-flex items-center gap-2 text-sm text-indigo-700 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium">
                    <i class="fas fa-plus-circle"></i> Tambah Baris Penugasan
                </button>
            </form>

            <div class="px-6 py-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <a href="{{ route('dashboard.onboarding.wizard', ['step' => $previousStep ?? 'ustadz']) }}"
                       class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali
                    </a>
                    <form action="{{ route('dashboard.onboarding.wizard.skip-step') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="step" value="penugasan">
                        @if ($actualProgress['hasUstadzKelas'])
                            <button type="submit" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                Lewati <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        @else
                            <span class="text-xs text-gray-400 dark:text-gray-500 italic">Penugasan wajib diisi</span>
                        @endif
                    </form>
                </div>
                <button type="submit" form="form-penugasan"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition">
                    Simpan Penugasan <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        @endif

        {{-- ─── STEP: JADWAL ─────────────────────────────────────────────── --}}
        @if ($step === 'jadwal')
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                        <i class="fas fa-calendar-days text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-white font-bold text-lg">Buat Jadwal</h2>
                        <p class="text-amber-100 text-sm">Buat jadwal berdasarkan penugasan mengajar yang sudah ada</p>
                    </div>
                </div>
            </div>

            <form id="form-jadwal" action="{{ route('dashboard.onboarding.wizard.store-jadwal') }}" method="POST" class="p-6 pb-0">
                @csrf

                {{-- Existing jadwal summary --}}
                @if ($jadwalList->isNotEmpty())
                    <div class="mb-5 p-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30">
                        <p class="text-sm font-semibold text-amber-800 dark:text-amber-300 mb-2">
                            <i class="fas fa-check-circle mr-1"></i> {{ $jadwalList->count() }} jadwal sudah ada:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($jadwalList->take(5) as $j)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-white dark:bg-gray-700 border border-amber-300 dark:border-amber-500/30 text-xs font-medium text-amber-800 dark:text-amber-300">
                                    {{ $j->hari }} {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }} - {{ $j->kelas?->name ?? '-' }} ({{ $j->subject?->name ?? '-' }})
                                </span>
                            @endforeach
                            @if ($jadwalList->count() > 5)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-amber-100 dark:bg-amber-500/20 border border-amber-300 dark:border-amber-500/30 text-xs font-medium text-amber-700 dark:text-amber-300">
                                    +{{ $jadwalList->count() - 5 }} lagi
                                </span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Bulk jadwal rows — built from existing teaching assignments only --}}
                <div id="jadwal-rows" class="space-y-3">
                    <div class="jadwal-row grid grid-cols-12 gap-2 items-start">
                        <div class="col-span-6">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Penugasan Mengajar <span class="text-red-500">*</span></label>
                            <select name="jadwal[0][ustadz_kelas_id]"
                                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Pilih Penugasan</option>
                                @foreach ($penugasanList as $pg)
                                    <option value="{{ $pg->id }}">{{ $pg->kelas?->name ?? '-' }} — {{ $pg->subject?->name ?? '-' }} ({{ $pg->ustadz?->user?->name ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Hari <span class="text-red-500">*</span></label>
                            <select name="jadwal[0][hari]"
                                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-amber-500 focus:border-amber-500">
                                <option value="">Pilih</option>
                                @foreach ($hariList as $h)
                                    <option value="{{ $h }}">{{ $h }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                            <input type="time" name="jadwal[0][jam_mulai]" placeholder="08:00"
                                   class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-amber-500 focus:border-amber-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                            <input type="time" name="jadwal[0][jam_selesai]" placeholder="10:00"
                                   class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-amber-500 focus:border-amber-500">
                        </div>
                    </div>
                </div>

                <button type="button" id="add-jadwal-row"
                        class="mt-4 inline-flex items-center gap-2 text-sm text-amber-700 dark:text-amber-400 hover:text-amber-900 dark:hover:text-amber-300 font-medium">
                    <i class="fas fa-plus-circle"></i> Tambah Baris Jadwal
                </button>
            </form>

            <div class="px-6 py-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-4">
                    @if($currentProgram)
                        <a href="{{ route('dashboard.onboarding.program-setup-queue') }}" 
                           class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar Program
                        </a>
                    @else
                        <a href="{{ route('dashboard.onboarding.wizard', ['step' => $previousStep ?? 'penugasan']) }}"
                           class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    @endif
                    @if ($actualProgress['hasSchedule'])
                        <form action="{{ route('dashboard.onboarding.wizard.skip-step') }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="step" value="jadwal">
                            <button type="submit" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                                Lewati <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                        </form>
                    @else
                        <span class="text-xs text-gray-400 dark:text-gray-500 italic">Jadwal wajib diisi</span>
                    @endif
                </div>
                <button type="submit" form="form-jadwal"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg transition">
                    Simpan Jadwal <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
        @endif

        {{-- ─── STEP: RINGKASAN ─────────────────────────────────────────── --}}
        @if ($step === 'ringkasan')
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-teal-500 to-emerald-600 px-6 py-5 text-center">
                <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check-circle text-white text-2xl"></i>
                </div>
                <h2 class="text-white font-bold text-xl">Setup Selesai!</h2>
                <p class="text-teal-100 text-sm mt-1">Pesantren Anda sudah siap digunakan</p>
            </div>

            <div class="p-6">
                {{-- Summary checklist — order/labels come from OnboardingStepRegistry --}}
                @php
                    $items = [
                        ['label' => 'Program', 'done' => $actualProgress['hasProgram'], 'icon' => 'fa-layer-group'],
                    ];
                    foreach (\App\Services\OnboardingStepRegistry::flow($currentProgram?->slug) as $def) {
                        if ($def['key'] === 'ringkasan') {
                            continue;
                        }
                        $items[] = [
                            'label' => $def['label'],
                            'done' => \App\Services\OnboardingStepRegistry::isStepComplete($def['key'], $actualProgress, $currentProgram?->slug),
                            'icon' => $def['icon'] ?? 'fa-circle',
                        ];
                    }
                    $items[] = ['label' => 'Santri', 'done' => $actualProgress['hasSantri'], 'icon' => 'fa-user-graduate'];
                @endphp
                <ul class="space-y-3 mb-8">
                    @foreach ($items as $item)
                        <li class="flex items-center gap-3 p-3 rounded-xl {{ $item['done'] ? 'bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30' : 'bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600' }}">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center {{ $item['done'] ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' : 'bg-gray-200 dark:bg-gray-600 text-gray-400 dark:text-gray-500' }}">
                                <i class="fas {{ $item['icon'] }} text-sm"></i>
                            </div>
                            <span class="flex-1 text-sm font-medium {{ $item['done'] ? 'text-emerald-800 dark:text-emerald-300' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ $item['label'] }}
                            </span>
                            @if ($item['done'])
                                <i class="fas fa-check-circle text-emerald-500 dark:text-emerald-400"></i>
                            @else
                                <span class="text-xs text-gray-400 dark:text-gray-500 italic">Belum diisi</span>
                            @endif
                        </li>
                    @endforeach
                </ul>

                @if (!$actualProgress['is_complete'])
                    @php
                        $missingKey = \App\Services\OnboardingStepRegistry::firstIncompleteKey($actualProgress, $currentProgram?->slug);
                        $missingDef = $missingKey ? \App\Services\OnboardingStepRegistry::find($missingKey, $currentProgram?->slug) : null;
                    @endphp
                    <div class="mb-6 p-4 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 flex items-start gap-3">
                        <i class="fas fa-exclamation-triangle text-amber-500 dark:text-amber-400 mt-0.5"></i>
                        <div>
                            <p class="text-amber-800 dark:text-amber-300 text-sm font-semibold">Setup belum lengkap</p>
                            <p class="text-amber-700 dark:text-amber-400 text-xs mt-0.5">Kelas, Mata Pelajaran, Ustadz, Penugasan Mengajar, dan Jadwal wajib diisi sebelum dapat menggunakan modul akademik.</p>
                        </div>
                    </div>
                    @if ($missingKey)
                        <div class="flex flex-col gap-3">
                            <a href="{{ route('dashboard.onboarding.wizard', ['step' => $missingKey]) }}"
                               class="flex items-center justify-center gap-2 px-5 py-3 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition">
                                <i class="fas {{ $missingDef['icon'] ?? 'fa-arrow-right' }}"></i> {{ $missingDef['title'] ?? 'Lanjutkan Setup' }}
                            </a>
                        </div>
                    @endif
                @else
                    <a href="{{ route('dashboard.index') }}"
                       class="flex items-center justify-center gap-2 w-full px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold transition shadow-sm">
                        <i class="fas fa-rocket"></i> Mulai Gunakan Sistem
                    </a>
                    <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-3">
                        Langkah berikutnya (opsional): Tambah Santri
                    </p>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

<script>
(function () {
    const counters = {};

    function nextIdx(containerId) {
        counters[containerId] = (counters[containerId] ?? 1) + 1;
        return counters[containerId];
    }

    function addRow(containerId, templateFn) {
        const container = document.getElementById(containerId);
        if (!container) return;
        const i = nextIdx(containerId);
        const wrap = document.createElement('div');
        wrap.className = 'flex items-start gap-2';
        wrap.innerHTML = templateFn(i);
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'flex-shrink-0 mt-1 w-7 h-7 flex items-center justify-center rounded-full text-gray-400 hover:text-red-500 hover:bg-red-50 transition';
        removeBtn.innerHTML = '<i class="fas fa-times text-xs"></i>';
        removeBtn.addEventListener('click', () => wrap.remove());
        wrap.appendChild(removeBtn);
        container.appendChild(wrap);
        wrap.querySelector('input, select')?.focus();
    }

    const programOptions = `@foreach ($tenantPrograms as $tp)<option value="{{ $tp->program->id }}">{{ $tp->program->name }}</option>@endforeach`;
    const currentProgramId = @if($currentProgram) {{ $currentProgram->id }} @else null @endif;
    const currentProgramName = @if($currentProgram) '{{ $currentProgram->name }}' @else null @endif;

    // Kelas
    const addKelas = document.getElementById('add-kelas-row');
    if (addKelas) {
        addKelas.addEventListener('click', () => {
            addRow('kelas-rows', (i) => {
                let programField = '';
                if (currentProgramId) {
                    programField = `
                        <input type="hidden" name="kelas[${i}][program_id]" value="${currentProgramId}">
                        <div class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm bg-gray-50 dark:bg-gray-600 text-gray-700 dark:text-gray-300">
                            <i class="fas fa-book-open mr-2"></i>${currentProgramName}
                        </div>`;
                } else {
                    programField = `<select name="kelas[${i}][program_id]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">${programOptions}</select>`;
                }
                
                return `
                <div class="flex-1 grid grid-cols-12 gap-2">
                    <div class="col-span-4">
                        ${programField}
                    </div>
                    <div class="col-span-5">
                        <input type="text" name="kelas[${i}][name]" placeholder="" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <div class="col-span-3">
                        <input type="text" name="kelas[${i}][description]" placeholder="" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                </div>`;
            });
        });
    }

    // Mapel
    const addMapel = document.getElementById('add-mapel-row');
    if (addMapel) {
        addMapel.addEventListener('click', () => {
            addRow('mapel-rows', (i) => {
                let programField = '';
                if (currentProgramId) {
                    programField = `
                        <input type="hidden" name="mapel[${i}][program_id]" value="${currentProgramId}">
                        <div class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 px-3 py-2 text-sm bg-gray-50 dark:bg-gray-600 text-gray-700 dark:text-gray-300">
                            <i class="fas fa-book-open mr-2"></i>${currentProgramName}
                        </div>`;
                } else {
                    programField = `<select name="mapel[${i}][program_id]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-blue-500 focus:border-blue-500">${programOptions}</select>`;
                }
                
                return `
                <div class="flex-1 grid grid-cols-12 gap-2">
                    <div class="col-span-4">
                        ${programField}
                    </div>
                    <div class="col-span-3">
                        <input type="text" name="mapel[${i}][name]" placeholder="" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="col-span-2">
                        <input type="text" name="mapel[${i}][code]" placeholder="" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="col-span-3">
                        <input type="text" name="mapel[${i}][description]" placeholder="" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>`;
            });
        });
    }

    // Penugasan
    const ustadzOptions = `@foreach ($ustadzList as $u)<option value="{{ $u->id }}">{{ $u->user?->name ?? '-' }}</option>@endforeach`;
    const kelasOptions = `@foreach ($kelasList as $k)<option value="{{ $k->id }}">{{ $k->name }} ({{ $k->program?->name ?? '-' }})</option>@endforeach`;
    const subjectOptions = `@foreach ($subjectsList as $s)<option value="{{ $s->id }}">{{ $s->name }} ({{ $s->program?->name ?? '-' }})</option>@endforeach`;

    const addPenugasan = document.getElementById('add-penugasan-row');
    if (addPenugasan) {
        addPenugasan.addEventListener('click', () => {
            addRow('penugasan-rows', (i) => { return `
                <div class="flex-1 grid grid-cols-12 gap-2">
                    <div class="col-span-4">
                        <select name="penugasan[${i}][ustadz_id]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Pilih Ustadz</option>${ustadzOptions}
                        </select>
                    </div>
                    <div class="col-span-4">
                        <select name="penugasan[${i}][kelas_id]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Pilih Kelas</option>${kelasOptions}
                        </select>
                    </div>
                    <div class="col-span-4">
                        <select name="penugasan[${i}][subject_id]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Pilih Mapel</option>${subjectOptions}
                        </select>
                    </div>
                </div>`; });
        });
    }

    // Jadwal
    const penugasanOptions = `@foreach ($penugasanList as $pg)<option value="{{ $pg->id }}">{{ $pg->kelas?->name ?? '-' }} — {{ $pg->subject?->name ?? '-' }} ({{ $pg->ustadz?->user?->name ?? '-' }})</option>@endforeach`;
    const hariOptions = `@foreach ($hariList as $h)<option value="{{ $h }}">{{ $h }}</option>@endforeach`;

    const addJadwal = document.getElementById('add-jadwal-row');
    if (addJadwal) {
        addJadwal.addEventListener('click', () => {
            addRow('jadwal-rows', (i) => { return `
                <div class="flex-1 grid grid-cols-12 gap-2">
                    <div class="col-span-6">
                        <select name="jadwal[${i}][ustadz_kelas_id]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-amber-500 focus:border-amber-500">
                            <option value="">Pilih Penugasan</option>${penugasanOptions}
                        </select>
                    </div>
                    <div class="col-span-2">
                        <select name="jadwal[${i}][hari]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-amber-500 focus:border-amber-500">
                            <option value="">Pilih</option>${hariOptions}
                        </select>
                    </div>
                    <div class="col-span-2">
                        <input type="time" name="jadwal[${i}][jam_mulai]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-amber-500 focus:border-amber-500">
                    </div>
                    <div class="col-span-2">
                        <input type="time" name="jadwal[${i}][jam_selesai]" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 text-sm focus:ring-amber-500 focus:border-amber-500">
                    </div>
                </div>`; });
        });
    }
})();
</script>
@endsection
