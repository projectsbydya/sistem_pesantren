@extends('layouts.tenant')

@section('title', 'Selamat Datang - Setup Pesantren')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-2xl">
        {{-- Progress Bar --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Progress Setup</span>
                <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">Langkah 1 dari 2</span>
            </div>
            <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: 25%"></div>
            </div>
        </div>

        {{-- Welcome Card --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-8 text-white">
                <div class="flex items-center justify-center w-16 h-16 rounded-full bg-white/20 mb-4">
                    <i class="fa-solid fa-mosque text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold text-center mb-2">Selamat Datang!</h1>
                <p class="text-emerald-100 text-center">
                    {{ $tenant->name }}
                </p>
            </div>

            {{-- Content --}}
            <div class="p-8">
                <div class="space-y-6">
                    {{-- Intro --}}
                    <div class="text-center">
                        <p class="text-gray-600 dark:text-gray-200 mb-4">
                            Selamat datang di Sistem Pesantren. Untuk mulai menggunakan aplikasi, 
                            Anda perlu menyelesaikan setup awal yang terdiri dari beberapa langkah sederhana.
                        </p>
                        <p class="text-gray-600 dark:text-gray-200">
                            Proses ini akan membantu Anda mengatur struktur pesantren sesuai dengan kebutuhan Anda.
                        </p>
                    </div>

                    {{-- Steps Preview --}}
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 border border-gray-100 dark:border-gray-600">
                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 mb-4">Apa yang akan Anda lakukan:</h3>
                        <div class="space-y-3">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-500/30 text-emerald-600 dark:text-emerald-300 flex items-center justify-center shrink-0 text-sm font-bold">1</div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-100">Pilih Program</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-300">Pilih program-program yang akan digunakan pesantren Anda</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-300 flex items-center justify-center shrink-0 text-sm font-bold">2</div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-gray-100">Setup Data Dasar</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-300">Buat kelas, tambah ustadz dan santri, atur jadwal</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Time Estimate --}}
                    <div class="flex items-center justify-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                        <i class="fa-regular fa-clock"></i>
                        <span>Estimasi waktu: 5-10 menit</span>
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-col sm:flex-row gap-3 pt-4">
                        <a href="{{ route('dashboard.onboarding.programs') }}" 
                           class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-3 px-6 rounded-xl text-center transition-colors">
                            Mulai Setup
                            <i class="fa-solid fa-arrow-right ml-2"></i>
                        </a>
                        
                        @if($progress->step_program_selected)
                            <a href="{{ route('dashboard.onboarding.setup-guide') }}" 
                               class="flex-1 bg-gray-100 dark:bg-gray-700 border-2 border-gray-300 dark:border-gray-600 hover:border-emerald-500 dark:hover:border-emerald-400 hover:bg-white dark:hover:bg-gray-600 text-gray-700 dark:text-gray-100 font-medium py-3 px-6 rounded-xl text-center transition-colors">
                                Lanjutkan Setup
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Help --}}
        <div class="text-center mt-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Butuh bantuan? 
                <a href="#" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-700 dark:hover:text-emerald-300">Lihat panduan lengkap</a>
            </p>
        </div>
    </div>
</div>
@endsection
