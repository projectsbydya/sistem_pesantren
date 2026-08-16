<?php

namespace App\Services;

use App\Models\Program;
use App\Models\Tenant;
use App\Models\TenantProgram;
use App\Models\TenantSetupProgress;
use App\Models\UstadzKelas;
use Illuminate\Support\Collection;

/**
 * TenantSetupService — Phase 1E FREEZE
 *
 * Manages universal onboarding setup progress.
 * Steps are UNIVERSAL modules only: Program, Kelas, Subject, Ustadz, Santri, Jadwal.
 *
 * DO NOT add setup steps for Program Feature Pack modules
 * (Hafalan targets, Placement Tests, etc.).
 * Those belong in Phase 1F ProgramFeaturePackService.
 */
class TenantSetupService
{
    /**
     * Initialize setup progress untuk tenant baru
     */
    public static function initialize(Tenant $tenant): TenantSetupProgress
    {
        return TenantSetupProgress::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'setup_status' => 'pending',
                'progress_percentage' => 0,
            ]
        );
    }

    /**
     * Get atau create setup progress untuk tenant
     */
    public static function getProgress(?int $tenantId = null): TenantSetupProgress
    {
        $tenantId = $tenantId ?? tenant_id();
        
        if (!$tenantId) {
            throw new \RuntimeException('Tenant ID not available');
        }

        $progress = TenantSetupProgress::where('tenant_id', $tenantId)->first();

        if (!$progress) {
            $progress = self::initialize(Tenant::findOrFail($tenantId));
        }

        return $progress;
    }

    /**
     * Check apakah tenant sudah siap operasional
     */
    public static function isSiapOperasional(?int $tenantId = null): bool
    {
        try {
            $progress = self::getProgress($tenantId);
            return $progress->setup_status === 'siap_operasional';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check apakah tenant sudah memilih program
     */
    public static function hasSelectedPrograms(?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? tenant_id();
        
        if (!$tenantId) {
            return false;
        }

        return TenantProgram::where('tenant_id', $tenantId)->where('is_active', true)->exists();
    }

    /**
     * Get programs yang tersedia untuk dipilih tenant.
     * Hanya program dengan is_available_for_tenants = true yang dikembalikan.
     * Untuk membuat program tersedia: set is_available_for_tenants = true di tabel programs.
     */
    public static function getAvailablePrograms(): Collection
    {
        return Program::active()
            ->availableForTenants()
            ->ordered()
            ->get();
    }

    /**
     * Get program-program yang dipilih tenant
     */
    public static function getTenantPrograms(?int $tenantId = null): Collection
    {
        $tenantId = $tenantId ?? tenant_id();

        if (!$tenantId) {
            return collect();
        }

        return TenantProgram::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with('program')
            ->get();
    }

    /**
     * Get first active program for tenant (for building URLs that require programSlug).
     */
    public static function getFirstActiveProgram(?int $tenantId = null): ?Program
    {
        $tenantId = $tenantId ?? tenant_id();

        if (!$tenantId) {
            return null;
        }

        $tenantProgram = TenantProgram::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->with('program')
            ->first();

        return $tenantProgram?->program;
    }

    /**
     * Simpan pilihan program untuk tenant
     */
    public static function saveProgramSelection(array $programIds, ?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? tenant_id();
        
        if (!$tenantId) {
            throw new \RuntimeException('Tenant ID not available');
        }

        // Nonaktifkan semua program dulu
        TenantProgram::where('tenant_id', $tenantId)
            ->update(['is_active' => false]);

        // Aktifkan program yang dipilih
        foreach ($programIds as $programId) {
            TenantProgram::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'program_id' => $programId,
                ],
                [
                    'is_active' => true,
                    'activated_at' => now(),
                ]
            );
        }

        // Mark step as complete
        $progress = self::getProgress($tenantId);
        $progress->completeStep('program_selected');

        // Refresh all progress after program change
        self::refreshProgress($tenantId);
    }

    /**
     * Get progress percentage
     */
    public static function getProgressPercentage(?int $tenantId = null): int
    {
        try {
            return self::getProgress($tenantId)->progress_percentage;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get next step untuk tenant
     */
    public static function getNextStep(?int $tenantId = null): ?array
    {
        try {
            return self::getProgress($tenantId)->getNextStep();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Force complete all onboarding steps (for skip/escape hatch).
     * Use with caution - only for advanced users who will manage kelas,
     * subjects, ustadz, penugasan and jadwal manually via the regular
     * academic menus instead of the wizard.
     *
     * This is a deliberate bypass of the normal (live-data-driven) progress
     * calculation: it marks legacy step flags true for continuity/analytics
     * and force-sets setup_status directly, WITHOUT requiring the real data
     * to exist yet (unlike TenantSetupService::refreshProgress()).
     */
    public static function forceCompleteAllSteps(?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? tenant_id();

        if (!$tenantId) {
            return;
        }

        $progress = self::getProgress($tenantId);

        $legacyFlags = [
            'program_selected',
            'branches_setup',
            'kelas_template_applied',
            'subjects_template_applied',
            'jadwal_setup',
        ];

        foreach ($legacyFlags as $step) {
            if (!$progress->isStepComplete($step)) {
                $progress->completeStep($step);
            }
        }

        // Bypass live-data gating on purpose — this IS the escape hatch.
        $progress->update([
            'setup_status'        => 'siap_operasional',
            'progress_percentage' => 100,
            'completed_at'        => $progress->completed_at ?? now(),
        ]);
    }

    /**
     * Refresh and recalculate all progress for a tenant.
     * Single source of truth for setup_status, progress_percentage, and completed_at.
     */
    public static function refreshProgress(?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? tenant_id();

        if (!$tenantId) {
            return;
        }

        // First auto-update all steps based on current data
        self::autoUpdateSteps($tenantId);

        // Get progress after auto-update
        $progress = self::getProgress($tenantId);

        // Recalculate percentage and status (handled by recalculateProgress)
        $progress->recalculateProgress();

        // Update completed_at if siap_operasional
        if ($progress->setup_status === 'siap_operasional' && !$progress->completed_at) {
            $progress->update(['completed_at' => now()]);
        }
    }

    /**
     * Update step secara otomatis berdasarkan kondisi sistem
     * Bidirectional: can mark complete or uncomplete based on data existence
     */
    public static function autoUpdateSteps(?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? tenant_id();

        if (!$tenantId) {
            return;
        }

        $progress = self::getProgress($tenantId);
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return;
        }

        // Check kelas (kelas_template_applied only; branches_setup is legacy and excluded)
        $kelasCount = \App\Models\Kelas::where('tenant_id', $tenantId)->count();
        if ($kelasCount > 0) {
            if (!$progress->step_kelas_template_applied) {
                $progress->completeStep('kelas_template_applied');
            }
        } else {
            if ($progress->step_kelas_template_applied) {
                $progress->uncompleteStep('kelas_template_applied');
            }
        }

        // Check subjects
        $subjectsCount = \App\Models\Subject::where('tenant_id', $tenantId)->count();
        if ($subjectsCount > 0) {
            if (!$progress->step_subjects_template_applied) {
                $progress->completeStep('subjects_template_applied');
            }
        } else {
            if ($progress->step_subjects_template_applied) {
                $progress->uncompleteStep('subjects_template_applied');
            }
        }

        // Check ustadz
        $ustadzCount = \App\Models\Ustadz::where('tenant_id', $tenantId)->count();
        if ($ustadzCount > 0) {
            if (!$progress->step_first_ustadz_added) {
                $progress->completeStep('first_ustadz_added');
            }
        } else {
            if ($progress->step_first_ustadz_added) {
                $progress->uncompleteStep('first_ustadz_added');
            }
        }

        // Check santri
        $santriCount = \App\Models\Santri::where('tenant_id', $tenantId)->count();
        if ($santriCount > 0) {
            if (!$progress->step_first_santri_added) {
                $progress->completeStep('first_santri_added');
            }
        } else {
            if ($progress->step_first_santri_added) {
                $progress->uncompleteStep('first_santri_added');
            }
        }

        // Check jadwal
        $jadwalCount = \App\Models\Schedule::where('tenant_id', $tenantId)->count();
        if ($jadwalCount > 0) {
            if (!$progress->step_jadwal_setup) {
                $progress->completeStep('jadwal_setup');
            }
        } else {
            if ($progress->step_jadwal_setup) {
                $progress->uncompleteStep('jadwal_setup');
            }
        }

        $progress->recalculateProgress();
    }

    /**
     * Get setup progress for a SINGLE program (used by the multi-program
     * queue and the dashboard's per-program cards).
     *
     * Required steps and their order come from OnboardingStepRegistry
     * (config/onboarding.php) — not hardcoded here — so this stays in sync
     * automatically if the registry gains/loses required steps. This is the
     * single source of truth; do not duplicate this calculation elsewhere.
     */
    public static function getProgramProgress(int $programId, ?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? tenant_id();
        $programSlug = Program::find($programId)?->slug;

        $kelasCount     = \App\Models\Kelas::where('tenant_id', $tenantId)->where('program_id', $programId)->count();
        $subjectCount   = \App\Models\Subject::where('tenant_id', $tenantId)->where('program_id', $programId)->count();
        $ustadzCount    = \App\Models\Ustadz::where('tenant_id', $tenantId)->count();
        $penugasanCount = UstadzKelas::where('tenant_id', $tenantId)->where('program_id', $programId)->count();
        $scheduleCount  = \App\Models\Schedule::where('tenant_id', $tenantId)->where('program_id', $programId)->count();

        $checks = [
            'hasKelas'       => $kelasCount > 0,
            'hasSubject'     => $subjectCount > 0,
            'hasUstadz'      => $ustadzCount > 0,
            'hasUstadzKelas' => $penugasanCount > 0,
            'hasSchedule'    => $scheduleCount > 0,
        ];

        $requiredKeys = array_filter(array_column(OnboardingStepRegistry::requiredSteps($programSlug), 'progress_field'));

        $completedSteps = collect($requiredKeys)->filter(fn ($field) => $checks[$field] ?? false)->count();
        $percentage = $requiredKeys ? (int) round(($completedSteps / count($requiredKeys)) * 100) : 0;
        $isComplete = collect($requiredKeys)->every(fn ($field) => $checks[$field] ?? false);

        return [
            'kelas_count' => $kelasCount,
            'subject_count' => $subjectCount,
            'ustadz_count' => $ustadzCount,
            'penugasan_count' => $penugasanCount,
            'schedule_count' => $scheduleCount,
            'has_kelas' => $checks['hasKelas'],
            'has_subject' => $checks['hasSubject'],
            'has_ustadz' => $checks['hasUstadz'],
            'has_penugasan' => $checks['hasUstadzKelas'],
            'has_schedule' => $checks['hasSchedule'],
            'percentage' => $percentage,
            'is_complete' => $isComplete,
        ];
    }

    /**
     * Get actual setup progress based on live DB counts (no flag dependency).
     * Returns an array with boolean checks and percentage.
     * This is the single source of truth for dashboard progress cards AND
     * for gating/ordering the onboarding wizard (see OnboardingStepRegistry).
     *
     * Required dependency chain (see config/onboarding.php):
     *   Program → Kelas → Subject → Ustadz → Penugasan (UstadzKelas) → Jadwal
     * Santri remains a post-onboarding step.
     */
    public static function getActualProgress(?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? tenant_id();

        if (!$tenantId) {
            return self::emptyProgress();
        }

        $hasProgram     = \App\Models\TenantProgram::where('tenant_id', $tenantId)->where('is_active', true)->exists();
        $hasKelas       = \App\Models\Kelas::where('tenant_id', $tenantId)->exists();
        $hasSubject     = \App\Models\Subject::where('tenant_id', $tenantId)->exists();
        $hasUstadz      = \App\Models\Ustadz::where('tenant_id', $tenantId)->exists();
        $hasUstadzKelas = \App\Models\UstadzKelas::where('tenant_id', $tenantId)->exists();
        $hasSchedule    = \App\Models\Schedule::where('tenant_id', $tenantId)->exists();
        $hasSantri      = \App\Models\Santri::where('tenant_id', $tenantId)->exists();

        $checks = [
            'hasProgram'     => $hasProgram,
            'hasKelas'       => $hasKelas,
            'hasSubject'     => $hasSubject,
            'hasUstadz'      => $hasUstadz,
            'hasUstadzKelas' => $hasUstadzKelas,
            'hasSchedule'    => $hasSchedule,
            'hasSantri'      => $hasSantri,
        ];

        $completed  = count(array_filter($checks));
        $total      = count($checks);
        $percentage = (int) round(($completed / $total) * 100);

        $programSlug = self::getFirstActiveProgram($tenantId)?->slug;

        // Onboarding complete when: Program + every required wizard step is done
        $isComplete = $hasProgram && OnboardingStepRegistry::isFlowComplete($checks, $programSlug);

        $nextAction = self::resolveNextAction($checks, $programSlug);

        return array_merge($checks, [
            'percentage'    => $percentage,
            'completed'     => $completed,
            'total'         => $total,
            'is_complete'   => $isComplete,
            'next_action'   => $nextAction,
        ]);
    }

    /**
     * Resolve the next recommended action based on what's missing.
     * Order comes from OnboardingStepRegistry (config/onboarding.php), not
     * from a hardcoded sequence here.
     */
    private static function resolveNextAction(array $checks, ?string $programSlug): ?array
    {
        if (!$checks['hasProgram']) {
            return [
                'label'  => 'Pilih Program',
                'route'  => 'dashboard.onboarding.programs',
                'params' => [],
                'icon'   => 'fa-layer-group',
            ];
        }

        $nextKey = OnboardingStepRegistry::firstIncompleteKey($checks, $programSlug);

        if (!$nextKey) {
            // Onboarding complete - no more required actions
            return null;
        }

        $step = OnboardingStepRegistry::find($nextKey, $programSlug);

        return [
            'label'  => $step['title'] ?? $step['label'] ?? 'Lanjutkan Setup',
            'route'  => 'dashboard.onboarding.wizard',
            'params' => ['step' => $nextKey],
            'icon'   => $step['icon'] ?? 'fa-arrow-right',
        ];
    }

    /**
     * Empty progress for unauthenticated/missing tenant context.
     */
    private static function emptyProgress(): array
    {
        return [
            'hasProgram'     => false,
            'hasKelas'       => false,
            'hasSubject'     => false,
            'hasUstadz'      => false,
            'hasUstadzKelas' => false,
            'hasSchedule'    => false,
            'hasSantri'      => false,
            'percentage'     => 0,
            'completed'      => 0,
            'total'          => 7,
            'is_complete'    => false,
            'next_action'    => null,
        ];
    }
}
