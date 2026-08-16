@extends('layouts.tenant')

@section('title', 'Antrian Setup Program - Setup Pesantren')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-800">
    <div class="container mx-auto px-4 py-8 max-w-7xl">
        {{-- Header Section --}}
        <div class="mb-10">
            {{-- Progress Bar --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wide">Progress Setup</span>
                    <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/20 px-3 py-1 rounded-full">
                        Langkah 2 dari {{ count($tenantPrograms) + 1 }}
                    </span>
                </div>
                <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden shadow-inner">
                    <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-600 rounded-full transition-all duration-700 ease-out shadow-sm" 
                         style="width: {{ (1 / (count($tenantPrograms) + 1)) * 100 }}%"></div>
                </div>
            </div>

            {{-- Title and Info --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white mb-4 shadow-lg">
                    <i class="fa-solid fa-list-check text-xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-3">Setup Program Pesantren</h1>
                <p class="text-gray-600 dark:text-gray-400 text-lg max-w-2xl mx-auto">
                    Atur setiap program yang telah dipilih untuk memulai sistem akademik pesantren Anda
                </p>
            </div>

            {{-- Info Card --}}
            <div class="bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-500/10 dark:to-orange-500/10 border border-amber-200 dark:border-amber-500/30 rounded-2xl p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center">
                        <i class="fa-solid fa-lightbulb text-amber-600 dark:text-amber-400"></i>
                    </div>
                    <div>
                        <h3 class="font-semibold text-amber-900 dark:text-amber-300 mb-1">Panduan Setup Program</h3>
                        <p class="text-sm text-amber-800 dark:text-amber-400 leading-relaxed">
                            Setiap program perlu disetup secara terpisah (Kelas, Mata Pelajaran, Jadwal). 
                            Program harus diselesaikan secara berurutan sebelum melanjutkan ke program berikutnya.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Error Message --}}
        @if($errors->has('program'))
            <div class="mb-6 p-4 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-red-600 dark:text-red-400"></i>
                <p class="text-sm text-red-700 dark:text-red-400">{{ $errors->first('program') }}</p>
            </div>
        @endif

        {{-- Program Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
            @foreach($tenantPrograms as $index => $tenantProgram)
                @php
                    $program = $tenantProgram->program;
                    $isCompleted = $setupProgress[$program->id]['is_complete'] ?? false;
                    $progressPercentage = $setupProgress[$program->id]['percentage'] ?? 0;
                    $canStartSetup = !$isCompleted && ($index === 0 || $setupProgress[$tenantPrograms[$index-1]->program_id]['is_complete'] ?? true);
                    $isCurrent = !$isCompleted && $canStartSetup;
                @endphp
                
                <div class="relative group">
                    @if($canStartSetup)
                        <a href="{{ route('dashboard.onboarding.program-setup.start', $program->id) }}" 
                           class="block h-full">
                    @endif
                    
                    <div class="h-full p-6 rounded-2xl border-2 transition-all duration-300 flex flex-col
                         {{ $isCompleted 
                             ? 'border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-500/10 dark:to-teal-500/10 dark:border-emerald-500/30 shadow-sm' 
                             : ($canStartSetup 
                                 ? 'border-blue-200 bg-white dark:bg-gray-800 dark:border-blue-500/30 shadow-md hover:shadow-xl hover:-translate-y-1' 
                                 : 'border-gray-200 bg-gray-50 dark:bg-gray-800/50 dark:border-gray-600 opacity-60') }}
                         {{ $isCurrent ? 'ring-2 ring-blue-500 ring-offset-2 dark:ring-offset-gray-800' : '' }}">
                        
                        {{-- Status Badge --}}
                        <div class="absolute -top-3 -right-3">
                            @if($isCompleted)
                                <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-lg">
                                    <i class="fa-solid fa-check text-sm"></i>
                                </div>
                            @elseif($isCurrent)
                                <div class="px-3 py-1 bg-blue-500 text-white text-xs font-bold rounded-full shadow-lg animate-pulse">
                                    Aktif
                                </div>
                            @else
                                <div class="w-8 h-8 rounded-full bg-gray-400 text-white flex items-center justify-center shadow-lg">
                                    <i class="fa-solid fa-lock text-sm"></i>
                                </div>
                            @endif
                        </div>

                        {{-- Card Content --}}
                        <div class="flex flex-col h-full">
                            {{-- Program Header --}}
                            <div class="flex items-start gap-4 mb-4">
                                <div class="w-14 h-14 rounded-xl flex items-center justify-center shrink-0 shadow-sm
                                    {{ $isCompleted 
                                        ? 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' 
                                        : ($canStartSetup 
                                            ? 'bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-500/20 dark:to-indigo-500/20 text-blue-600 dark:text-blue-400' 
                                            : 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500') }}">
                                    @if($isCompleted)
                                        <i class="fa-solid fa-check-circle text-lg"></i>
                                    @else
                                        <i class="fa-solid fa-book-open text-lg"></i>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-lg text-gray-900 dark:text-gray-100 mb-1 leading-tight">
                                        {{ $program->name }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 leading-relaxed">
                                        {{ $program->description ?? 'Program untuk jenjang ' . $program->name }}
                                    </p>
                                </div>
                            </div>

                            {{-- Progress Section --}}
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wide">
                                        Progress Setup
                                    </span>
                                    <span class="text-sm font-bold 
                                        {{ $isCompleted 
                                            ? 'text-emerald-600 dark:text-emerald-400' 
                                            : ($canStartSetup 
                                                ? 'text-blue-600 dark:text-blue-400' 
                                                : 'text-gray-500 dark:text-gray-400') }}">
                                        {{ $progressPercentage }}%
                                    </span>
                                </div>
                                <div class="h-2 bg-gray-200 dark:bg-gray-600 rounded-full overflow-hidden shadow-inner">
                                    <div class="h-full rounded-full transition-all duration-500 ease-out shadow-sm
                                        {{ $isCompleted 
                                            ? 'bg-gradient-to-r from-emerald-500 to-teal-600' 
                                            : ($canStartSetup 
                                                ? 'bg-gradient-to-r from-blue-500 to-indigo-600' 
                                                : 'bg-gradient-to-r from-gray-400 to-gray-500') }}" 
                                         style="width: {{ $progressPercentage }}%"></div>
                                </div>
                            </div>

                            {{-- Stats or Action --}}
                            @if($isCompleted)
                                <div class="mt-auto pt-4 border-t border-emerald-200 dark:border-emerald-500/30">
                                    <div class="grid grid-cols-3 gap-3 text-center">
                                        <div class="bg-white dark:bg-gray-700 rounded-lg p-2">
                                            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ $setupProgress[$program->id]['kelas_count'] ?? 0 }}
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Kelas</div>
                                        </div>
                                        <div class="bg-white dark:bg-gray-700 rounded-lg p-2">
                                            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ $setupProgress[$program->id]['subject_count'] ?? 0 }}
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Mapel</div>
                                        </div>
                                        <div class="bg-white dark:bg-gray-700 rounded-lg p-2">
                                            <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ $setupProgress[$program->id]['schedule_count'] ?? 0 }}
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Jadwal</div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="mt-auto">
                                    @if($canStartSetup)
                                        <div class="flex items-center justify-center py-3 px-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl font-semibold shadow-lg">
                                            <i class="fa-solid fa-play mr-2"></i>
                                            Mulai Setup
                                        </div>
                                    @else
                                        <div class="flex items-center justify-center py-3 px-4 bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-xl font-medium">
                                            <i class="fa-solid fa-lock mr-2"></i>
                                            {{ $index > 0 ? 'Tunggu Program Sebelumnya' : 'Tidak Tersedia' }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    @if($canStartSetup)
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Completion Section --}}
        @if($allProgramsCompleted)
            <div class="bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-500/10 dark:to-teal-500/10 border-2 border-emerald-200 dark:border-emerald-500/30 rounded-3xl p-8 text-center shadow-lg">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center mx-auto mb-6 shadow-lg">
                    <i class="fa-solid fa-check-circle text-white text-3xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-emerald-900 dark:text-emerald-300 mb-3">
                    🎉 Semua Program Siap Digunakan!
                </h2>
                <p class="text-emerald-800 dark:text-emerald-400 mb-6 text-lg">
                    Selamat! Semua program telah disetup dengan lengkap dan siap untuk operasional.
                </p>
                <a href="{{ route('dashboard.index') }}" 
                   class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-1">
                    <i class="fa-solid fa-rocket text-xl"></i>
                    Mulai Gunakan Sistem
                </a>
            </div>
        @endif

        {{-- Navigation --}}
        <div class="mt-10 flex items-center justify-between">
            <a href="{{ route('dashboard.onboarding.programs') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 font-medium transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Pilih Program
            </a>
            
            @if($allProgramsCompleted)
                <a href="{{ route('dashboard.index') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl transition-all duration-300 shadow-md hover:shadow-lg">
                    Selesai Setup
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
