<?php

namespace App\Providers;

use App\Models\HafalanKitab;
use App\Models\HafalanQuran;
use App\Models\Invoice;
use App\Models\Kelas;
use App\Models\LivePengajian;
use App\Models\Parents;
use App\Models\Plan;
use App\Models\SaasPayment;
use App\Models\Santri;
use App\Models\UsageLog;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\TargetHafalan;
use App\Models\Tenant;
use App\Models\Ustadz;
use App\Models\UstadzKelas;
use App\Policies\AcademicProgramPolicy;
use App\Policies\HafalanKitabPolicy;
use App\Policies\HafalanQuranPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\KelasPolicy;
use App\Policies\LivePengajianPolicy;
use App\Policies\ParentPolicy;
use App\Policies\PlanPolicy;
use App\Policies\SaasPaymentPolicy;
use App\Policies\SantriPolicy;
use App\Policies\SubscriptionPolicy;
use App\Policies\TargetHafalanPolicy;
use App\Policies\UsageLogPolicy;
use App\Policies\TenantManagementPolicy;
use App\Policies\UstadzKelasPolicy;
use App\Policies\UstadzPolicy;
use App\Services\PermissionRegistrar;
use App\View\Composers\SidebarComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Santri::class        => SantriPolicy::class,
        Ustadz::class        => UstadzPolicy::class,
        Parents::class       => ParentPolicy::class,
        HafalanKitab::class  => HafalanKitabPolicy::class,
        HafalanQuran::class  => HafalanQuranPolicy::class,
        Kelas::class         => KelasPolicy::class,
        UstadzKelas::class   => UstadzKelasPolicy::class,
        Subject::class       => AcademicProgramPolicy::class,
        TargetHafalan::class => TargetHafalanPolicy::class,
        LivePengajian::class => LivePengajianPolicy::class,
        Tenant::class        => TenantManagementPolicy::class,
        Subscription::class  => SubscriptionPolicy::class,
        Plan::class          => PlanPolicy::class,
        Invoice::class       => InvoicePolicy::class,
        SaasPayment::class   => SaasPaymentPolicy::class,
        UsageLog::class      => UsageLogPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Load helper functions
        require_once app_path('Helpers/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Register all permissions as Gates
        // USER → ROLE → RELATION → RESOURCE architecture
        PermissionRegistrar::register();


        // Rate limiter for registration endpoints
        // Prevents spam/abuse of tenant creation (disabled in testing)
        RateLimiter::for('registration', function (Request $request) {
            if (app()->environment('testing')) {
                return Limit::none();
            }
            return Limit::perHour(5)->by($request->ip());
        });

        // Stricter rate limiter for Pesantren (tenant) registration (disabled in testing)
        RateLimiter::for('tenant-registration', function (Request $request) {
            if (app()->environment('testing')) {
                return Limit::none();
            }
            return Limit::perHour(2)->by($request->ip());
        });

        // Shared sidebar composer: single source of truth for tenant sidebar menu
        View::composer('layouts.tenant.sidebar', SidebarComposer::class);

        // Eager-load ustadz subjects for the ustadz dashboard partial so it
        // can display subject names without issuing extra queries in Blade.
        View::composer('dashboard.partials.dashboard-ustadz', function ($view) {
            auth()->user()?->loadMissing(['ustadz.subjects']);
        });
    }
}
