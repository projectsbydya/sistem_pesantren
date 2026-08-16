<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $tenantLogo = \App\Services\TenantService::getTenant()?->logo;
        $faviconUrl = $tenantLogo
            ? (str_starts_with($tenantLogo, 'http') ? $tenantLogo : asset($tenantLogo))
            : asset('favicon.svg');
    @endphp
    <link rel="icon" type="image/svg+xml" href="{{ $faviconUrl }}">

    <title>@yield('title', 'Dashboard') - {{ \App\Services\TenantService::getTenant()?->name ?? config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 transition-colors duration-200">
    <div class="h-screen flex overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.tenant.sidebar')

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Navigation -->
            @include('layouts.tenant.topbar')

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 transition-colors duration-200">
                <div class="max-w-7xl mx-auto">
                    @if(session('success'))
                        <x-alert type="success" class="mb-4">{{ session('success') }}</x-alert>
                    @endif
                    @if(session('error'))
                        <x-alert type="error" class="mb-4">{{ session('error') }}</x-alert>
                    @endif
                    @if(session('warning'))
                        <x-alert type="warning" class="mb-4">{{ session('warning') }}</x-alert>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Bug Report Trigger — available on all authenticated tenant dashboard pages -->
    @auth
        <x-bug-report-trigger />
    @endauth

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>

    @stack('scripts')
</body>
</html>
