@php
    $user = Auth::user();
    $currentRoute = request()->route()->getName() ?? '';
    $tenant = App\Services\TenantService::getTenant();
    $nav = App\Services\NavigationGateService::forUser($user);
    
    $safeRoute = function(string $name) {
        try { return tenant_route($name); } catch (\Throwable $e) { return '#'; }
    };
    
    $isRouteActive = function(string $routePattern) use ($currentRoute) {
        return str_starts_with($currentRoute, $routePattern);
    };
@endphp

<aside class="w-64 bg-slate-900 text-black flex flex-col">
    <!-- Logo -->
    <div class="h-16 flex items-center px-6 border-b border-slate-800">
        <a href="{{ $nav->canAccessSuperAdminPanel() ? route('dashboard.super-admin.index') : route('dashboard.index') }}" class="flex items-center gap-2">
            <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <span class="font-semibold text-lg">Pesantren</span>
        </a>
    </div>

    <!-- Tenant Info -->
    <div class="px-4 py-3 border-b border-slate-800">
        @if($nav->canAccessSuperAdminPanel())
            <div class="flex items-center gap-2 text-amber-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                <span class="text-xs font-medium uppercase tracking-wide">Super Admin</span>
            </div>
            <p class="mt-1 text-sm text-slate-400">Manajemen Tenant</p>
        @else
            <p class="text-sm font-medium text-slate-200">{{ $tenant?->name ?? 'No Tenant' }}</p>
            <p class="text-xs text-slate-400 mt-1">{{ $user->getRoleLabel() }}</p>
        @endif
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">

        @if($nav->canAccessSuperAdminPanel())
            <!-- Super Admin Navigation -->
            <div class="pt-2 pb-2">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Manajemen Tenant</p>
            </div>

            <a href="{{ route('dashboard.super-admin.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.super-admin.index') ? 'bg-amber-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Dashboard Super Admin
            </a>

            @if($nav->canViewTenants())
            <a href="{{ route('dashboard.super-admin.tenants.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.super-admin.tenants.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                Daftar Tenant
            </a>
            @endif

            @if($nav->canCreateTenant())
            <a href="{{ route('dashboard.super-admin.tenants.create') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.super-admin.tenants.create') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Buat Tenant Baru
            </a>
            @endif

            @if($nav->canViewSaasBillingSection())
            <div class="pt-4 pb-2">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">SaaS Billing</p>
            </div>

            @if($nav->canViewSubscriptions())
            <a href="{{ route('dashboard.super-admin.subscriptions.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.super-admin.subscriptions.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Subscriptions
            </a>
            @endif

            @if($nav->canViewPlans())
            <a href="{{ route('dashboard.super-admin.plans.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.super-admin.plans.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Plans
            </a>
            @endif

            @if($nav->canViewInvoices())
            <a href="{{ route('dashboard.super-admin.invoices.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.super-admin.invoices.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Invoices
            </a>
            @endif

            @if($nav->canViewRevenue())
            <a href="{{ route('dashboard.super-admin.revenue.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.super-admin.revenue.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Revenue
            </a>
            @endif
            @endif

            @if($nav->canViewUsage())
            <div class="pt-4 pb-2">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Monitoring</p>
            </div>

            <a href="{{ route('dashboard.super-admin.usage.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.super-admin.usage.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Usage Monitoring
            </a>
            @endif

            @if($nav->canViewPrograms())
            <div class="pt-4 pb-2">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Master Data</p>
            </div>

            <a href="{{ route('dashboard.super-admin.programs.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.super-admin.programs.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                Programs
            </a>
            @endif

        @else
            <!-- Dashboard -->
            <a href="{{ route('dashboard.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.index') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Dashboard
            </a>

            <!-- Data Management -->
            @if($nav->canViewSantri() || $nav->canViewParents())
                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Data Pesantren</p>
                </div>

                @if($nav->canViewSantri())
                    <a href="{{ route('dashboard.santri.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.santri.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Data Santri
                    </a>
                @endif

                @if($nav->canViewParents())
                    <a href="{{ route('dashboard.parent.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.parent.*') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Data Orang Tua
                    </a>
                @endif
            @endif

            <!-- AKADEMIK -->
            @if($nav->canViewAcademicSection())
                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">AKADEMIK</p>
                </div>

                <!-- DINIYAH -->
                <div class="mb-2">
                    <div class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        <i class="fas fa-book-open text-xs"></i>
                        DINIYAH
                    </div>
                    <ul class="mt-1 space-y-0.5">
                        <li>
                            <a href="{{ route('dashboard.akademik.diniyah.absensi.index', 'diniyah') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.akademik.diniyah.absensi') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-clipboard-list w-4 text-center text-xs"></i>
                                Absensi Santri
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.akademik.diniyah.subjects.index', 'diniyah') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.akademik.diniyah.subjects') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-book w-4 text-center text-xs"></i>
                                Mata Pelajaran
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.akademik.diniyah.kelas.index', 'diniyah') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.akademik.diniyah.kelas') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-chalkboard w-4 text-center text-xs"></i>
                                Kelas
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.akademik.diniyah.jadwal.index', 'diniyah') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.akademik.diniyah.jadwal') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-calendar-alt w-4 text-center text-xs"></i>
                                Jadwal
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.akademik.diniyah.nilai.index', 'diniyah') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.akademik.diniyah.nilai') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-chart-line w-4 text-center text-xs"></i>
                                Nilai
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.akademik.diniyah.elearning.index', 'diniyah') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.akademik.diniyah.elearning') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-laptop w-4 text-center text-xs"></i>
                                E-Learning
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- PESANTREN -->
                <div class="mb-2">
                    <div class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        <i class="fas fa-mosque text-xs"></i>
                        PESANTREN
                    </div>
                    <ul class="mt-1 space-y-0.5">
                        <li>
                            <a href="{{ route('dashboard.akademik.pesantren.absensi.index', 'pesantren') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.akademik.pesantren.absensi') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-clipboard-list w-4 text-center text-xs"></i>
                                Absensi Santri
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.akademik.pesantren.hafalan-quran.index', 'pesantren') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.akademik.pesantren.hafalan-quran') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-book-quran w-4 text-center text-xs"></i>
                                Hafalan Qur'an
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.akademik.pesantren.hafalan-kitab.index', 'pesantren') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.akademik.pesantren.hafalan-kitab') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-book w-4 text-center text-xs"></i>
                                Hafalan Kitab
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.akademik.pesantren.kamar.index', 'pesantren') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.akademik.pesantren.kamar') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-bed w-4 text-center text-xs"></i>
                                Kamar
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.akademik.pesantren.jadwal.index', 'pesantren') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.akademik.pesantren.jadwal') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-calendar-alt w-4 text-center text-xs"></i>
                                Jadwal
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.akademik.pesantren.nilai.index', 'pesantren') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.akademik.pesantren.nilai') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-chart-line w-4 text-center text-xs"></i>
                                Nilai
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.akademik.pesantren.elearning.index', 'pesantren') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.akademik.pesantren.elearning') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-laptop w-4 text-center text-xs"></i>
                                E-Learning
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- SDM -->
                <div class="mb-2">
                    <div class="flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                        <i class="fas fa-users text-xs"></i>
                        SDM
                    </div>
                    <ul class="mt-1 space-y-0.5">
                        <li>
                            <a href="{{ route('dashboard.sdm.absensi-ustadz.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.sdm.absensi-ustadz') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-clipboard-user w-4 text-center text-xs"></i>
                                Absensi Ustadz
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard.ustadz.index') }}"
                               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium ml-6
                                      {{ $isRouteActive('dashboard.ustadz') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fas fa-chalkboard-user w-4 text-center text-xs"></i>
                                Data Ustadz
                            </a>
                        </li>
                    </ul>
                </div>
            @endif

            <!-- Student/Parent Specific -->
            @if($user->santri)
                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Profil Saya</p>
                </div>

                <a href="{{ route('dashboard.santri.show', $user->santri?->id ?? 0) }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard.santri.show') ? 'bg-emerald-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profil Santri
                </a>
            @endif
        @endif
    </nav>

    <!-- Bottom Actions -->
    <div class="p-4 border-t border-slate-800 space-y-2">
        <!-- Logout -->
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-400 hover:bg-slate-800 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Keluar
            </button>
        </form>
    </div>
</aside>
