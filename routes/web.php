<?php

use App\Helpers\TenantUrlHelper;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Dashboard\AbsensiSantriController;
use App\Http\Controllers\Dashboard\InvoiceController as TenantInvoiceController;
use App\Http\Controllers\Dashboard\AbsensiUstadzController;
use App\Http\Controllers\Dashboard\BugReportController;
use App\Http\Controllers\Dashboard\ClassSessionController;
use App\Http\Controllers\Dashboard\ElearningController;
use App\Http\Controllers\Dashboard\AssignmentController;
use App\Http\Controllers\Dashboard\Diniyah\DiniyahController;
use App\Http\Controllers\Dashboard\Modern\ModernController;
use App\Http\Controllers\Dashboard\HafalanKitabController;
use App\Http\Controllers\Dashboard\HafalanQuranController;
use App\Http\Controllers\Dashboard\KamarController;
use App\Http\Controllers\Dashboard\KelasController;
use App\Http\Controllers\Dashboard\MateriController;
use App\Http\Controllers\Dashboard\NilaiController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Dashboard\ParentController;
use App\Http\Controllers\Dashboard\ProgramAssessmentConfigController;
use App\Http\Controllers\Dashboard\SantriController;
use App\Http\Controllers\Dashboard\ScheduleController;
use App\Http\Controllers\Dashboard\SubjectController;
use App\Http\Controllers\Dashboard\TargetHafalanController;
use App\Http\Controllers\Dashboard\LivePengajianController;
use App\Http\Controllers\Dashboard\SppController;
use App\Http\Controllers\Dashboard\PaymentController;
use App\Http\Controllers\Dashboard\TabunganController;
use App\Http\Controllers\Dashboard\UstadzController;
use App\Http\Controllers\Dashboard\UstadzKelasController;
use App\Http\Controllers\Dashboard\OnboardingController;
use App\Http\Controllers\Dashboard\RaportController;
use App\Http\Controllers\Dashboard\KegiatanHarianController;
use App\Http\Controllers\Dashboard\MonitoringKarakterController;
use App\Http\Controllers\Dashboard\MutasiKamarController;
use App\Http\Controllers\Dashboard\PelanggaranController;
use App\Http\Controllers\Dashboard\PenempatanKamarController;
use App\Http\Controllers\Dashboard\PerizinanController;
use App\Http\Controllers\Dashboard\SanksiController;
use App\Http\Controllers\ProfileController;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// =========================================================================
// MAIN DOMAIN ROUTES — Super Admin & Central Landing Only
// =========================================================================
// Main domain = config('app.app_domain') e.g. pesantren.test / pesantren.com
// NO tenant operational routes here. Tenants MUST use subdomains.
// =========================================================================

Route::get('/', function () {
    // Unauthenticated users on main domain see landing page
    if (!auth()->check()) {
        return view('landing', [
            'whatsapp' => config('landing.whatsapp'),
            'email'    => config('landing.email'),
            'address'  => config('landing.address'),
        ]);
    }

    $user = auth()->user();

    // Super Admin → stay on main domain
    if ($user->is_super_admin) {
        return redirect()->route('dashboard.super-admin.index');
    }

    // Tenant user → redirect to their subdomain
    if ($user->tenant_id) {
        $tenant = TenantUrlHelper::getValidatedTenant($user->tenant_id);
        session(['tenant_id' => $tenant->id]);

        return redirect()->to(
            TenantUrlHelper::tenantUrlWithPort($tenant, '/dashboard')
        );
    }

    // Edge case: authenticated user without tenant or super admin flag
    abort(403, 'Akun Anda tidak terkait dengan tenant manapun.');
});

// =========================================================================
// MAIN DOMAIN — Super Admin Dashboard
// =========================================================================
// Profile — auth + tenant resolved so the shared sidebar has the same tenant context as Dashboard
Route::middleware(['auth', 'tenant.resolve'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Super Admin dashboard redirect (named route for backward compat)
Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->is_super_admin) {
        return redirect()->route('dashboard.super-admin.index');
    }

    // Non-super-admin on main domain → redirect to subdomain
    if ($user->tenant_id) {
        $tenant = TenantUrlHelper::getValidatedTenant($user->tenant_id);

        return redirect()->to(
            TenantUrlHelper::tenantUrlWithPort($tenant, '/dashboard')
        );
    }

    abort(403, 'Akun Anda tidak terkait dengan tenant manapun.');
})->middleware(['auth'])->name('dashboard.index.session');

// =========================================================================
// SUPER ADMIN ONLY — Tenant Management (NO operational data access)
// =========================================================================
Route::middleware(['auth', 'super_admin.gate'])
    ->prefix('dashboard/super-admin')
    ->name('dashboard.super-admin.')
    ->group(function () {
        // Resolve bug reports without tenant scope so Super Admin can view
        // cross-tenant submissions. The route middleware already ensures
        // only authenticated Super Admins reach this binding.
        Route::bind('bugReport', function (string $value): \App\Models\BugReport {
            return \App\Models\BugReport::withoutTenant()->findOrFail($value);
        });

        Route::get('/', function () {
            return view('dashboard.super-admin.index');
        })->name('index');

        // Bug Reports — Super Admin detail view (cross-tenant)
        Route::get('/bug-reports/{bugReport}', [\App\Http\Controllers\SuperAdmin\BugReportController::class, 'show'])
            ->name('bug-reports.show');

        Route::prefix('tenants')->name('tenants.')->group(function () {
            Route::get('/', function () {
                $tenants = Tenant::withCount('santri')->latest()->get();
                return view('dashboard.super-admin.tenants.index', compact('tenants'));
            })->name('index');

            Route::get('/create', function () {
                return view('dashboard.super-admin.tenants.create');
            })->name('create');

            Route::post('/', [\App\Http\Controllers\Admin\TenantManagementController::class, 'store'])
                ->name('store');

            Route::get('/{tenant}/credentials', [\App\Http\Controllers\Admin\TenantManagementController::class, 'showCredentials'])
                ->name('credentials');

            Route::post('/{tenant}/reset-password', [\App\Http\Controllers\Admin\TenantManagementController::class, 'resetAdminPassword'])
                ->name('reset-password');

            Route::get('/{tenant}/edit', function (Tenant $tenant) {
                return view('dashboard.super-admin.tenants.edit', compact('tenant'));
            })->name('edit');

            Route::put('/{tenant}', function (\Illuminate\Http\Request $request, Tenant $tenant) {
                $tenant->update($request->only(['name', 'is_active', 'is_trial', 'trial_ends_at']));
                return redirect()->route('dashboard.super-admin.tenants.index')
                    ->with('success', 'Tenant berhasil diupdate.');
            })->name('update');

            Route::delete('/{tenant}', function (Tenant $tenant) {
                if ($tenant->santri()->count() > 0) {
                    return back()->with('error', 'Tenant memiliki data santri, tidak bisa dihapus.');
                }
                $tenant->delete();
                return redirect()->route('dashboard.super-admin.tenants.index')
                    ->with('success', 'Tenant berhasil dihapus.');
            })->name('destroy');

            Route::post('/{tenant}/activate', function (Tenant $tenant) {
                $tenant->update(['is_active' => true]);
                return back()->with('success', 'Tenant diaktifkan.');
            })->name('activate');

            Route::post('/{tenant}/deactivate', function (Tenant $tenant) {
                $tenant->update(['is_active' => false]);
                return back()->with('success', 'Tenant dinonaktifkan.');
            })->name('deactivate');
        });

        // Subscription Management — Super Admin Only
        Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'index'])
                ->name('index');
            Route::get('/create', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'create'])
                ->name('create');
            Route::post('/', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'store'])
                ->name('store');
            Route::get('/{subscription}', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'show'])
                ->name('show');
            Route::get('/{subscription}/edit', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'edit'])
                ->name('edit');
            Route::put('/{subscription}', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'update'])
                ->name('update');
            Route::delete('/{subscription}', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'destroy'])
                ->name('destroy');

            // Subscription actions
            Route::post('/{subscription}/activate', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'activate'])
                ->name('activate');
            Route::post('/{subscription}/suspend', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'suspend'])
                ->name('suspend');
            Route::post('/{subscription}/cancel', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'cancel'])
                ->name('cancel');
            Route::post('/{subscription}/renew', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'renew'])
                ->name('renew');
            Route::post('/{subscription}/convert-trial', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'convertTrial'])
                ->name('convert-trial');
            Route::post('/{subscription}/extend-grace', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'extendGracePeriod'])
                ->name('extend-grace');

            // API route for tenant subscriptions
            Route::get('/tenant/{tenantId}', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'getForTenant'])
                ->name('for-tenant');

            // Manual tenant suspend / activate
            Route::post('/tenant/{tenant}/suspend', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'suspendTenant'])
                ->name('tenant.suspend');
            Route::post('/tenant/{tenant}/activate', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'activateTenant'])
                ->name('tenant.activate');
        });

        // Revenue Analytics Dashboard — Super Admin Only
        Route::prefix('revenue')->name('revenue.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SuperAdmin\RevenueDashboardController::class, 'index'])
                ->name('index');
            Route::get('/metrics', [\App\Http\Controllers\SuperAdmin\RevenueDashboardController::class, 'metrics'])
                ->name('metrics');
        });

        // Plan Management
        Route::prefix('plans')->name('plans.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'store'])->name('store');
            Route::get('/{plan}', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'show'])->name('show');
            Route::get('/{plan}/edit', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'edit'])->name('edit');
            Route::put('/{plan}', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'update'])->name('update');
            Route::delete('/{plan}', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'destroy'])->name('destroy');
            Route::post('/{plan}/toggle-active', [\App\Http\Controllers\SuperAdmin\PlanController::class, 'toggleActive'])->name('toggle-active');
        });

        // Invoice Management
        Route::prefix('invoices')->name('invoices.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SuperAdmin\InvoiceController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\SuperAdmin\InvoiceController::class, 'store'])->name('store');
            Route::get('/{invoice}', [\App\Http\Controllers\SuperAdmin\InvoiceController::class, 'show'])->name('show');
            Route::post('/{invoice}/cancel', [\App\Http\Controllers\SuperAdmin\InvoiceController::class, 'cancel'])->name('cancel');
            Route::post('/{invoice}/mark-paid', [\App\Http\Controllers\SuperAdmin\InvoiceController::class, 'markPaid'])->name('mark-paid');
        });

        // SaaS Payment Management
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SuperAdmin\SaasPaymentController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\SuperAdmin\SaasPaymentController::class, 'store'])->name('store');
            Route::get('/{payment}', [\App\Http\Controllers\SuperAdmin\SaasPaymentController::class, 'show'])->name('show');
            Route::post('/{payment}/confirm', [\App\Http\Controllers\SuperAdmin\SaasPaymentController::class, 'confirm'])->name('confirm');
            Route::post('/{payment}/reject', [\App\Http\Controllers\SuperAdmin\SaasPaymentController::class, 'reject'])->name('reject');
            Route::delete('/{payment}', [\App\Http\Controllers\SuperAdmin\SaasPaymentController::class, 'destroy'])->name('destroy');
        });

        // Usage Monitoring — Super Admin only
        Route::prefix('usage')->name('usage.')->group(function () {
            Route::get('/', [\App\Http\Controllers\SuperAdmin\UsageMonitoringController::class, 'index'])->name('index');
            Route::get('/report', [\App\Http\Controllers\SuperAdmin\UsageMonitoringController::class, 'report'])->name('report');
            Route::get('/approaching', [\App\Http\Controllers\SuperAdmin\UsageMonitoringController::class, 'approachingLimits'])->name('approaching');
            Route::get('/{tenant}', [\App\Http\Controllers\SuperAdmin\UsageMonitoringController::class, 'show'])->name('show');
            Route::post('/{tenant}/record', [\App\Http\Controllers\SuperAdmin\UsageMonitoringController::class, 'record'])->name('record');
        });

        // Program Management — Global Master Data (Super Admin only)
        Route::prefix('programs')->name('programs.')->group(function () {
            Route::get('/', function () {
                $programs = \App\Models\Program::withCount('kelas')->ordered()->get();
                return view('dashboard.super-admin.programs.index', compact('programs'));
            })->name('index');

            Route::get('/create', function () {
                return view('dashboard.super-admin.programs.create');
            })->name('create');

            Route::post('/', function (\Illuminate\Http\Request $request) {
                $validated = $request->validate([
                    'name'        => 'required|string|max:255|unique:programs',
                    'slug'        => 'required|string|max:255|unique:programs|regex:/^[a-z0-9-]+$/',
                    'description' => 'nullable|string|max:1000',
                    'is_active'   => 'nullable|boolean',
                ]);

                $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

                \App\Models\Program::create($validated);
                return redirect()->route('dashboard.super-admin.programs.index')
                    ->with('success', 'Program berhasil ditambahkan ke katalog.');
            })->name('store');

            Route::get('/{program}/edit', function (\App\Models\Program $program) {
                return view('dashboard.super-admin.programs.edit', compact('program'));
            })->name('edit');

            Route::put('/{program}', function (\Illuminate\Http\Request $request, \App\Models\Program $program) {
                $validated = $request->validate([
                    'name'        => 'required|string|max:255|unique:programs,name,' . $program->id,
                    'slug'        => 'required|string|max:255|unique:programs,slug,' . $program->id . '|regex:/^[a-z0-9-]+$/',
                    'description' => 'nullable|string|max:1000',
                    'is_active'   => 'nullable|boolean',
                ]);

                $validated['is_active'] = (bool) ($validated['is_active'] ?? false);

                $program->update($validated);
                return redirect()->route('dashboard.super-admin.programs.index')
                    ->with('success', 'Program berhasil diperbarui.');
            })->name('update');

            Route::delete('/{program}', function (\App\Models\Program $program) {
                // Block delete if program is referenced by any academic data.
                // Use isInUse() which checks kelas, subjects, and jadwal.
                if ($program->isInUse()) {
                    return back()->with('error', 'Program tidak dapat dihapus karena masih digunakan oleh data akademik. Nonaktifkan program tersebut sebagai gantinya.');
                }
                $program->delete();
                return redirect()->route('dashboard.super-admin.programs.index')
                    ->with('success', 'Program berhasil dihapus.');
            })->name('destroy');

            Route::patch('/{program}/toggle-active', function (\App\Models\Program $program) {
                // Block deactivating programs that are in active use
                if ($program->is_active && $program->isInUse()) {
                    return back()->with('error', 'Program tidak dapat dinonaktifkan karena masih memiliki kelas, mata pelajaran, atau jadwal aktif.');
                }
                $program->update(['is_active' => !$program->is_active]);
                $label = $program->is_active ? 'diaktifkan' : 'dinonaktifkan';
                return back()->with('success', "Program {$program->name} berhasil {$label}.");
            })->name('toggle-active');
        });
    });

// Tenant switch — tenant users only (e.g. multi-role session helpers)
// Super admin is explicitly blocked: they manage tenants but must NOT
// inject tenant context to access operational data.
Route::middleware(['auth'])->post('/switch-tenant', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Gate::authorize('switch-tenant');

    $request->validate(['tenant_id' => 'nullable|exists:tenants,id']);

    $tenantId = $request->tenant_id ? (int) $request->tenant_id : null;
    $user     = auth()->user();

    if ($tenantId !== null && (int) $user->tenant_id !== $tenantId) {
        abort(403, 'Anda tidak memiliki akses ke tenant ini.');
    }

    session(['tenant_id' => $tenantId]);
    return back();
})->name('switch-tenant');

// =========================================================================
// Notifications — available to any authenticated user (tenant + super admin)
// =========================================================================
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount'])
        ->name('notifications.unread-count');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
        ->name('notifications.mark-all-read');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
});

// =========================================================================
// SUBDOMAIN ROUTES — ALL Tenant Features
// =========================================================================
// Route::domain('{tenant}.' . config('app.app_domain'))
// {tenant} is captured as a route parameter → ResolveTenant picks it up.
// ALL operational routes MUST be here. Main domain has NONE.
// =========================================================================

// =========================================================================
// MAIN-DOMAIN SESSION-BASED ROUTES — same operational routes, no {tenant} param
// Covers: tests, direct URL access, users with active session
// Tenant is resolved via session/header/user-fallback by tenant.resolve
// =========================================================================
Route::middleware(['auth', 'password.change', 'tenant.resolve', 'owns.tenant', 'tenant.log', 'onboarding'])
    ->prefix('dashboard')
    ->group(function () {
        // =========================================================================
        // ONBOARDING & SETUP (Tenant Onboarding Flow)
        // =========================================================================
        Route::prefix('onboarding')->name('dashboard.onboarding.')->group(function () {
            Route::get('/welcome', [OnboardingController::class, 'welcome'])->name('welcome');
            Route::get('/programs', [OnboardingController::class, 'programs'])->name('programs');
            Route::post('/programs', [OnboardingController::class, 'storePrograms'])->name('programs.store');
            Route::get('/program-setup-queue', [OnboardingController::class, 'programSetupQueue'])->name('program-setup-queue');
            Route::get('/program-setup/{programId}/start', [OnboardingController::class, 'startProgramSetup'])->name('program-setup.start');
            Route::get('/setup-guide', [OnboardingController::class, 'setupGuide'])->name('setup-guide');
            Route::post('/skip', [OnboardingController::class, 'skip'])->name('skip');
            Route::post('/complete-step/{step}', [OnboardingController::class, 'completeStep'])->name('complete-step');
            Route::get('/progress', [OnboardingController::class, 'progressJson'])->name('progress');
            // Wizard
            Route::get('/wizard', [OnboardingController::class, 'wizard'])->name('wizard');
            Route::post('/wizard/kelas', [OnboardingController::class, 'storeKelas'])->name('wizard.store-kelas');
            Route::post('/wizard/mapel', [OnboardingController::class, 'storeMapel'])->name('wizard.store-mapel');
            Route::post('/wizard/ustadz', [OnboardingController::class, 'storeUstadz'])->name('wizard.store-ustadz');
            Route::post('/wizard/penugasan', [OnboardingController::class, 'storePenugasan'])->name('wizard.store-penugasan');
            Route::post('/wizard/jadwal', [OnboardingController::class, 'storeJadwal'])->name('wizard.store-jadwal');
            Route::post('/wizard/skip', [OnboardingController::class, 'skipStep'])->name('wizard.skip-step');
        });

        // Dashboard Index
        Route::get('/', function () {
            return view('dashboard.index');
        })->name('dashboard.index.session');

        // Bug Reports (tenant submission only; listing/detail are super-admin only)
        Route::post('/bug-reports', [BugReportController::class, 'store'])
            ->name('dashboard.bug-reports.store');

        // Santri
        Route::get('/santri', [SantriController::class, 'index'])
            ->name('dashboard.santri.index.session');

        // Santri credentials download MUST be before {id} wildcard routes
        Route::get('/santri/credentials/download', [SantriController::class, 'downloadCredentials'])
            ->name('dashboard.santri.credentials.download.session');

        Route::get('/santri/create', [SantriController::class, 'create'])
            ->name('dashboard.santri.create.session');
        Route::post('/santri', [SantriController::class, 'store'])
            ->name('dashboard.santri.store.session');
        Route::get('/santri/{id}/edit', [SantriController::class, 'edit'])
            ->name('dashboard.santri.edit.session')
            ->whereNumber('id');
        Route::put('/santri/{id}', [SantriController::class, 'update'])
            ->name('dashboard.santri.update.session')
            ->whereNumber('id');
        Route::delete('/santri/{id}', [SantriController::class, 'destroy'])
            ->name('dashboard.santri.destroy.session')
            ->whereNumber('id');

        Route::get('/santri/{id}', [SantriController::class, 'show'])
            ->name('dashboard.santri.show.session')
            ->where('id', '[0-9]+');

        // Parent routes - accessible by admin and parent
        Route::get('/parents', [ParentController::class, 'index'])
            ->name('dashboard.parent.index.session');

        // Parent credentials download MUST be before {id} wildcard routes
        Route::get('/parents/credentials/download', [ParentController::class, 'downloadCredentials'])
            ->name('dashboard.parent.credentials.download.session');

        Route::get('/parents/{id}', [ParentController::class, 'show'])
            ->name('dashboard.parent.show.session')
            ->whereNumber('id');

        Route::get('/parents/create', [ParentController::class, 'create'])
            ->name('dashboard.parent.create.session');
        Route::post('/parents', [ParentController::class, 'store'])
            ->name('dashboard.parent.store.session');
        Route::get('/parents/{id}/edit', [ParentController::class, 'edit'])
            ->name('dashboard.parent.edit.session')
            ->whereNumber('id');
        Route::put('/parents/{id}', [ParentController::class, 'update'])
            ->name('dashboard.parent.update.session')
            ->whereNumber('id');
        Route::delete('/parents/{id}', [ParentController::class, 'destroy'])
            ->name('dashboard.parent.destroy.session')
            ->whereNumber('id');

        // Admin routes (duplicated path for compat with existing tests)
        Route::prefix('admin')->group(function () {
            // User management routes
            Route::get('/users', [UserManagementController::class, 'index'])
                ->name('dashboard.admin.users.index');
            Route::get('/users/create', [UserManagementController::class, 'create'])
                ->name('dashboard.admin.users.create');
            Route::post('/users', [UserManagementController::class, 'store'])
                ->name('dashboard.admin.users.store');
            Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])
                ->name('dashboard.admin.users.edit')
                ->whereNumber('user');
            Route::put('/users/{user}', [UserManagementController::class, 'update'])
                ->name('dashboard.admin.users.update')
                ->whereNumber('user');
            Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])
                ->name('dashboard.admin.users.destroy')
                ->whereNumber('user');
            Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])
                ->name('dashboard.admin.users.reset-password')
                ->whereNumber('user');
            
            // Santri routes
            Route::get('/santri/create', [SantriController::class, 'create'])
                ->name('dashboard.admin.santri.create.session');
            Route::post('/santri', [SantriController::class, 'store'])
                ->name('dashboard.admin.santri.store.session');
            Route::get('/santri/{id}/edit', [SantriController::class, 'edit'])
                ->name('dashboard.admin.santri.edit.session')
                ->where('id', '[0-9]+');
            Route::put('/santri/{id}', [SantriController::class, 'update'])
                ->name('dashboard.admin.santri.update.session')
                ->whereNumber('id');
            Route::delete('/santri/{id}', [SantriController::class, 'destroy'])
                ->name('dashboard.admin.santri.destroy.session')
                ->whereNumber('id');
        });

        // Ustadz — authorization handled by UstadzPolicy in controller
        Route::get('/ustadz/credentials/download', [UstadzController::class, 'downloadCredentials'])
            ->name('dashboard.ustadz.credentials.download.session');
        Route::get('/ustadz', [UstadzController::class, 'index'])
            ->name('dashboard.ustadz.index.session');
        Route::get('/ustadz/create', [UstadzController::class, 'create'])
            ->name('dashboard.ustadz.create.session');
        Route::post('/ustadz', [UstadzController::class, 'store'])
            ->name('dashboard.ustadz.store.session');
        Route::get('/ustadz/{id}/edit', [UstadzController::class, 'edit'])
            ->name('dashboard.ustadz.edit.session')
            ->whereNumber('id');
        Route::put('/ustadz/{id}', [UstadzController::class, 'update'])
            ->name('dashboard.ustadz.update.session')
            ->whereNumber('id');
        Route::delete('/ustadz/{id}', [UstadzController::class, 'destroy'])
            ->name('dashboard.ustadz.destroy.session')
            ->whereNumber('id');
        Route::get('/ustadz/{id}', [UstadzController::class, 'show'])
            ->name('dashboard.ustadz.show.session')
            ->whereNumber('id');

        // ── Akademik ─────────────────────────────────────────────────────
        Route::prefix('akademik')->group(function () {

            // ── Kepesantrenan ───────────────────────────────────────────────
            Route::prefix('kepesantrenan')->group(function () {
                // Hafalan Quran
                Route::prefix('hafalan-quran')->group(function () {
                    Route::get('/', [HafalanQuranController::class, 'index'])->name('dashboard.kepesantrenan.hafalan-quran.index');
                    Route::post('/', [HafalanQuranController::class, 'store'])->name('dashboard.kepesantrenan.hafalan-quran.store');
                    Route::get('/santri/{id}', [HafalanQuranController::class, 'show'])->name('dashboard.kepesantrenan.hafalan-quran.show')->whereNumber('id');
                    Route::delete('/{id}', [HafalanQuranController::class, 'destroy'])->name('dashboard.kepesantrenan.hafalan-quran.destroy')->whereNumber('id');
                });
                // Hafalan Kitab
                Route::prefix('hafalan-kitab')->group(function () {
                    Route::get('/', [HafalanKitabController::class, 'index'])->name('dashboard.kepesantrenan.hafalan-kitab.index');
                    Route::post('/', [HafalanKitabController::class, 'store'])->name('dashboard.kepesantrenan.hafalan-kitab.store');
                    Route::get('/santri/{id}', [HafalanKitabController::class, 'show'])->name('dashboard.kepesantrenan.hafalan-kitab.show')->whereNumber('id');
                    Route::delete('/{id}', [HafalanKitabController::class, 'destroy'])->name('dashboard.kepesantrenan.hafalan-kitab.destroy')->whereNumber('id');
                });
                // Target Hafalan
                Route::prefix('target-hafalan')->group(function () {
                    Route::get('/', [TargetHafalanController::class, 'index'])->name('dashboard.kepesantrenan.target-hafalan.index');
                    Route::post('/', [TargetHafalanController::class, 'store'])->name('dashboard.kepesantrenan.target-hafalan.store');
                    Route::put('/{id}', [TargetHafalanController::class, 'update'])->name('dashboard.kepesantrenan.target-hafalan.update')->whereNumber('id');
                    Route::delete('/{id}', [TargetHafalanController::class, 'destroy'])->name('dashboard.kepesantrenan.target-hafalan.destroy')->whereNumber('id');
                });
            });

            // ── Modern Pack ─────────────────────────────────────────────────
            // Middleware validates tenant can access the program (prevents cross-tenant access)
            // Class-centric assignment features use AssignmentController.
            // type-based sub-categorisation uses ?type= query param (e.g. ?type=arabic).
            Route::middleware(['program.access'])->prefix('modern/{programSlug}')->group(function () {
                foreach (['vocabulary', 'muhadatsah', 'muhadhoroh'] as $feature) {
                    Route::prefix($feature)->group(function () use ($feature) {
                        Route::get('/',            [AssignmentController::class, 'index'])  ->defaults('pack', 'modern')->defaults('featureSlug', $feature)->name("dashboard.modern.{$feature}.index");
                        Route::post('/',           [AssignmentController::class, 'store'])  ->defaults('pack', 'modern')->defaults('featureSlug', $feature)->name("dashboard.modern.{$feature}.store");
                        Route::get('/santri/{id}', [AssignmentController::class, 'show'])   ->defaults('pack', 'modern')->defaults('featureSlug', $feature)->name("dashboard.modern.{$feature}.show")->whereNumber('id');
                        Route::get('/{id}/edit',   [AssignmentController::class, 'edit'])   ->defaults('pack', 'modern')->defaults('featureSlug', $feature)->name("dashboard.modern.{$feature}.edit")->whereNumber('id');
                        Route::put('/{id}',        [AssignmentController::class, 'update']) ->defaults('pack', 'modern')->defaults('featureSlug', $feature)->name("dashboard.modern.{$feature}.update")->whereNumber('id');
                        Route::delete('/{id}',     [AssignmentController::class, 'destroy'])->defaults('pack', 'modern')->defaults('featureSlug', $feature)->name("dashboard.modern.{$feature}.destroy")->whereNumber('id');
                        Route::patch('/{id}/status', [AssignmentController::class, 'updateMember'])->defaults('pack', 'modern')->defaults('featureSlug', $feature)->name("dashboard.modern.{$feature}.update-status")->whereNumber('id');
                    });
                }

                // Placement Test stays individual — handled by ModernController
                Route::prefix('placement-test')->group(function () {
                    Route::get('/',            [ModernController::class, 'index'])  ->defaults('featureSlug', 'placement-test')->name("dashboard.modern.placement-test.index");
                    Route::post('/',           [ModernController::class, 'store'])  ->defaults('featureSlug', 'placement-test')->name("dashboard.modern.placement-test.store");
                    Route::get('/santri/{id}', [ModernController::class, 'show'])   ->defaults('featureSlug', 'placement-test')->name("dashboard.modern.placement-test.show")->whereNumber('id');
                    Route::get('/{id}/edit',   [ModernController::class, 'edit'])   ->defaults('featureSlug', 'placement-test')->name("dashboard.modern.placement-test.edit")->whereNumber('id');
                    Route::put('/{id}',        [ModernController::class, 'update']) ->defaults('featureSlug', 'placement-test')->name("dashboard.modern.placement-test.update")->whereNumber('id');
                    Route::delete('/{id}',     [ModernController::class, 'destroy'])->defaults('featureSlug', 'placement-test')->name("dashboard.modern.placement-test.destroy")->whereNumber('id');

                    // Dedicated Placement Test Result routes
                    Route::post('/results',          [ModernController::class, 'storeResult'])  ->defaults('featureSlug', 'placement-test')->name('dashboard.modern.placement-test.results.store');
                    Route::put('/results/{id}',        [ModernController::class, 'updateResult']) ->defaults('featureSlug', 'placement-test')->name('dashboard.modern.placement-test.results.update')->whereNumber('id');
                    Route::delete('/results/{id}',     [ModernController::class, 'destroyResult'])->defaults('featureSlug', 'placement-test')->name('dashboard.modern.placement-test.results.destroy')->whereNumber('id');
                });
            });

            // ── Diniyah Pack ────────────────────────────────────────────────
            // Middleware validates tenant can access the program (prevents cross-tenant access)
            // Hafalan features are class-centric assignments; monitoring/nilai remain per-student.
            Route::middleware(['program.access'])->prefix('diniyah/{programSlug}')->group(function () {
                foreach (['hafalan-doa', 'hafalan-hadits', 'hafalan-surat'] as $feature) {
                    Route::prefix($feature)->group(function () use ($feature) {
                        Route::get('/', [AssignmentController::class, 'index'])
                            ->defaults('pack', 'diniyah')
                            ->defaults('featureSlug', $feature)
                            ->name("dashboard.diniyah.{$feature}.index");
                        Route::post('/', [AssignmentController::class, 'store'])
                            ->defaults('pack', 'diniyah')
                            ->defaults('featureSlug', $feature)
                            ->name("dashboard.diniyah.{$feature}.store");
                        Route::get('/santri/{id}', [AssignmentController::class, 'show'])
                            ->defaults('pack', 'diniyah')
                            ->defaults('featureSlug', $feature)
                            ->name("dashboard.diniyah.{$feature}.show")
                            ->whereNumber('id');
                        Route::get('/{id}/edit', [AssignmentController::class, 'edit'])
                            ->defaults('pack', 'diniyah')
                            ->defaults('featureSlug', $feature)
                            ->name("dashboard.diniyah.{$feature}.edit")
                            ->whereNumber('id');
                        Route::put('/{id}', [AssignmentController::class, 'update'])
                            ->defaults('pack', 'diniyah')
                            ->defaults('featureSlug', $feature)
                            ->name("dashboard.diniyah.{$feature}.update")
                            ->whereNumber('id');
                        Route::delete('/{id}', [AssignmentController::class, 'destroy'])
                            ->defaults('pack', 'diniyah')
                            ->defaults('featureSlug', $feature)
                            ->name("dashboard.diniyah.{$feature}.destroy")
                            ->whereNumber('id');
                        Route::patch('/{id}/status', [AssignmentController::class, 'updateMember'])
                            ->defaults('pack', 'diniyah')
                            ->defaults('featureSlug', $feature)
                            ->name("dashboard.diniyah.{$feature}.update-status")
                            ->whereNumber('id');
                    });
                }

                foreach (['monitoring-sholat', 'monitoring-adab', 'monitoring-akhlak', 'nilai-keagamaan', 'nilai-akhlak'] as $feature) {
                    Route::prefix($feature)->group(function () use ($feature) {
                        Route::get('/', [DiniyahController::class, 'index'])
                            ->defaults('featureSlug', $feature)
                            ->name("dashboard.diniyah.{$feature}.index");
                        Route::post('/', [DiniyahController::class, 'store'])
                            ->defaults('featureSlug', $feature)
                            ->name("dashboard.diniyah.{$feature}.store");
                        Route::get('/santri/{id}', [DiniyahController::class, 'show'])
                            ->defaults('featureSlug', $feature)
                            ->name("dashboard.diniyah.{$feature}.show")
                            ->whereNumber('id');
                        Route::delete('/{id}', [DiniyahController::class, 'destroy'])
                            ->defaults('featureSlug', $feature)
                            ->name("dashboard.diniyah.{$feature}.destroy")
                            ->whereNumber('id');

                        // Monitoring-specific routes for rekap and riwayat
                        if (str_starts_with($feature, 'monitoring-') || str_starts_with($feature, 'nilai-')) {
                            Route::get('/rekap', [DiniyahController::class, 'rekap'])
                                ->defaults('featureSlug', $feature)
                                ->name("dashboard.diniyah.{$feature}.rekap");
                            Route::get('/riwayat/{santriId}', [DiniyahController::class, 'riwayat'])
                                ->defaults('featureSlug', $feature)
                                ->name("dashboard.diniyah.{$feature}.riwayat")
                                ->whereNumber('santriId');
                        }
                    });
                }
            });

        });

        // ── SDM: Ustadz Attendance (Staff Domain) ───────────────────────────────
        // Handles staff attendance - independent from academic modules
        Route::prefix('sdm/absensi-ustadz')->group(function () {
            Route::get('/', [AbsensiUstadzController::class, 'index'])->name('dashboard.sdm.absensi-ustadz.index.session');
            Route::post('/store', [AbsensiUstadzController::class, 'store'])->name('dashboard.sdm.absensi-ustadz.store.session');
            Route::post('/store-bulk', [AbsensiUstadzController::class, 'storeBulk'])->name('dashboard.sdm.absensi-ustadz.store-bulk.session');
            Route::get('/rekap', [AbsensiUstadzController::class, 'rekap'])->name('dashboard.sdm.absensi-ustadz.rekap.session');
        });

        // ── Academic: Santri Attendance (Academic Domain) ───────────────────────
        // Handles student attendance - uses ustadz_kelas_id with program filtering
        Route::prefix('absensi')->group(function () {
            // Program-based Attendance Routes
            Route::prefix('santri/{programSlug}')->group(function () {
                Route::get('/', [AbsensiSantriController::class, 'index'])->name('dashboard.absensi.santri.index');
                Route::get('/input', [AbsensiSantriController::class, 'input'])->name('dashboard.absensi.santri.input');
                Route::post('/store', [AbsensiSantriController::class, 'store'])->name('dashboard.absensi.santri.store');
                Route::get('/rekap', [AbsensiSantriController::class, 'rekap'])->name('dashboard.absensi.santri.rekap');
            });
        });

        // SPP
        Route::prefix('spp')->group(function () {
            Route::get('/', [SppController::class, 'index'])->name('dashboard.spp.index');
            Route::get('/export', [SppController::class, 'export'])->name('dashboard.spp.export');
            Route::get('/create', [SppController::class, 'create'])->name('dashboard.spp.create');
            Route::post('/', [SppController::class, 'store'])->name('dashboard.spp.store');
            Route::get('/{bill}', [SppController::class, 'show'])->name('dashboard.spp.show');
            Route::get('/{bill}/edit', [SppController::class, 'edit'])->name('dashboard.spp.edit');
            Route::put('/{bill}', [SppController::class, 'update'])->name('dashboard.spp.update');
            Route::delete('/{bill}', [SppController::class, 'destroy'])->name('dashboard.spp.destroy');
        });

        // Tabungan
        Route::prefix('tabungan')->group(function () {
            Route::get('/', [TabunganController::class, 'index'])->name('dashboard.tabungan.index');
            Route::get('/export', [TabunganController::class, 'export'])->name('dashboard.tabungan.export');
            Route::get('/create', [TabunganController::class, 'create'])->name('dashboard.tabungan.create');
            Route::post('/', [TabunganController::class, 'store'])->name('dashboard.tabungan.store');
            Route::get('/santri/{santri}', [TabunganController::class, 'saldoSantri'])->name('dashboard.tabungan.santri');
            Route::get('/{tabungan}/edit', [TabunganController::class, 'edit'])->name('dashboard.tabungan.edit');
            Route::put('/{tabungan}', [TabunganController::class, 'update'])->name('dashboard.tabungan.update');
            Route::delete('/{tabungan}', [TabunganController::class, 'destroy'])->name('dashboard.tabungan.destroy');
        });

        // Payments
        Route::prefix('payments')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('dashboard.payments.index');
            Route::get('/pending', [PaymentController::class, 'pending'])->name('dashboard.payments.pending');
            Route::get('/create', [PaymentController::class, 'create'])->name('dashboard.payments.create');
            Route::post('/', [PaymentController::class, 'store'])->name('dashboard.payments.store');
            Route::get('/{payment}', [PaymentController::class, 'show'])->name('dashboard.payments.show');
            Route::get('/{payment}/edit', [PaymentController::class, 'edit'])->name('dashboard.payments.edit');
            Route::put('/{payment}', [PaymentController::class, 'update'])->name('dashboard.payments.update');
            Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('dashboard.payments.destroy');
            Route::post('/{payment}/verify', [PaymentController::class, 'verify'])->name('dashboard.payments.verify');
            Route::post('/{payment}/reject', [PaymentController::class, 'reject'])->name('dashboard.payments.reject');
            Route::post('/{payment}/proof', [PaymentController::class, 'uploadProof'])->name('dashboard.payments.proof');
        });

        // Tenant Invoices — tenant-facing subscription invoice pages
        Route::prefix('invoices')->group(function () {
            Route::get('/{invoice}', [TenantInvoiceController::class, 'show'])
                ->name('dashboard.invoices.show.session')
                ->whereNumber('invoice');
            Route::post('/{invoice}/pay', [TenantInvoiceController::class, 'pay'])
                ->name('dashboard.invoices.pay.session')
                ->whereNumber('invoice');
        });

        // Live Pengajian — semua role bisa lihat, hanya admin bisa kelola
        Route::prefix('live-pengajian')->group(function () {
            Route::get('/', [LivePengajianController::class, 'index'])->name('dashboard.live-pengajian.index');
            Route::get('/create', [LivePengajianController::class, 'create'])->name('dashboard.live-pengajian.create');
            Route::post('/', [LivePengajianController::class, 'store'])->name('dashboard.live-pengajian.store');
            Route::get('/{livePengajian}/edit', [LivePengajianController::class, 'edit'])->name('dashboard.live-pengajian.edit');
            Route::put('/{livePengajian}', [LivePengajianController::class, 'update'])->name('dashboard.live-pengajian.update');
            Route::delete('/{livePengajian}', [LivePengajianController::class, 'destroy'])->name('dashboard.live-pengajian.destroy');
            Route::post('/{livePengajian}/set-status', [LivePengajianController::class, 'setStatus'])->name('dashboard.live-pengajian.set-status');
        });
    });

// Subdomain root: redirect to dashboard or login
// Note: $tenant param is intentionally omitted — ResolveTenant strips it from the
// route parameter bag (to fix positional arg injection to controllers).
// Tenant context is available via TenantService if needed here.
Route::domain('{tenant}.' . config('app.app_domain'))
    ->group(function () {
        Route::get('/', function () {
            if (auth()->check()) {
                return redirect('/dashboard');
            }
            return redirect('/login');
        });
    });

// Subdomain dashboard: ALL tenant operational routes
Route::domain('{tenant}.' . config('app.app_domain'))
    ->middleware(['auth', 'password.change', 'tenant.resolve', 'owns.tenant', 'tenant.log', 'onboarding'])
    ->prefix('dashboard')
    ->group(function () {
        // Dashboard root → role-aware dashboard view
        Route::get('/', function () {
            return view('dashboard.index');
        })->name('dashboard.index');

        // Santri — accessible by admin, parent, student
        Route::get('/santri', [SantriController::class, 'index'])
            ->name('dashboard.santri.index');

        // Santri — authorization via SantriPolicy in controller
        Route::get('/santri/credentials/download', [SantriController::class, 'downloadCredentials'])
            ->name('dashboard.santri.credentials.download');
        Route::get('/santri/create', [SantriController::class, 'create'])
            ->name('dashboard.santri.create');
        Route::post('/santri', [SantriController::class, 'store'])
            ->name('dashboard.santri.store');
        Route::get('/santri/{id}/edit', [SantriController::class, 'edit'])
            ->name('dashboard.santri.edit')
            ->whereNumber('id');
        Route::put('/santri/{id}', [SantriController::class, 'update'])
            ->name('dashboard.santri.update')
            ->whereNumber('id');
        Route::delete('/santri/{id}', [SantriController::class, 'destroy'])
            ->name('dashboard.santri.destroy')
            ->whereNumber('id');

        // Santri show — AFTER /santri/create to avoid {id} catching 'create'
        // Authorization handled by SantriPolicy in controller
        Route::get('/santri/{id}', [SantriController::class, 'show'])
            ->name('dashboard.santri.show')
            ->where('id', '[0-9]+');

        // Parent routes
        Route::get('/parents', [ParentController::class, 'index'])
            ->name('dashboard.parent.index');

        // Parents — authorization via ParentPolicy in controller
        Route::get('/parents/credentials/download', [ParentController::class, 'downloadCredentials'])
            ->name('dashboard.parent.credentials.download');
        Route::get('/parents/{id}', [ParentController::class, 'show'])
            ->name('dashboard.parent.show')
            ->whereNumber('id');
        Route::get('/parents/create', [ParentController::class, 'create'])
            ->name('dashboard.parent.create');
        Route::post('/parents', [ParentController::class, 'store'])
            ->name('dashboard.parent.store');
        Route::get('/parents/{id}/edit', [ParentController::class, 'edit'])
            ->name('dashboard.parent.edit')
            ->whereNumber('id');
        Route::put('/parents/{id}', [ParentController::class, 'update'])
            ->name('dashboard.parent.update')
            ->whereNumber('id');
        Route::delete('/parents/{id}', [ParentController::class, 'destroy'])
            ->name('dashboard.parent.destroy')
            ->whereNumber('id');

        // Ustadz — authorization via UstadzPolicy in controller
        Route::get('/ustadz/credentials/download', [UstadzController::class, 'downloadCredentials'])
            ->name('dashboard.ustadz.credentials.download');
        Route::get('/ustadz', [UstadzController::class, 'index'])
            ->name('dashboard.ustadz.index');
        Route::get('/ustadz/create', [UstadzController::class, 'create'])
            ->name('dashboard.ustadz.create');
        Route::post('/ustadz', [UstadzController::class, 'store'])
            ->name('dashboard.ustadz.store');
        Route::get('/ustadz/{id}/edit', [UstadzController::class, 'edit'])
            ->name('dashboard.ustadz.edit')
            ->whereNumber('id');
        Route::put('/ustadz/{id}', [UstadzController::class, 'update'])
            ->name('dashboard.ustadz.update')
            ->whereNumber('id');
        Route::delete('/ustadz/{id}', [UstadzController::class, 'destroy'])
            ->name('dashboard.ustadz.destroy')
            ->whereNumber('id');
        Route::get('/ustadz/{id}', [UstadzController::class, 'show'])
            ->name('dashboard.ustadz.show')
            ->whereNumber('id');

        // ── Akademik - Program Based Structure ───────────────────────────────
        // Pattern: /dashboard/akademik/{programSlug}/{feature}
        // Program slugs loaded dynamically from Program model
        Route::prefix('akademik')->group(function () {

            // ── PROGRAM-BASED ROUTES ─────────────────────────────────────────
            // Middleware validates tenant can access the program (prevents cross-tenant access)
            Route::middleware(['program.access'])->prefix('{programSlug}')->group(function () {
                // Absensi
                Route::prefix('absensi')->group(function () {
                    Route::get('/', [AbsensiSantriController::class, 'index'])->name('dashboard.akademik.absensi.index');
                    Route::get('/input', [AbsensiSantriController::class, 'input'])->name('dashboard.akademik.absensi.input');
                    Route::post('/', [AbsensiSantriController::class, 'store'])->name('dashboard.akademik.absensi.store');
                    Route::get('/rekap', [AbsensiSantriController::class, 'rekap'])->name('dashboard.akademik.absensi.rekap');
                });

                // Subjects/Mata Pelajaran
                Route::prefix('subjects')->group(function () {
                    Route::get('/', [SubjectController::class, 'index'])->name('dashboard.akademik.subjects.index');
                    Route::get('/create', [SubjectController::class, 'create'])->name('dashboard.akademik.subjects.create');
                    Route::post('/', [SubjectController::class, 'store'])->name('dashboard.akademik.subjects.store');
                    Route::get('/{id}/edit', [SubjectController::class, 'edit'])->name('dashboard.akademik.subjects.edit')->whereNumber('id');
                    Route::put('/{id}', [SubjectController::class, 'update'])->name('dashboard.akademik.subjects.update')->whereNumber('id');
                    Route::delete('/{id}', [SubjectController::class, 'destroy'])->name('dashboard.akademik.subjects.destroy')->whereNumber('id');
                });
                
                // Kelas
                Route::prefix('kelas')->group(function () {
                    Route::get('/', [KelasController::class, 'index'])->name('dashboard.akademik.kelas.index');
                    Route::get('/create', [KelasController::class, 'create'])->name('dashboard.akademik.kelas.create');
                    Route::post('/', [KelasController::class, 'store'])->name('dashboard.akademik.kelas.store');
                    Route::get('/{id}/edit', [KelasController::class, 'edit'])->name('dashboard.akademik.kelas.edit')->whereNumber('id');
                    Route::put('/{id}', [KelasController::class, 'update'])->name('dashboard.akademik.kelas.update')->whereNumber('id');
                    Route::delete('/{id}', [KelasController::class, 'destroy'])->name('dashboard.akademik.kelas.destroy')->whereNumber('id');
                });
                
                // Jadwal
                Route::prefix('jadwal')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Dashboard\ScheduleController::class, 'index'])->name('dashboard.akademik.jadwal.index');
                    Route::get('/create', [\App\Http\Controllers\Dashboard\ScheduleController::class, 'create'])->name('dashboard.akademik.jadwal.create');
                    Route::post('/', [\App\Http\Controllers\Dashboard\ScheduleController::class, 'store'])->name('dashboard.akademik.jadwal.store');
                    Route::get('/{id}/edit', [\App\Http\Controllers\Dashboard\ScheduleController::class, 'edit'])->name('dashboard.akademik.jadwal.edit')->whereNumber('id');
                    Route::put('/{id}', [\App\Http\Controllers\Dashboard\ScheduleController::class, 'update'])->name('dashboard.akademik.jadwal.update')->whereNumber('id');
                    Route::delete('/{id}', [\App\Http\Controllers\Dashboard\ScheduleController::class, 'destroy'])->name('dashboard.akademik.jadwal.destroy')->whereNumber('id');
                });
                
                Route::get('/assessment-config', [ProgramAssessmentConfigController::class, 'index'])
                    ->name('dashboard.akademik.assessment-config.index');
                Route::patch('/assessment-config/{config}', [ProgramAssessmentConfigController::class, 'update'])
                    ->name('dashboard.akademik.assessment-config.update')
                    ->whereNumber('config');

                // Nilai
                Route::prefix('nilai')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Dashboard\NilaiController::class, 'index'])->name('dashboard.akademik.nilai.index');
                    Route::get('/input', [\App\Http\Controllers\Dashboard\NilaiController::class, 'input'])->name('dashboard.akademik.nilai.input');
                    Route::post('/store', [\App\Http\Controllers\Dashboard\NilaiController::class, 'storeBulk'])->name('dashboard.akademik.nilai.store');
                    Route::get('/dari-jadwal/{jadwalId}', [\App\Http\Controllers\Dashboard\NilaiController::class, 'fromJadwal'])->name('dashboard.akademik.nilai.dari-jadwal')->whereNumber('jadwalId');
                    Route::get('/show/{santriId}', [\App\Http\Controllers\Dashboard\NilaiController::class, 'show'])->name('dashboard.akademik.nilai.show')->whereNumber('santriId');
                });

                // Class Sessions
                Route::prefix('class-sessions')->group(function () {
                    Route::get('/', [ClassSessionController::class, 'index'])->name('dashboard.akademik.class-sessions.index');
                    Route::get('/create', [ClassSessionController::class, 'create'])->name('dashboard.akademik.class-sessions.create');
                    Route::post('/', [ClassSessionController::class, 'store'])->name('dashboard.akademik.class-sessions.store');
                    Route::get('/{id}/edit', [ClassSessionController::class, 'edit'])->name('dashboard.akademik.class-sessions.edit')->whereNumber('id');
                    Route::put('/{id}', [ClassSessionController::class, 'update'])->name('dashboard.akademik.class-sessions.update')->whereNumber('id');
                    Route::delete('/{id}', [ClassSessionController::class, 'destroy'])->name('dashboard.akademik.class-sessions.destroy')->whereNumber('id');
                });

                // Penugasan Ustadz ke Kelas
                Route::prefix('penugasan')->group(function () {
                    Route::get('/', [UstadzKelasController::class, 'index'])->name('dashboard.akademik.penugasan.index');
                    Route::get('/create', [UstadzKelasController::class, 'create'])->name('dashboard.akademik.penugasan.create');
                    Route::post('/', [UstadzKelasController::class, 'store'])->name('dashboard.akademik.penugasan.store');
                    Route::post('/ensure', [UstadzKelasController::class, 'ensure'])->name('dashboard.akademik.penugasan.ensure');
                    Route::get('/{id}/edit', [UstadzKelasController::class, 'edit'])->name('dashboard.akademik.penugasan.edit')->whereNumber('id');
                    Route::put('/{id}', [UstadzKelasController::class, 'update'])->name('dashboard.akademik.penugasan.update')->whereNumber('id');
                    Route::delete('/{id}', [UstadzKelasController::class, 'destroy'])->name('dashboard.akademik.penugasan.destroy')->whereNumber('id');
                });

                // E-learning
                Route::prefix('elearning')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Dashboard\ElearningController::class, 'index'])->name('dashboard.akademik.elearning.index');
                    Route::get('/create', [\App\Http\Controllers\Dashboard\ElearningController::class, 'create'])->name('dashboard.akademik.elearning.create');
                    Route::post('/', [\App\Http\Controllers\Dashboard\ElearningController::class, 'store'])->name('dashboard.akademik.elearning.store');
                    Route::get('/{id}/edit', [\App\Http\Controllers\Dashboard\ElearningController::class, 'edit'])->name('dashboard.akademik.elearning.edit')->whereNumber('id');
                    Route::put('/{id}', [\App\Http\Controllers\Dashboard\ElearningController::class, 'update'])->name('dashboard.akademik.elearning.update')->whereNumber('id');
                    Route::delete('/{id}', [\App\Http\Controllers\Dashboard\ElearningController::class, 'destroy'])->name('dashboard.akademik.elearning.destroy')->whereNumber('id');
                });

                // Materi (Lesson Materials)
                Route::prefix('materi')->group(function () {
                    Route::get('/', [MateriController::class, 'index'])->name('dashboard.akademik.materi.index');
                    Route::get('/create', [MateriController::class, 'create'])->name('dashboard.akademik.materi.create');
                    Route::post('/', [MateriController::class, 'store'])->name('dashboard.akademik.materi.store');
                    Route::get('/jadwal/{jadwalId}', [MateriController::class, 'byJadwal'])->name('dashboard.akademik.materi.by-jadwal')->whereNumber('jadwalId');
                    Route::get('/dari-jadwal/{jadwalId}', [MateriController::class, 'fromJadwal'])->name('dashboard.akademik.materi.dari-jadwal')->whereNumber('jadwalId');
                    Route::get('/{id}/edit', [MateriController::class, 'edit'])->name('dashboard.akademik.materi.edit')->whereNumber('id');
                    Route::put('/{id}', [MateriController::class, 'update'])->name('dashboard.akademik.materi.update')->whereNumber('id');
                    Route::delete('/{id}', [MateriController::class, 'destroy'])->name('dashboard.akademik.materi.destroy')->whereNumber('id');
                });

                // Hafalan from Jadwal shortcuts
                Route::prefix('hafalan-quran')->group(function () {
                    Route::get('/dari-jadwal/{jadwalId}', [HafalanQuranController::class, 'fromJadwal'])->name('dashboard.akademik.hafalan-quran.dari-jadwal')->whereNumber('jadwalId');
                    Route::post('/dari-jadwal/{jadwalId}', [HafalanQuranController::class, 'storeFromJadwal'])->name('dashboard.akademik.hafalan-quran.store-dari-jadwal')->whereNumber('jadwalId');
                });

                Route::prefix('hafalan-kitab')->group(function () {
                    Route::get('/dari-jadwal/{jadwalId}', [HafalanKitabController::class, 'fromJadwal'])->name('dashboard.akademik.hafalan-kitab.dari-jadwal')->whereNumber('jadwalId');
                    Route::post('/dari-jadwal/{jadwalId}', [HafalanKitabController::class, 'storeFromJadwal'])->name('dashboard.akademik.hafalan-kitab.store-dari-jadwal')->whereNumber('jadwalId');
                });

                // E-Raport
                Route::prefix('raport')->group(function () {
                    Route::get('/', [RaportController::class, 'index'])->name('dashboard.akademik.raport.index');
                    Route::get('/create', [RaportController::class, 'create'])->name('dashboard.akademik.raport.create');
                    Route::post('/generate', [RaportController::class, 'generate'])->name('dashboard.akademik.raport.generate');
                    Route::get('/{id}', [RaportController::class, 'show'])->name('dashboard.akademik.raport.show')->whereNumber('id');
                    Route::get('/{id}/edit', [RaportController::class, 'edit'])->name('dashboard.akademik.raport.edit')->whereNumber('id');
                    Route::put('/{id}', [RaportController::class, 'update'])->name('dashboard.akademik.raport.update')->whereNumber('id');
                    Route::post('/{id}/publish', [RaportController::class, 'publish'])->name('dashboard.akademik.raport.publish')->whereNumber('id');
                    Route::post('/{id}/unpublish', [RaportController::class, 'unpublish'])->name('dashboard.akademik.raport.unpublish')->whereNumber('id');
                    Route::post('/{id}/regenerate', [RaportController::class, 'regenerate'])->name('dashboard.akademik.raport.regenerate')->whereNumber('id');
                    Route::get('/{id}/print', [RaportController::class, 'print'])->name('dashboard.akademik.raport.print')->whereNumber('id');
                    Route::delete('/{id}', [RaportController::class, 'destroy'])->name('dashboard.akademik.raport.destroy')->whereNumber('id');
                });
            });
        });

        // ── SDM: Absensi Ustadz — authorization via AbsensiUstadzPolicy in controller
        Route::prefix('sdm/absensi-ustadz')->group(function () {
            Route::get('/', [AbsensiUstadzController::class, 'index'])->name('dashboard.sdm.absensi-ustadz.index');
            Route::post('/store', [AbsensiUstadzController::class, 'store'])->name('dashboard.sdm.absensi-ustadz.store');
            Route::post('/store-bulk', [AbsensiUstadzController::class, 'storeBulk'])->name('dashboard.sdm.absensi-ustadz.store-bulk');
            Route::get('/rekap', [AbsensiUstadzController::class, 'rekap'])->name('dashboard.sdm.absensi-ustadz.rekap');
        });

        // ── Keuangan
        Route::prefix('keuangan')->group(function () {
            Route::get('/tagihan', function () {
                return view('dashboard.keuangan.tagihan');
            })->name('dashboard.keuangan.tagihan');
        });

        // ── Phase 1E.5: Pesantren Core (Universal) ─────────────────────────────
        // Kepesantrenan: Kamar, Penempatan, Mutasi
        Route::prefix('kepesantrenan/kamar')->name('dashboard.kepesantrenan.kamar.')->group(function () {
            Route::get('/', [KamarController::class, 'index'])->name('index');
            Route::get('/create', [KamarController::class, 'create'])->name('create');
            Route::post('/', [KamarController::class, 'store'])->name('store');
            Route::get('/api/available', [KamarController::class, 'available'])->name('available');
            Route::get('/api/statistics', [KamarController::class, 'statistics'])->name('statistics');
            Route::get('/{kamar}/edit', [KamarController::class, 'edit'])->name('edit');
            Route::put('/{kamar}', [KamarController::class, 'update'])->name('update');
            Route::delete('/{kamar}', [KamarController::class, 'destroy'])->name('destroy');
            Route::get('/{kamar}', [KamarController::class, 'show'])->name('show');
        });

        Route::prefix('kepesantrenan/penempatan')->name('dashboard.kepesantrenan.penempatan.')->group(function () {
            Route::get('/', [PenempatanKamarController::class, 'index'])->name('index');
            Route::get('/create', [PenempatanKamarController::class, 'create'])->name('create');
            Route::post('/', [PenempatanKamarController::class, 'store'])->name('store');
            Route::get('/move/form', [PenempatanKamarController::class, 'moveForm'])->name('move-form');
            Route::post('/move', [PenempatanKamarController::class, 'move'])->name('move');
            Route::get('/api/by-kamar/{kamarId}', [PenempatanKamarController::class, 'byKamar'])->name('by-kamar');
            Route::get('/{penempatan}', [PenempatanKamarController::class, 'show'])->name('show');
            Route::delete('/{penempatan}', [PenempatanKamarController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('kepesantrenan/mutasi')->name('dashboard.kepesantrenan.mutasi.')->group(function () {
            Route::get('/', [MutasiKamarController::class, 'index'])->name('index');
            Route::get('/create', [MutasiKamarController::class, 'create'])->name('create');
            Route::post('/', [MutasiKamarController::class, 'store'])->name('store');
            Route::get('/santri/{santriId}', [MutasiKamarController::class, 'bySantri'])->name('by-santri');
            Route::get('/api/statistics', [MutasiKamarController::class, 'statistics'])->name('statistics');
            Route::get('/{mutasi}', [MutasiKamarController::class, 'show'])->name('show');
        });

        // Pelanggaran & Sanksi
        Route::prefix('kepesantrenan/pelanggaran')->name('dashboard.kepesantrenan.pelanggaran.')->group(function () {
            Route::get('/', [PelanggaranController::class, 'index'])->name('index');
            Route::get('/create', [PelanggaranController::class, 'create'])->name('create');
            Route::post('/', [PelanggaranController::class, 'store'])->name('store');
            Route::get('/api/statistics', [PelanggaranController::class, 'statistics'])->name('statistics');
            Route::get('/santri/{santriId}', [PelanggaranController::class, 'bySantri'])->name('by-santri');
            Route::get('/{pelanggaran}/edit', [PelanggaranController::class, 'edit'])->name('edit');
            Route::put('/{pelanggaran}', [PelanggaranController::class, 'update'])->name('update');
            Route::delete('/{pelanggaran}', [PelanggaranController::class, 'destroy'])->name('destroy');
            Route::post('/{pelanggaran}/process', [PelanggaranController::class, 'process'])->name('process');
            Route::post('/{pelanggaran}/complete', [PelanggaranController::class, 'complete'])->name('complete');
            Route::get('/{pelanggaran}', [PelanggaranController::class, 'show'])->name('show');
        });

        Route::prefix('kepesantrenan/sanksi')->name('dashboard.kepesantrenan.sanksi.')->group(function () {
            Route::get('/', [SanksiController::class, 'index'])->name('index');
            Route::get('/create', [SanksiController::class, 'create'])->name('create');
            Route::post('/', [SanksiController::class, 'store'])->name('store');
            Route::get('/api/statistics', [SanksiController::class, 'statistics'])->name('statistics');
            Route::get('/santri/{santriId}', [SanksiController::class, 'bySantri'])->name('by-santri');
            Route::get('/{sanksi}/edit', [SanksiController::class, 'edit'])->name('edit');
            Route::put('/{sanksi}', [SanksiController::class, 'update'])->name('update');
            Route::delete('/{sanksi}', [SanksiController::class, 'destroy'])->name('destroy');
            Route::post('/{sanksi}/complete', [SanksiController::class, 'complete'])->name('complete');
            Route::post('/{sanksi}/cancel', [SanksiController::class, 'cancel'])->name('cancel');
            Route::get('/{sanksi}', [SanksiController::class, 'show'])->name('show');
        });

        // Perizinan
        Route::prefix('kepesantrenan/perizinan')->name('dashboard.kepesantrenan.perizinan.')->group(function () {
            Route::get('/', [PerizinanController::class, 'index'])->name('index');
            Route::get('/pending', [PerizinanController::class, 'pending'])->name('pending');
            Route::get('/create', [PerizinanController::class, 'create'])->name('create');
            Route::post('/', [PerizinanController::class, 'store'])->name('store');
            Route::get('/santri/{santriId}', [PerizinanController::class, 'bySantri'])->name('by-santri');
            Route::get('/{perizinan}/edit', [PerizinanController::class, 'edit'])->name('edit');
            Route::put('/{perizinan}', [PerizinanController::class, 'update'])->name('update');
            Route::delete('/{perizinan}', [PerizinanController::class, 'destroy'])->name('destroy');
            Route::post('/{perizinan}/approve', [PerizinanController::class, 'approve'])->name('approve');
            Route::post('/{perizinan}/reject', [PerizinanController::class, 'reject'])->name('reject');
            Route::post('/{perizinan}/return', [PerizinanController::class, 'recordReturn'])->name('return');
            Route::get('/{perizinan}', [PerizinanController::class, 'show'])->name('show');
        });

        // Monitoring Karakter
        Route::prefix('kepesantrenan/monitoring-karakter')->name('dashboard.kepesantrenan.monitoring-karakter.')->group(function () {
            Route::get('/', [MonitoringKarakterController::class, 'index'])->name('index');
            Route::get('/create', [MonitoringKarakterController::class, 'create'])->name('create');
            Route::post('/', [MonitoringKarakterController::class, 'store'])->name('store');
            Route::get('/santri/{santriId}', [MonitoringKarakterController::class, 'bySantri'])->name('by-santri');
            Route::get('/{monitoring}/edit', [MonitoringKarakterController::class, 'edit'])->name('edit');
            Route::put('/{monitoring}', [MonitoringKarakterController::class, 'update'])->name('update');
            Route::delete('/{monitoring}', [MonitoringKarakterController::class, 'destroy'])->name('destroy');
            Route::get('/{monitoring}', [MonitoringKarakterController::class, 'show'])->name('show');
        });

        // Kegiatan Harian
        Route::prefix('kepesantrenan/kegiatan-harian')->name('dashboard.kepesantrenan.kegiatan-harian.')->group(function () {
            Route::get('/', [KegiatanHarianController::class, 'index'])->name('index');
            Route::get('/create', [KegiatanHarianController::class, 'create'])->name('create');
            Route::post('/', [KegiatanHarianController::class, 'store'])->name('store');
            Route::get('/api/by-date/{date}', [KegiatanHarianController::class, 'byDate'])->name('by-date');
            Route::get('/santri/{santriId}', [KegiatanHarianController::class, 'bySantri'])->name('by-santri');
            Route::get('/{kegiatan}/edit', [KegiatanHarianController::class, 'edit'])->name('edit');
            Route::put('/{kegiatan}', [KegiatanHarianController::class, 'update'])->name('update');
            Route::delete('/{kegiatan}', [KegiatanHarianController::class, 'destroy'])->name('destroy');
            Route::post('/{kegiatan}/done', [KegiatanHarianController::class, 'markAsDone'])->name('done');
            Route::post('/{kegiatan}/missed', [KegiatanHarianController::class, 'markAsMissed'])->name('missed');
            Route::get('/{kegiatan}', [KegiatanHarianController::class, 'show'])->name('show');
        });

        // Tenant Invoices — tenant-facing subscription invoice pages
        Route::prefix('invoices')->group(function () {
            Route::get('/{invoice}', [TenantInvoiceController::class, 'show'])
                ->name('dashboard.invoices.show')
                ->whereNumber('invoice');
            Route::post('/{invoice}/pay', [TenantInvoiceController::class, 'pay'])
                ->name('dashboard.invoices.pay')
                ->whereNumber('invoice');
        });
    });

// Test routes for tenant resolution
Route::domain('{tenant}.*')
    ->middleware(['web', 'tenant.resolve'])
    ->get('/test/tenant', function () {
        $tenant = app(\App\Services\TenantService::class)->getTenant();
        
        if (!$tenant) {
            return response()->json(['error' => 'No tenant resolved'], 401);
        }
        
        return response()->json([
            'tenant_id' => $tenant->id,
            'tenant_name' => $tenant->name,
        ]);
    });


require __DIR__.'/auth.php';
