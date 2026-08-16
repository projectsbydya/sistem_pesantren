<?php

namespace App\Providers;

use App\Models\AbsensiSantri;
use App\Models\AbsensiUstadz;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\BugReport;
use App\Models\ClassSession;
use App\Models\Elearning;
use App\Models\DiniyahAssessment;
use App\Models\DiniyahHafalan;
use App\Models\DiniyahMonitoring;
use App\Models\HafalanKitab;
use App\Models\HafalanNilai;
use App\Models\HafalanQuran;
use App\Models\Kamar;
use App\Models\Kelas;
use App\Models\LivePengajian;
use App\Models\Nilai;
use App\Models\Parents;
use App\Models\Payment;
use App\Models\Perizinan;
use App\Models\Program;
use App\Models\ProgramAssessmentConfig;
use App\Models\Raport;
use App\Models\Santri;
use App\Models\SantriProgram;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Tabungan;
use App\Models\User;
use App\Models\Assignment;
use App\Models\AssignmentMember;
use App\Models\TargetHafalan;
use App\Models\Tenant;
use App\Models\Muhadatsah;
use App\Models\Muhadhoroh;
use App\Models\PlacementTest;
use App\Models\PlacementTestResult;
use App\Models\Vocabulary;
use App\Models\Ustadz;
use App\Models\UstadzKelas;
use App\Policies\AbsensiPolicy;
use App\Policies\AbsensiUstadzPolicy;
use App\Policies\AcademicProgramPolicy;
use App\Policies\BillPolicy;
use App\Policies\BillPaymentPolicy;
use App\Policies\BugReportPolicy;
use App\Policies\ProgramAssessmentConfigPolicy;
use App\Policies\ClassSessionPolicy;
use App\Policies\ElearningPolicy;
use App\Policies\DiniyahAssessmentPolicy;
use App\Policies\DiniyahHafalanPolicy;
use App\Policies\DiniyahMonitoringPolicy;
use App\Policies\HafalanKitabPolicy;
use App\Policies\HafalanNilaiPolicy;
use App\Policies\HafalanQuranPolicy;
use App\Policies\KamarPolicy;
use App\Policies\KelasPolicy;
use App\Policies\LivePengajianPolicy;
use App\Policies\NilaiPolicy;
use App\Policies\ParentPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PerizinanPolicy;
use App\Policies\ProgramPolicy;
use App\Policies\RaportPolicy;
use App\Policies\SantriPolicy;
use App\Policies\SantriProgramPolicy;
use App\Policies\SchedulePolicy;
use App\Policies\TabunganPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\AssignmentMemberPolicy;
use App\Policies\TargetHafalanPolicy;
use App\Policies\TenantManagementPolicy;
use App\Policies\UstadzKelasPolicy;
use App\Policies\UstadzPolicy;
use App\Policies\UserPolicy;
use App\Policies\MuhadatsahPolicy;
use App\Policies\MuhadhorohPolicy;
use App\Policies\PlacementTestPolicy;
use App\Policies\PlacementTestResultPolicy;
use App\Policies\VocabularyPolicy;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Absensi Policies
        Gate::policy(AbsensiSantri::class, AbsensiPolicy::class);
        Gate::policy(AbsensiUstadz::class, AbsensiUstadzPolicy::class);

        // Academic Policies
        Gate::policy(Schedule::class, SchedulePolicy::class);
        Gate::policy(ClassSession::class, ClassSessionPolicy::class);
        Gate::policy(Subject::class, AcademicProgramPolicy::class);
        Gate::policy(Program::class, ProgramPolicy::class);
        Gate::policy(ProgramAssessmentConfig::class, ProgramAssessmentConfigPolicy::class);
        Gate::policy(SantriProgram::class, SantriProgramPolicy::class);
        Gate::policy(Nilai::class, NilaiPolicy::class);
        Gate::policy(Raport::class, RaportPolicy::class);
        Gate::policy(HafalanNilai::class, HafalanNilaiPolicy::class);

        // Class-centric Assignment Policies
        Gate::policy(Assignment::class, AssignmentPolicy::class);
        Gate::policy(AssignmentMember::class, AssignmentMemberPolicy::class);

        // Modern Pack Policies (legacy per-student models retained for data access)
        Gate::policy(Vocabulary::class, VocabularyPolicy::class);
        Gate::policy(Muhadatsah::class, MuhadatsahPolicy::class);
        Gate::policy(Muhadhoroh::class, MuhadhorohPolicy::class);
        Gate::policy(PlacementTest::class, PlacementTestPolicy::class);
        Gate::policy(PlacementTestResult::class, PlacementTestResultPolicy::class);

        // Diniyah Pack Policies
        // ARCHITECTURE FROZEN: Unified entities replace legacy separate entities.
        Gate::policy(DiniyahHafalan::class, DiniyahHafalanPolicy::class);
        Gate::policy(DiniyahMonitoring::class, DiniyahMonitoringPolicy::class);
        Gate::policy(DiniyahAssessment::class, DiniyahAssessmentPolicy::class);

        // Hafalan Policies
        Gate::policy(HafalanQuran::class, HafalanQuranPolicy::class);
        Gate::policy(HafalanKitab::class, HafalanKitabPolicy::class);
        Gate::policy(TargetHafalan::class, TargetHafalanPolicy::class);

        // Kelas & Kamar Policies
        Gate::policy(Kelas::class, KelasPolicy::class);
        Gate::policy(Kamar::class, KamarPolicy::class);
        Gate::policy(UstadzKelas::class, UstadzKelasPolicy::class);

        // Kepesantrenan Policies
        Gate::policy(Perizinan::class, PerizinanPolicy::class);

        // User Management Policies
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Santri::class, SantriPolicy::class);
        Gate::policy(Ustadz::class, UstadzPolicy::class);
        Gate::policy(Parents::class, ParentPolicy::class);

        // Core / Support Policies
        Gate::policy(BugReport::class, BugReportPolicy::class);

        // Financial Policies
        Gate::policy(Bill::class, BillPolicy::class);
        Gate::policy(BillPayment::class, BillPaymentPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Tabungan::class, TabunganPolicy::class);

        // Content Policies
        Gate::policy(Elearning::class, ElearningPolicy::class);
        Gate::policy(LivePengajian::class, LivePengajianPolicy::class);

        // Tenant Management Policy
        Gate::policy(Tenant::class, TenantManagementPolicy::class);

        // Role assignment — only super_admin may change a user's role
        // This is the primary privilege-escalation guard
        Gate::define('update-user-role', fn ($user) => $user->isSuperAdmin());

        // Super admin panel access
        Gate::define('access-super-admin-panel', fn ($user) => $user->isSuperAdmin());

        // Raport generation scoped to the selected santri and program
        Gate::define('generate-raport-for', function (User $user, Santri $santri, Program $program) {
            return app(RaportPolicy::class)->generateFor($user, $santri, $program);
        });
    }
}
