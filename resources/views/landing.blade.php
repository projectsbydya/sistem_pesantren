<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistem Manajemen Pesantren Modern - Kelola santri, akademik, keuangan, dan asrama dalam satu platform terpadu.">

    <title>{{ config('app.name', 'Kelola Pesantren') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=plus+jakarta+sans:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/js/app.js'])
    
    <!-- Critical CSS - MUST LOAD FIRST -->
    <style>
        /* Reset and base styles */
        *, *::before, *::after { box-sizing: border-box; }
        
        /* SVG sizing - prevent full screen icons */
        svg {
            max-width: 100% !important;
            height: auto !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }
        
        /* Force icon sizes based on parent classes */
        .w-4, .w-4 svg, svg.w-4 { width: 1rem !important; min-width: 1rem !important; }
        .w-5, .w-5 svg, svg.w-5 { width: 1.25rem !important; min-width: 1.25rem !important; }
        .w-6, .w-6 svg, svg.w-6 { width: 1.5rem !important; min-width: 1.5rem !important; }
        .w-7, .w-7 svg, svg.w-7 { width: 1.75rem !important; min-width: 1.75rem !important; }
        .w-8, .w-8 svg, svg.w-8 { width: 2rem !important; min-width: 2rem !important; }
        .w-10, .w-10 svg, svg.w-10 { width: 2.5rem !important; min-width: 2.5rem !important; }
        .w-12, .w-12 svg, svg.w-12 { width: 3rem !important; min-width: 3rem !important; }
        .w-14, .w-14 svg, svg.w-14 { width: 3.5rem !important; min-width: 3.5rem !important; }
        
        .h-4, .h-4 svg, svg.h-4 { height: 1rem !important; min-height: 1rem !important; }
        .h-5, .h-5 svg, svg.h-5 { height: 1.25rem !important; min-height: 1.25rem !important; }
        .h-6, .h-6 svg, svg.h-6 { height: 1.5rem !important; min-height: 1.5rem !important; }
        .h-7, .h-7 svg, svg.h-7 { height: 1.75rem !important; min-height: 1.75rem !important; }
        .h-8, .h-8 svg, svg.h-8 { height: 2rem !important; min-height: 2rem !important; }
        .h-10, .h-10 svg, svg.h-10 { height: 2.5rem !important; min-height: 2.5rem !important; }
        .h-12, .h-12 svg, svg.h-12 { height: 3rem !important; min-height: 3rem !important; }
        .h-14, .h-14 svg, svg.h-14 { height: 3.5rem !important; min-height: 3.5rem !important; }
        
        /* Component styles */
        .blob { position: absolute; filter: blur(80px); opacity: 0.4; }
        .glass { background: rgba(255,255,255,0.7); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
        .animated-bg { background: linear-gradient(135deg, #0d9488, #14b8a6, #06b6d4, #0ea5e9, #10b981); background-size: 400% 400%; }
        .gradient-text { background: linear-gradient(135deg, #14b8a6, #06b6d4, #0ea5e9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
@php
// Priority: 1. system_settings (DB), 2. config/landing.php, 3. Hardcoded default
$tagline = \App\Models\SystemSetting::get('landing.tagline', config('landing.tagline'));
$title = \App\Models\SystemSetting::get('landing.title', config('landing.title'));
$subtitle = \App\Models\SystemSetting::get('landing.subtitle', config('landing.subtitle'));
$ctaPrimary = \App\Models\SystemSetting::get('landing.cta_primary', config('landing.cta_primary'));
$ctaSecondary = \App\Models\SystemSetting::get('landing.cta_secondary', config('landing.cta_secondary'));
$ctaDescription = config('landing.cta_description');
$ctaWhatsappLabel = config('landing.cta_whatsapp_label');
$whatsapp = config('landing.whatsapp');
$email = config('landing.email');
$address = config('landing.address');

// Hero capability cards are fully driven from config so they can be customized
// without touching Blade. Optional SystemSetting override supports JSON-encoded arrays.
$heroDashboardCards = json_decode(
    \App\Models\SystemSetting::get('landing.hero_dashboard_cards', json_encode(config('landing.hero_dashboard_cards'))),
    true
) ?: config('landing.hero_dashboard_cards', []);
$heroFloatingCards = json_decode(
    \App\Models\SystemSetting::get('landing.hero_floating_cards', json_encode(config('landing.hero_floating_cards'))),
    true
) ?: config('landing.hero_floating_cards', []);
$heroBottomStats = json_decode(
    \App\Models\SystemSetting::get('landing.hero_bottom_stats', json_encode(config('landing.hero_bottom_stats'))),
    true
) ?: config('landing.hero_bottom_stats', []);

$social = config('landing.social', []);
@endphp

<body class="landing-page font-sans antialiased text-gray-900 bg-slate-50">
    <!-- Navigation -->
    <nav class="fixed w-full z-50 glass border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="/" class="flex items-center gap-3 group">
                        <div class="w-10 h-10 rounded-xl animated-bg flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <span class="text-xl font-bold gradient-text">
                            {{ config('app.name', 'Kelola Pesantren') }}
                        </span>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="#fitur" class="nav-link px-4 py-2 text-gray-600 hover:text-teal-600 font-medium transition-all duration-300 hover:bg-teal-50 rounded-lg">Fitur</a>
                    <a href="#cara-bergabung" class="nav-link px-4 py-2 text-gray-600 hover:text-teal-600 font-medium transition-all duration-300 hover:bg-teal-50 rounded-lg">Cara Bergabung</a>
                    <a href="#kontak" class="nav-link px-4 py-2 text-gray-600 hover:text-teal-600 font-medium transition-all duration-300 hover:bg-teal-50 rounded-lg">Kontak</a>
                    <div class="ml-4 pl-4 border-l border-gray-200">
                        <a href="{{ route('login') }}" class="btn-modern inline-flex items-center px-6 py-2.5 animated-bg text-white font-semibold rounded-xl shadow-lg shadow-teal-500/30 hover:shadow-teal-500/50 hover:scale-105 transition-all duration-300">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            Masuk
                        </a>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="p-2 rounded-lg text-gray-600 hover:text-teal-600 hover:bg-teal-50 transition-all">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden glass border-t border-white/20">
            <div class="px-4 py-4 space-y-2">
                <a href="#fitur" class="block px-4 py-3 rounded-xl text-base font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50/50 transition-all">Fitur</a>
                <a href="#cara-bergabung" class="block px-4 py-3 rounded-xl text-base font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50/50 transition-all">Cara Bergabung</a>
                <a href="#kontak" class="block px-4 py-3 rounded-xl text-base font-medium text-gray-700 hover:text-teal-600 hover:bg-teal-50/50 transition-all">Kontak</a>
                <a href="{{ route('login') }}" class="block px-4 py-3 rounded-xl text-base font-medium text-teal-600 bg-teal-50 hover:bg-teal-100 transition-all">Masuk</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden pt-20">
        <!-- Animated Background Blobs -->
        <div class="blob bg-teal-400 w-96 h-96 rounded-full top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
        <div class="blob bg-cyan-400 w-80 h-80 rounded-full bottom-0 right-0 translate-x-1/3 translate-y-1/3" style="animation-delay: -5s;"></div>
        <div class="blob bg-emerald-400 w-64 h-64 rounded-full top-1/2 right-0 -translate-y-1/2 translate-x-1/2" style="animation-delay: -10s;"></div>
        
        <!-- Grid Pattern -->
        <div class="absolute inset-0 grid-pattern"></div>
        
        <!-- Gradient -->
        <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-cyan-50"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Text -->
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-gradient-to-r from-teal-50 to-cyan-50 border border-teal-100 text-teal-700 text-sm font-semibold mb-6 reveal">
                        <span class="flex h-2 w-2 rounded-full bg-teal-500 animate-pulse"></span>
                        {{ $tagline }}
                    </div>
                    
                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-extrabold text-gray-900 leading-[1.1] mb-6 reveal" style="transition-delay: 0.1s;">
                        {{ explode(' ', $title)[0] ?? 'Kelola' }} {{ explode(' ', $title)[1] ?? 'Pesantren' }}
                        <span class="gradient-text">{{ implode(' ', array_slice(explode(' ', $title), 2)) ?: 'Lebih Mudah' }}</span>
                    </h1>
                    
                    <p class="text-xl text-gray-600 mb-8 leading-relaxed max-w-xl mx-auto lg:mx-0 reveal" style="transition-delay: 0.2s;">
                        {{ $subtitle }}
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start items-center reveal" style="transition-delay: 0.3s;">
                        <a href="{{ route('login') }}" class="btn-modern group inline-flex items-center px-8 py-4 animated-bg text-white font-bold rounded-2xl shadow-xl shadow-teal-500/30 hover:shadow-teal-500/50 hover:scale-105 transition-all duration-300">
                            <span>{{ $ctaPrimary }}</span>
                            <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                        <a href="#kontak" class="group inline-flex items-center px-8 py-4 bg-white text-gray-700 font-bold rounded-2xl shadow-lg shadow-gray-200/50 hover:shadow-xl hover:text-teal-600 transition-all duration-300 border border-gray-100">
                            <svg class="w-5 h-5 mr-2 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            {{ $ctaSecondary }}
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mt-12 reveal" style="transition-delay: 0.4s;">
                        @foreach($heroBottomStats as $item)
                            <div class="text-center p-4 rounded-2xl hover:bg-white/60 transition-colors duration-300">
                                <div class="flex justify-center mb-3">
                                    {!! $item['icon'] !!}
                                </div>
                                <div class="text-xl font-bold gradient-text">{{ $item['title'] }}</div>
                                @if(!empty($item['subtitle']))
                                    <div class="text-sm text-gray-500 font-medium">{{ $item['subtitle'] }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                
                <!-- Visual -->
                <div class="relative hidden lg:block">
                    <!-- Decorative gradient blob to fill bottom-right whitespace -->
                    <div class="absolute bottom-0 right-0 w-80 h-80 pointer-events-none z-0">
                        <svg viewBox="0 0 200 200" class="w-full h-full opacity-80">
                            <defs>
                                <radialGradient id="heroBlobTeal" cx="50%" cy="50%" r="50%" fx="40%" fy="40%">
                                    <stop offset="0%" stop-color="#14b8a6" stop-opacity="0.7" />
                                    <stop offset="70%" stop-color="#06b6d4" stop-opacity="0.3" />
                                    <stop offset="100%" stop-color="#06b6d4" stop-opacity="0.05" />
                                </radialGradient>
                            </defs>
                            <circle cx="100" cy="100" r="90" fill="url(#heroBlobTeal)" />
                            <circle cx="150" cy="150" r="55" fill="#0ea5e9" fill-opacity="0.25" />
                            <circle cx="55" cy="160" r="35" fill="#14b8a6" fill-opacity="0.18" />
                        </svg>
                    </div>

                    <div class="relative z-10 float">
                        <div class="relative bg-white rounded-3xl shadow-2xl shadow-teal-500/20 p-6 border border-gray-100">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="flex gap-2">
                                    <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                    <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
                                </div>
                                <div class="flex-1 h-8 bg-gray-100 rounded-lg"></div>
                            </div>
                            <div class="grid grid-cols-3 gap-3 mb-4">
                                @php
                                    $dashboardGradients = ['from-teal-50 to-cyan-50', 'from-blue-50 to-indigo-50', 'from-amber-50 to-orange-50'];
                                    $dashboardIconGradients = ['icon-gradient-teal', 'icon-gradient-blue', 'icon-gradient-amber'];
                                @endphp
                                @foreach($heroDashboardCards as $card)
                                    @php
                                        $bgGradient = $dashboardGradients[$loop->index % count($dashboardGradients)] ?? 'from-gray-50 to-gray-100';
                                        $iconGradient = $dashboardIconGradients[$loop->index % count($dashboardIconGradients)] ?? 'bg-gray-400';
                                    @endphp
                                    <div class="bg-gradient-to-br {{ $bgGradient }} p-4 rounded-2xl">
                                        <div class="w-8 h-8 {{ $iconGradient }} rounded-xl flex items-center justify-center mb-2">
                                            {!! $card['icon'] !!}
                                        </div>
                                        <div class="text-sm font-bold text-gray-800 leading-tight">{{ $card['title'] }}</div>
                                        @if(!empty($card['subtitle']))
                                            <div class="text-[10px] text-gray-500 leading-tight">{{ $card['subtitle'] }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <!-- Floating Elements -->
                        @php
                            $floatingPositions = [
                                ['position' => '-top-4 -right-4', 'delay' => '-2s', 'iconGradient' => 'icon-gradient-emerald', 'subtitleColor' => 'text-emerald-600'],
                                ['position' => '-bottom-4 -left-4', 'delay' => '-4s', 'iconGradient' => 'icon-gradient-amber', 'subtitleColor' => 'text-amber-600'],
                            ];
                        @endphp
                        @foreach($heroFloatingCards as $card)
                            @php
                                $floating = $floatingPositions[$loop->index] ?? ['position' => '-top-4 -right-4', 'delay' => '-2s', 'iconGradient' => 'icon-gradient-emerald', 'subtitleColor' => 'text-emerald-600'];
                            @endphp
                            <div class="absolute {{ $floating['position'] }} bg-white rounded-2xl shadow-xl p-4 float" style="animation-delay: {{ $floating['delay'] }};">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 {{ $floating['iconGradient'] }} rounded-xl flex items-center justify-center">
                                        {!! $card['icon'] !!}
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-800">{{ $card['title'] }}</div>
                                        @if(!empty($card['subtitle']))
                                            <div class="text-xs {{ $floating['subtitleColor'] }}">{{ $card['subtitle'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-24 bg-white relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-slate-50/50 to-transparent"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-teal-50 text-teal-700 text-sm font-semibold mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Fitur Lengkap
                </div>
                <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-4">
                    Semua yang Anda <span class="gradient-text">Butuhkan</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Platform terintegrasi dengan fitur lengkap untuk manajemen pesantren modern
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1: Manajemen Santri -->
                <div class="feature-card group p-8 rounded-3xl bg-white border border-gray-100 shadow-lg shadow-gray-100/50">
                    <div class="w-14 h-14 icon-gradient-teal rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-teal-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Manajemen Santri</h3>
                    <p class="text-gray-600 leading-relaxed">Kelola data santri lengkap dengan informasi pribadi, orang tua, dan riwayat akademik terintegrasi.</p>
                </div>

                <!-- Feature 2: Jadwal & Akademik -->
                <div class="feature-card group p-8 rounded-3xl bg-white border border-gray-100 shadow-lg shadow-gray-100/50">
                    <div class="w-14 h-14 icon-gradient-blue rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-blue-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Jadwal & Akademik</h3>
                    <p class="text-gray-600 leading-relaxed">Atur jadwal pelajaran, mata pelajaran, kelas, dan monitoring absensi real-time.</p>
                </div>

                <!-- Feature 3: Keuangan & SPP -->
                <div class="feature-card group p-8 rounded-3xl bg-white border border-gray-100 shadow-lg shadow-gray-100/50">
                    <div class="w-14 h-14 icon-gradient-amber rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-amber-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Keuangan & SPP</h3>
                    <p class="text-gray-600 leading-relaxed">Kelola pembayaran SPP, tabungan santri, dan laporan keuangan otomatis.</p>
                </div>

                <!-- Feature 4: Manajemen Asrama -->
                <div class="feature-card group p-8 rounded-3xl bg-white border border-gray-100 shadow-lg shadow-gray-100/50">
                    <div class="w-14 h-14 icon-gradient-purple rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-purple-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Manajemen Asrama</h3>
                    <p class="text-gray-600 leading-relaxed">Atur kamar, blok asrama, dan pembagian kamar santri dengan mudah.</p>
                </div>

                <!-- Feature 5: Raport & Nilai -->
                <div class="feature-card group p-8 rounded-3xl bg-white border border-gray-100 shadow-lg shadow-gray-100/50">
                    <div class="w-14 h-14 icon-gradient-rose rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-rose-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Raport & Nilai</h3>
                    <p class="text-gray-600 leading-relaxed">Cetak raport digital, input nilai, dan tracking perkembangan belajar santri.</p>
                </div>

                <!-- Feature 6: Hafalan & Tahfidz -->
                <div class="feature-card group p-8 rounded-3xl bg-white border border-gray-100 shadow-lg shadow-gray-100/50">
                    <div class="w-14 h-14 icon-gradient-emerald rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-emerald-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Hafalan & Tahfidz</h3>
                    <p class="text-gray-600 leading-relaxed">Monitoring hafalan Quran dan kitab dengan target dan evaluasi berkala.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How to Join Section -->
    <section id="cara-bergabung" class="py-24 bg-slate-50 relative overflow-hidden">
        <div class="blob bg-teal-200 w-64 h-64 rounded-full top-0 right-0 translate-x-1/2 -translate-y-1/2 opacity-30"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-teal-50 text-teal-700 text-sm font-semibold mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Mudah & Cepat
                </div>
                <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-4">
                    Mulai dalam <span class="gradient-text">3 Langkah</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Transformasi digital pesantren Anda dimulai dengan mudah
                </p>
            </div>

            <div class="relative grid grid-cols-1 md:grid-cols-3 gap-8 step-connector">
                <!-- Step 1 -->
                <div class="relative text-center reveal" style="transition-delay: 0.1s;">
                    <div class="step-number w-20 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 text-white text-3xl font-bold relative z-10">
                        1
                    </div>
                    <div class="bg-white p-8 rounded-3xl shadow-lg shadow-gray-100/50 border border-gray-100 hover-lift">
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Hubungi Kami</h3>
                        <p class="text-gray-600 leading-relaxed">Konsultasi gratis via WhatsApp atau email untuk informasi lengkap.</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="relative text-center reveal" style="transition-delay: 0.2s;">
                    <div class="step-number w-20 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 text-white text-3xl font-bold relative z-10">
                        2
                    </div>
                    <div class="bg-white p-8 rounded-3xl shadow-lg shadow-gray-100/50 border border-gray-100 hover-lift">
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Setup Tenant</h3>
                        <p class="text-gray-600 leading-relaxed">Tim kami siapkan subdomain khusus & konfigurasi sistem untuk Anda.</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="relative text-center reveal" style="transition-delay: 0.3s;">
                    <div class="step-number w-20 h-16 rounded-2xl flex items-center justify-center mx-auto mb-6 text-white text-3xl font-bold relative z-10">
                        3
                    </div>
                    <div class="bg-white p-8 rounded-3xl shadow-lg shadow-gray-100/50 border border-gray-100 hover-lift">
                        <h3 class="text-xl font-bold text-gray-900 mb-3">Siap Digunakan</h3>
                        <p class="text-gray-600 leading-relaxed">Dapatkan akses login dan mulai kelola pesantren secara digital.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="kontak" class="py-24 bg-white relative overflow-hidden">
        <div class="blob bg-teal-100 w-96 h-96 rounded-full bottom-0 left-0 -translate-x-1/2 translate-y-1/2 opacity-40"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <!-- Contact Info -->
                <div class="reveal">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-teal-50 text-teal-700 text-sm font-semibold mb-4">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        Hubungi Kami
                    </div>
                    <h2 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6">
                        Siap <span class="gradient-text">Bergabung?</span>
                    </h2>
                    <p class="text-xl text-gray-600 mb-10 leading-relaxed">
                        Konsultasi gratis untuk pesantren Anda. Tim kami siap membantu transformasi digital.
                    </p>

                    <div class="space-y-6">
                        <!-- WhatsApp -->
                        <div class="flex items-start group">
                            <div class="w-14 h-14 bg-gradient-to-br from-green-400 to-green-500 rounded-2xl flex items-center justify-center flex-shrink-0 mr-5 shadow-lg shadow-green-500/30 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-900 mb-1">WhatsApp</h4>
                                <p class="text-gray-500 mb-2">Respon cepat dalam hitungan menit</p>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" target="_blank" class="text-lg font-semibold text-teal-600 hover:text-teal-700 transition-colors">
                                    {{ $whatsapp }}
                                </a>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="flex items-start group">
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-500 rounded-2xl flex items-center justify-center flex-shrink-0 mr-5 shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-900 mb-1">Email</h4>
                                <p class="text-gray-500 mb-2">Informasi detail & penawaran</p>
                                <a href="mailto:{{ $email }}" class="text-lg font-semibold text-teal-600 hover:text-teal-700 transition-colors">
                                    {{ $email }}
                                </a>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="flex items-start group">
                            <div class="w-14 h-14 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center flex-shrink-0 mr-5 shadow-lg shadow-amber-500/30 group-hover:scale-110 transition-transform">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-900 mb-1">Lokasi</h4>
                                <p class="text-gray-500 mb-2">Melayani seluruh Indonesia</p>
                                <span class="text-lg font-semibold text-gray-700">{{ $address }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA Card -->
                <div class="reveal" style="transition-delay: 0.2s;">
                    <div class="cta-gradient rounded-3xl p-10 text-white shadow-2xl shadow-teal-500/30 relative overflow-hidden">
                        <!-- Decorative circles -->
                        <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                        
                        <div class="relative">
                            <h3 class="text-3xl font-bold mb-4">{{ $ctaPrimary }}</h3>
                            <p class="text-teal-100 text-lg mb-8 leading-relaxed">
                                {{ $ctaDescription }}
                            </p>
                            
                            <div class="space-y-4">
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" target="_blank" class="btn-modern group flex items-center justify-center w-full px-8 py-4 bg-white text-teal-700 rounded-2xl font-bold hover:shadow-lg transition-all duration-300">
                                    <svg class="w-6 h-6 mr-3" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                                    </svg>
                                    {{ $ctaWhatsappLabel }}
                                </a>
                                <a href="{{ route('login') }}" class="flex items-center justify-center w-full px-8 py-4 bg-teal-700/50 text-white rounded-2xl font-bold hover:bg-teal-700/70 transition-all duration-300 border border-teal-500/50">
                                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                    </svg>
                                    Login ke Akun
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-gradient text-gray-400 py-16 relative overflow-hidden">
        <!-- Decorative elements -->
        <div class="absolute top-0 left-1/4 w-64 h-64 bg-teal-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-cyan-500/5 rounded-full blur-3xl"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <!-- Brand -->
                <div class="md:col-span-2">
                    <a href="/" class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl animated-bg flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">{{ config('app.name', 'Kelola Pesantren') }}</span>
                    </a>
                    <p class="text-gray-400 leading-relaxed mb-6 max-w-sm">
                        Sistem manajemen pesantren modern untuk mengelola santri, akademik, keuangan, dan asrama dalam satu platform terpadu.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center hover:bg-teal-500 hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center hover:bg-teal-500 hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-white/5 flex items-center justify-center hover:bg-teal-500 hover:text-white transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-white font-bold mb-6">Tautan Cepat</h4>
                    <ul class="space-y-3">
                        <li><a href="#fitur" class="text-gray-400 hover:text-teal-400 transition-colors">Fitur</a></li>
                        <li><a href="#cara-bergabung" class="text-gray-400 hover:text-teal-400 transition-colors">Cara Bergabung</a></li>
                        <li><a href="#kontak" class="text-gray-400 hover:text-teal-400 transition-colors">Kontak</a></li>
                        <li><a href="{{ route('login') }}" class="text-gray-400 hover:text-teal-400 transition-colors">Login</a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h4 class="text-white font-bold mb-6">Hubungi Kami</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span>{{ $whatsapp }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>{{ $email }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-500">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Kelola Pesantren') }}. All rights reserved.
                </p>
                <div class="flex gap-6 text-sm">
                    <a href="#" class="text-gray-500 hover:text-teal-400 transition-colors">Kebijakan Privasi</a>
                    <a href="#" class="text-gray-500 hover:text-teal-400 transition-colors">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Mobile Menu Toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Close mobile menu when clicking a link
        document.querySelectorAll('#mobile-menu a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('mobile-menu').classList.add('hidden');
            });
        });

        // Scroll Reveal Animation
        const revealElements = document.querySelectorAll('.reveal');
        
        const revealOnScroll = () => {
            revealElements.forEach(element => {
                const windowHeight = window.innerHeight;
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < windowHeight - elementVisible) {
                    element.classList.add('active');
                }
            });
        };

        // Initial check
        revealOnScroll();
        
        // Check on scroll
        window.addEventListener('scroll', revealOnScroll);

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar background on scroll
        const nav = document.querySelector('nav');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                nav.classList.add('shadow-lg');
            } else {
                nav.classList.remove('shadow-lg');
            }
        });
    </script>
</body>
</html>
