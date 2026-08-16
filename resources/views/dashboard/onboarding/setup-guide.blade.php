@extends('layouts.tenant')

@section('title', 'Setup Guide - Pesantren')

@section('content')
<div class="min-h-screen p-4">
    <div class="w-full max-w-5xl mx-auto">
        {{-- Header Section --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-route text-lg"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Setup Pesantren</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Lengkapi setup untuk mencapai status Siap Operasional</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left: Progress Overview --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Progress Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold text-gray-900 dark:text-gray-100">Progress Setup</h2>
                        <span class="text-sm font-medium {{ $percentage >= 100 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $percentage }}% Selesai
                        </span>
                    </div>
                    
                    {{-- Progress Bar --}}
                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden mb-6">
                        <div class="h-full {{ $percentage >= 100 ? 'bg-emerald-500' : 'bg-emerald-500' }} rounded-full transition-all duration-700" 
                             style="width: {{ $percentage }}%"></div>
                    </div>

                    @if($progress->setup_status === 'siap_operasional')
                        <div class="bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/30 rounded-xl p-4 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <i class="fa-solid fa-check-circle text-lg"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-emerald-900 dark:text-emerald-300">Siap Operasional!</p>
                                <p class="text-sm text-emerald-700 dark:text-emerald-400">Pesantren Anda sudah siap untuk digunakan.</p>
                            </div>
                        </div>
                    @else
                        {{-- Next Step Card --}}
                        @if($nextStep)
                            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-500/10 dark:to-teal-500/10 border border-emerald-200 dark:border-emerald-500/30 rounded-xl p-5">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                        <i class="fa-solid {{ $nextStep['icon'] }} text-lg"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-medium text-emerald-600 dark:text-emerald-400 uppercase tracking-wide mb-1">Langkah Berikutnya</p>
                                        <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ $nextStep['title'] }}</h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">{{ $nextStep['description'] }}</p>
                                        <a href="{{ tenant_route($nextStep['route'], $nextStep['params'] ?? []) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                                            Mulai Langkah Ini
                                            <i class="fa-solid fa-arrow-right ml-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- All Steps --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                    <h2 class="font-semibold text-gray-900 dark:text-gray-100 mb-6">Semua Langkah</h2>
                    
                    <div class="space-y-4">
                        @foreach($allSteps as $step)
                            @php
                                $isComplete = $step['done'];
                                $isNext = !$isComplete && ($nextStep && $nextStep['key'] === $step['key']);
                            @endphp
                            
                            <div class="flex items-center gap-4 p-4 rounded-xl {{ $isComplete ? 'bg-emerald-50/50 dark:bg-emerald-500/10' : ($isNext ? 'bg-amber-50/50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30' : 'bg-gray-50 dark:bg-gray-700') }} transition-all">
                                {{-- Icon/Check --}}
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                                    {{ $isComplete ? 'bg-emerald-500 text-white' : ($isNext ? 'bg-amber-500 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-500 dark:text-gray-300') }}">
                                    @if($isComplete)
                                        <i class="fa-solid fa-check"></i>
                                    @else
                                        <i class="fa-solid {{ $step['icon'] }}"></i>
                                    @endif
                                </div>
                                
                                {{-- Content --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-medium text-gray-900 dark:text-gray-100">{{ $step['label'] }}</h3>
                                        @if($isComplete)
                                            <span class="text-xs px-2 py-0.5 bg-emerald-100 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 rounded-full">Selesai</span>
                                        @elseif($isNext)
                                            <span class="text-xs px-2 py-0.5 bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 rounded-full">Berikutnya</span>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Action --}}
                                @if($isNext)
                                    <a href="{{ tenant_route($nextStep['route'], $nextStep['params'] ?? []) }}" 
                                       class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300 text-sm font-medium">
                                        Mulai <i class="fa-solid fa-arrow-right ml-1"></i>
                                    </a>
                                @elseif(!$isComplete)
                                    <span class="text-gray-400 dark:text-gray-500 text-sm">Menunggu</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Right: Sidebar --}}
            <div class="space-y-6">
                {{-- Status Card --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Status Setup</h3>
                    
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-3 h-3 rounded-full {{ $progress->setup_status === 'siap_operasional' ? 'bg-emerald-500' : 'bg-amber-500' }}"></div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $progress->setup_status === 'siap_operasional' ? 'Siap Operasional' : 'Dalam Proses' }}
                        </span>
                    </div>
                    
                    @if($progress->setup_status === 'siap_operasional')
                        <a href="{{ route('dashboard.index') }}" 
                           class="block w-full text-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-colors">
                            Ke Dashboard
                            <i class="fa-solid fa-arrow-right ml-1"></i>
                        </a>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                            Selesaikan semua langkah untuk mencapai status Siap Operasional.
                        </p>
                        
                        @if($nextStep)
                            <a href="{{ tenant_route($nextStep['route'], $nextStep['params'] ?? []) }}" 
                               class="block w-full text-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium rounded-xl transition-colors">
                                Lanjutkan Setup
                            </a>
                        @endif
                    @endif
                </div>

                {{-- Selected Programs --}}
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">Program Aktif</h3>
                        <a href="{{ route('dashboard.onboarding.programs') }}" class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300">
                            Ubah
                        </a>
                    </div>
                    
                    <div class="space-y-2">
                        @forelse($tenantPrograms as $tp)
                            <div class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-100 dark:border-gray-600">
                                <i class="fa-solid fa-check-circle text-emerald-500 text-sm"></i>
                                <span class="text-sm text-gray-700 dark:text-gray-200">{{ $tp->program->name }}</span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-300">Belum memilih program</p>
                        @endforelse
                    </div>
                </div>

                {{-- Skip Option --}}
                @if($progress->setup_status !== 'siap_operasional')
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4 border border-gray-200 dark:border-gray-600">
                        <p class="text-sm text-gray-600 dark:text-gray-200 mb-3">
                            Sudah paham sistem? Anda bisa melewati setup dan mulai langsung.
                        </p>
                        <form method="POST" action="{{ route('dashboard.onboarding.skip') }}">
                            @csrf
                            <button type="submit" 
                                    onclick="return confirm('Yakin ingin melewati setup? Anda masih bisa mengatur data melalui menu yang tersedia.')"
                                    class="text-sm text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-gray-100 underline"
                                Lewati Setup
                            </button>
                        </form>
                    </div>
                @endif

                {{-- Help --}}
                <div class="bg-blue-50 dark:bg-blue-500/10 rounded-xl p-4 border border-blue-100 dark:border-blue-500/30">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-circle-question text-blue-600 dark:text-blue-400 mt-0.5"></i>
                        <div>
                            <p class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-1">Butuh Bantuan?</p>
                            <p class="text-xs text-blue-700 dark:text-blue-400">
                                Hubungi tim support atau lihat dokumentasi untuk panduan lengkap.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
