<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AbsensiSantri;
use App\Models\AbsensiUstadz;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\ClassSession;
use App\Models\DiniyahAssessment;
use App\Models\DiniyahHafalan;
use App\Models\DiniyahMonitoring;
use App\Models\Elearning;
use App\Models\HafalanKitab;
use App\Models\HafalanQuran;
use App\Models\Invoice;
use App\Models\Kelas;
use App\Models\LivePengajian;
use App\Models\Nilai;
use App\Models\Parents;
use App\Models\Plan;
use App\Models\ProgramAssessmentConfig;
use App\Models\Santri;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Subscription;
use App\Models\Tabungan;
use App\Models\Tenant;
use App\Models\Ustadz;
use App\Models\UstadzKelas;
use App\Models\User;
use App\Models\Assignment;
use App\Models\Muhadatsah;
use App\Models\Muhadhoroh;
use App\Models\PlacementTest;
use App\Models\Vocabulary;
use Illuminate\Support\Facades\Gate;

/**
 * NavigationGateService — UI visibility based on existing Policies.
 *
 * This service translates policy `viewAny` / `create` checks into boolean
 * flags that Blade views consume to show/hide navigation and action elements.
 *
 * Rules:
 * - NEVER duplicate business logic — always delegate to the registered policy.
 * - No hardcoded role checks.
 * - Fail-closed: if a policy is missing, default to hidden.
 */
final class NavigationGateService
{
    private User $user;

    /** @var array<string, bool> Cached results for the request lifecycle */
    private array $cache = [];

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Create instance for the currently authenticated user.
     */
    public static function forUser(?User $user = null): self
    {
        $user = $user ?? auth()->user();

        return new self($user);
    }

    // =========================================================================
    // Navigation visibility checks (delegates to Policy::viewAny)
    // =========================================================================

    public function canViewSantri(): bool
    {
        return $this->check('viewAny', Santri::class);
    }

    public function canViewParents(): bool
    {
        return $this->check('viewAny', Parents::class);
    }

    public function canViewUstadz(): bool
    {
        return $this->check('viewAny', Ustadz::class);
    }

    public function canViewAbsensiSantri(): bool
    {
        return $this->check('viewAny', AbsensiSantri::class);
    }

    public function canViewAbsensiUstadz(): bool
    {
        return $this->check('viewAny', AbsensiUstadz::class);
    }

    public function canViewKelas(): bool
    {
        return $this->check('viewAny', Kelas::class);
    }

    public function canViewJadwal(): bool
    {
        return $this->check('viewAny', Schedule::class);
    }

    public function canViewClassSessions(): bool
    {
        return $this->check('viewAny', ClassSession::class);
    }

    public function canViewNilai(): bool
    {
        return $this->check('viewAny', Nilai::class);
    }

    public function canViewAssessmentConfig(): bool
    {
        return $this->check('viewAny', ProgramAssessmentConfig::class);
    }

    public function canViewElearning(): bool
    {
        return $this->check('viewAny', Elearning::class);
    }

    public function canViewMataPelajaran(): bool
    {
        return $this->check('viewAny', Subject::class);
    }

    public function canViewUstadzKelas(): bool
    {
        return $this->check('viewAny', UstadzKelas::class);
    }

    public function canViewHafalanQuran(): bool
    {
        return $this->check('viewAny', HafalanQuran::class);
    }

    public function canViewHafalanKitab(): bool
    {
        return $this->check('viewAny', HafalanKitab::class);
    }

    public function canViewSpp(): bool
    {
        return $this->check('viewAny', Bill::class);
    }

    public function canViewBillPayments(): bool
    {
        return $this->check('viewAny', BillPayment::class);
    }

    public function canViewTabungan(): bool
    {
        return $this->check('viewAny', Tabungan::class);
    }

    public function canViewLivePengajian(): bool
    {
        return $this->check('viewAny', LivePengajian::class);
    }

    public function canViewTargetHafalan(): bool
    {
        return $this->check('viewAny', \App\Models\TargetHafalan::class);
    }

    public function canViewKamar(): bool
    {
        return $this->check('viewAny', \App\Models\Kamar::class);
    }

    public function canViewPenempatanKamar(): bool
    {
        return $this->check('viewAny', \App\Models\PenempatanKamar::class);
    }

    public function canViewMutasiKamar(): bool
    {
        return $this->check('viewAny', \App\Models\MutasiKamar::class);
    }

    public function canViewPelanggaran(): bool
    {
        return $this->check('viewAny', \App\Models\Pelanggaran::class);
    }

    public function canViewSanksi(): bool
    {
        return $this->check('viewAny', \App\Models\Sanksi::class);
    }

    public function canViewPerizinan(): bool
    {
        return $this->check('viewAny', \App\Models\Perizinan::class);
    }

    public function canViewMonitoringKarakter(): bool
    {
        return $this->check('viewAny', \App\Models\MonitoringKarakter::class);
    }

    public function canViewKegiatanHarian(): bool
    {
        return $this->check('viewAny', \App\Models\KegiatanHarian::class);
    }

    // =========================================================================
    // Diniyah Pack navigation checks
    // ARCHITECTURE FROZEN: Unified DiniyahHafalan entity (replaces Doa/Hadits/Surat)
    // =========================================================================

    public function canViewDiniyahHafalan(): bool
    {
        return $this->check('viewAny', Assignment::class);
    }

    /**
     * Legacy compatibility — delegates to unified entity
     */
    public function canViewDiniyahHafalanDoa(): bool
    {
        return $this->canViewDiniyahHafalan();
    }

    public function canViewDiniyahHafalanHadits(): bool
    {
        return $this->canViewDiniyahHafalan();
    }

    public function canViewDiniyahHafalanSurat(): bool
    {
        return $this->canViewDiniyahHafalan();
    }

    // =========================================================================
    // Diniyah Monitoring — Unified Entity
    // ARCHITECTURE FROZEN: DiniyahMonitoring handles all monitoring types
    // =========================================================================

    public function canViewDiniyahMonitoring(): bool
    {
        return $this->check('viewAny', DiniyahMonitoring::class);
    }

    // ARCHITECTURE FROZEN: DiniyahAssessment handles all assessment types
    // =========================================================================

    public function canViewDiniyahAssessment(): bool
    {
        return $this->check('viewAny', DiniyahAssessment::class);
    }

    /**
     * Legacy compatibility — delegates to unified entity
     */
    public function canViewDiniyahNilaiKeagamaan(): bool
    {
        return $this->canViewDiniyahAssessment();
    }

    public function canViewDiniyahNilaiAkhlak(): bool
    {
        return $this->canViewDiniyahAssessment();
    }

    /**
     * Can the user access any Diniyah Pack feature?
     */
    public function canViewDiniyahSection(): bool
    {
        return $this->canViewDiniyahHafalan()
            || $this->canViewDiniyahMonitoring()
            || $this->canViewDiniyahAssessment();
    }

    // =========================================================================
    // Modern Pack
    // =========================================================================

    public function canViewModernVocabulary(): bool
    {
        return $this->check('viewAny', Assignment::class);
    }

    public function canViewModernMuhadatsah(): bool
    {
        return $this->check('viewAny', Assignment::class);
    }

    public function canViewModernMuhadhoroh(): bool
    {
        return $this->check('viewAny', Assignment::class);
    }

    public function canViewModernPlacementTest(): bool
    {
        return $this->check('viewAny', PlacementTest::class);
    }

    public function canViewMateri(): bool
    {
        return $this->check('viewAny', \App\Models\Materi::class);
    }

    public function canViewRaport(): bool
    {
        return $this->check('viewAny', \App\Models\Raport::class);
    }

    // =========================================================================
    // Action visibility checks (delegates to Policy::create)
    // =========================================================================

    public function canCreateSantri(): bool
    {
        return $this->check('create', Santri::class);
    }

    public function canCreateUstadz(): bool
    {
        return $this->check('create', Ustadz::class);
    }

    public function canCreateParent(): bool
    {
        return $this->check('create', Parents::class);
    }

    public function canCreateSpp(): bool
    {
        return $this->check('create', Bill::class);
    }

    public function canCreateTabungan(): bool
    {
        return $this->check('create', Tabungan::class);
    }

    public function canCreateLivePengajian(): bool
    {
        return $this->check('create', LivePengajian::class);
    }

    // =========================================================================
    // Role identity helpers (for role-aware UI branching)
    // =========================================================================

    public function isUstadz(): bool
    {
        return $this->user->isUstadz();
    }

    public function isAdmin(): bool
    {
        return $this->user->isAdmin();
    }

    public function isStudent(): bool
    {
        return $this->user->isStudent();
    }

    public function isParent(): bool
    {
        return $this->user->isParent();
    }

    // =========================================================================
    // Compound checks for grouped navigation sections
    // =========================================================================

    /**
     * Can the user access any academic feature (absensi, kelas, jadwal, nilai, elearning, subjects)?
     */
    public function canViewAcademicSection(): bool
    {
        return $this->canViewAbsensiSantri()
            || $this->canViewKelas()
            || $this->canViewJadwal()
            || $this->canViewNilai()
            || $this->canViewElearning()
            || $this->canViewMataPelajaran();
    }

    /**
     * Can the user access any kepesantrenan feature (kamar, penempatan, mutasi, pelanggaran, sanksi, perizinan, monitoring, kegiatan, hafalan)?
     */
    public function canViewKepesantrenanSection(): bool
    {
        return $this->canViewKamar()
            || $this->canViewPenempatanKamar()
            || $this->canViewMutasiKamar()
            || $this->canViewPelanggaran()
            || $this->canViewSanksi()
            || $this->canViewPerizinan()
            || $this->canViewMonitoringKarakter()
            || $this->canViewKegiatanHarian()
            || $this->canViewHafalanQuran()
            || $this->canViewHafalanKitab();
    }

    /**
     * Alias for sidebar compatibility.
     */
    public function canViewKepesantrenan(): bool
    {
        return $this->canViewKepesantrenanSection();
    }

    /**
     * Can the user access any financial feature (Tagihan/Riwayat Pembayaran/Tabungan)?
     */
    public function canViewFinanceSection(): bool
    {
        return $this->canViewSpp()
            || $this->canViewTabungan()
            || $this->canViewBillPayments();
    }

    // =========================================================================
    // Super Admin navigation visibility (delegates to super-admin policies)
    // =========================================================================

    /**
     * Can the user access the super admin panel at all?
     */
    public function canAccessSuperAdminPanel(): bool
    {
        return $this->checkGate('access-super-admin-panel');
    }

    public function canViewTenants(): bool
    {
        return $this->check('viewAny', Tenant::class);
    }

    public function canCreateTenant(): bool
    {
        return $this->check('create', Tenant::class);
    }

    public function canViewSubscriptions(): bool
    {
        return $this->check('viewAny', Subscription::class);
    }

    public function canCreateSubscription(): bool
    {
        return $this->check('create', Subscription::class);
    }

    public function canViewPlans(): bool
    {
        return $this->check('viewAny', Plan::class);
    }

    public function canCreatePlan(): bool
    {
        return $this->check('create', Plan::class);
    }

    public function canViewInvoices(): bool
    {
        return $this->check('viewAny', Invoice::class);
    }

    public function canViewRevenue(): bool
    {
        return $this->checkGate('access-super-admin-panel');
    }

    public function canViewUsage(): bool
    {
        return $this->checkGate('access-super-admin-panel');
    }

    public function canViewPrograms(): bool
    {
        return $this->checkGate('access-super-admin-panel');
    }

    /**
     * Can the user access the SaaS billing section (subscriptions, invoices, plans, revenue)?
     */
    public function canViewSaasBillingSection(): bool
    {
        return $this->canViewSubscriptions()
            || $this->canViewInvoices()
            || $this->canViewPlans()
            || $this->canViewRevenue();
    }

    // =========================================================================
    // Internal
    // =========================================================================

    /**
     * Resolve a gate string from config: null => true, missing => false, existing => execute.
     * Use this in views instead of manual method_exists checks.
     */
    public function allows(?string $gate): bool
    {
        if ($gate === null) {
            return true;
        }

        if (!method_exists($this, $gate)) {
            return false;
        }

        return $this->{$gate}();
    }

    private function check(string $ability, string $modelClass): bool
    {
        $key = $ability . ':' . $modelClass;

        if (!isset($this->cache[$key])) {
            $this->cache[$key] = Gate::forUser($this->user)->check($ability, $modelClass);
        }

        return $this->cache[$key];
    }

    private function checkGate(string $gate): bool
    {
        $key = 'gate:' . $gate;

        if (!isset($this->cache[$key])) {
            $this->cache[$key] = Gate::forUser($this->user)->check($gate);
        }

        return $this->cache[$key];
    }
}
