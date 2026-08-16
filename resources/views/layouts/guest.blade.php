<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kelola Pesantren') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=plus+jakarta+sans:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/js/app.js'])
        
        <!-- Critical CSS -->
        <style>
            *, *::before, *::after { box-sizing: border-box; }
            svg { max-width: 100% !important; height: auto !important; vertical-align: middle !important; }
            .blob { position: absolute; filter: blur(80px); opacity: 0.4; }
            .glass { background: rgba(255,255,255,0.8); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.3); }
            .animated-bg { background: linear-gradient(135deg, #0d9488, #14b8a6, #06b6d4, #0ea5e9, #10b981); background-size: 400% 400%; }
            .gradient-text { background: linear-gradient(135deg, #14b8a6, #06b6d4, #0ea5e9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
            .login-container { position: relative; z-index: 10; }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased landing-page">
        <!-- Animated Background -->
        <div class="fixed inset-0 overflow-hidden">
            <!-- Gradient Base -->
            <div class="absolute inset-0 bg-gradient-to-br from-slate-50 via-white to-cyan-50"></div>
            
            <!-- Animated Blobs -->
            <div class="blob bg-teal-400 w-96 h-96 rounded-full top-0 left-0 -translate-x-1/2 -translate-y-1/2"></div>
            <div class="blob bg-cyan-400 w-80 h-80 rounded-full bottom-0 right-0 translate-x-1/3 translate-y-1/3" style="animation-delay: -5s;"></div>
            <div class="blob bg-emerald-400 w-64 h-64 rounded-full top-1/2 left-1/4 -translate-y-1/2" style="animation-delay: -10s;"></div>
            
            <!-- Grid Pattern -->
            <div class="absolute inset-0 bg-[linear-gradient(rgba(20,184,166,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(20,184,166,0.03)_1px,transparent_1px)] bg-[size:60px_60px]"></div>
        </div>

        <!-- Login Container -->
        <div class="relative min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 login-container">
            <!-- Logo -->
            <div class="mb-8">
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-14 h-14 rounded-2xl animated-bg flex items-center justify-center shadow-lg shadow-teal-500/30 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold gradient-text">
                        {{ config('landing.app_name', config('app.name', 'Kelola Pesantren')) }}
                    </span>
                </a>
            </div>

            <!-- Glass Card -->
            <div class="w-full sm:max-w-md my-8 sm:my-12 px-6 sm:px-8 py-8 sm:py-10 glass shadow-2xl shadow-teal-500/10 overflow-hidden sm:rounded-3xl">
                {{ $slot }}
            </div>
            
            <!-- Back to Home -->
            <div class="mt-8 mb-6 text-center">
                <a href="/" class="text-sm text-gray-500 hover:text-teal-600 transition-colors flex items-center gap-2 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </body>
</html>
