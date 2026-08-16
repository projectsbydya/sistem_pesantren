<?php

namespace App\Services;

use App\Models\Kelas;
use App\Models\Santri;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\TenantProgram;
use App\Models\Ustadz;
use App\Models\UstadzKelas;

/**
 * Feature Dependency Validation Service — Phase 1E FREEZE
 *
 * Validates dependencies for UNIVERSAL modules only:
 * Program → Kelas → Subject → UstadzKelas → Jadwal
 *
 * Ustadz and Santri are POST-onboarding steps and do NOT block access.
 *
 * DO NOT add dependency rules for Program Feature Pack modules
 * (Hafalan, Placement Test, Speaking Assessment, etc.).
 * Those belong in Phase 1F ProgramFeaturePackService.
 *
 * Returns warnings and CTA actions instead of blocking.
 */
class FeatureDependencyService
{
    /**
     * Check if tenant has selected programs
     */
    public static function hasPrograms(?int $tenantId = null): bool
    {
        return TenantSetupService::hasSelectedPrograms($tenantId);
    }

    /**
     * Check if tenant has any kelas
     */
    public static function hasKelas(?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? tenant_id();
        return Kelas::where('tenant_id', $tenantId)->exists();
    }

    /**
     * Check if tenant has any subjects
     */
    public static function hasSubjects(?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? tenant_id();
        return Subject::where('tenant_id', $tenantId)->exists();
    }

    /**
     * Check if tenant has any ustadz
     */
    public static function hasUstadz(?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? tenant_id();
        return Ustadz::where('tenant_id', $tenantId)->exists();
    }

    /**
     * Check if tenant has any santri
     */
    public static function hasSantri(?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? tenant_id();
        return Santri::where('tenant_id', $tenantId)->exists();
    }

    /**
     * Check if tenant has any schedules
     */
    public static function hasSchedules(?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? tenant_id();
        return Schedule::where('tenant_id', $tenantId)->exists();
    }

    /**
     * Check if tenant has any ustadz_kelas assignments
     */
    public static function hasUstadzKelas(?int $tenantId = null): bool
    {
        $tenantId = $tenantId ?? tenant_id();
        return UstadzKelas::where('tenant_id', $tenantId)->exists();
    }

    /**
     * Get dependency status for Create Subject feature
     */
    public static function validateCreateSubject(?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? tenant_id();
        $firstProgram = TenantSetupService::getFirstActiveProgram($tenantId);

        if (!$firstProgram) {
            return [
                'can_access' => false,
                'warning' => 'Belum dapat membuat mata pelajaran.',
                'message' => 'Silakan pilih program terlebih dahulu.',
                'cta_text' => 'Pilih Program',
                'cta_route' => 'dashboard.onboarding.programs',
                'missing' => ['program'],
            ];
        }

        return [
            'can_access' => true,
            'program_slug' => $firstProgram->slug,
        ];
    }

    /**
     * Get dependency status for Create Schedule feature
     */
    public static function validateCreateSchedule(?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? tenant_id();
        $missing = [];
        $firstProgram = TenantSetupService::getFirstActiveProgram($tenantId);

        if (!$firstProgram) {
            return [
                'can_access' => false,
                'warning' => 'Belum dapat membuat jadwal.',
                'message' => 'Silakan pilih program terlebih dahulu.',
                'cta_text' => 'Pilih Program',
                'cta_route' => 'dashboard.onboarding.programs',
                'missing' => ['program'],
            ];
        }

        if (!self::hasKelas($tenantId)) {
            $missing[] = 'kelas';
        }

        if (!self::hasSubjects($tenantId)) {
            $missing[] = 'subject';
        }

        // A Teacher must exist before a Teaching Assignment can — enforce the
        // same dependency chain used by the onboarding wizard/registry.
        if (!self::hasUstadz($tenantId)) {
            $missing[] = 'ustadz';
        }

        if (!self::hasUstadzKelas($tenantId)) {
            $missing[] = 'ustadz_kelas';
        }

        if (!empty($missing)) {
            $messages = [
                'kelas' => 'buat kelas',
                'subject' => 'buat mata pelajaran',
                'ustadz' => 'tambah ustadz',
                'ustadz_kelas' => 'buat penugasan mengajar',
            ];

            $missingActions = array_map(fn($m) => $messages[$m] ?? $m, $missing);

            return [
                'can_access' => false,
                'warning' => 'Belum dapat membuat jadwal.',
                'message' => 'Silakan ' . implode(' dan ', $missingActions) . ' terlebih dahulu.',
                'cta_text' => match($missing[0]) {
                    'kelas' => 'Buat Kelas',
                    'subject' => 'Buat Mata Pelajaran',
                    'ustadz' => 'Tambah Ustadz',
                    'ustadz_kelas' => 'Buat Penugasan Mengajar',
                    default => 'Lanjutkan Setup',
                },
                'cta_route' => match($missing[0]) {
                    'kelas' => 'dashboard.akademik.kelas.create',
                    'subject' => 'dashboard.akademik.subjects.create',
                    'ustadz' => 'dashboard.ustadz.create',
                    'ustadz_kelas' => 'dashboard.akademik.penugasan.create',
                    default => 'dashboard.onboarding.wizard',
                },
                'cta_params' => $missing[0] === 'ustadz' ? [] : ['programSlug' => $firstProgram->slug],
                'missing' => $missing,
                'program_slug' => $firstProgram->slug,
            ];
        }

        return [
            'can_access' => true,
            'program_slug' => $firstProgram->slug,
        ];
    }

    /**
     * Get dependency status for Input Nilai feature
     */
    public static function validateInputNilai(?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? tenant_id();
        $missing = [];
        $firstProgram = TenantSetupService::getFirstActiveProgram($tenantId);

        if (!$firstProgram) {
            $missing[] = 'program';
        }

        if (!self::hasKelas($tenantId)) {
            $missing[] = 'kelas';
        }

        if (!self::hasSubjects($tenantId)) {
            $missing[] = 'subject';
        }

        if (!self::hasSantri($tenantId)) {
            $missing[] = 'santri';
        }

        if (!self::hasSchedules($tenantId)) {
            $missing[] = 'jadwal';
        }

        if (!empty($missing)) {
            $messages = [
                'program' => 'pilih program',
                'kelas' => 'buat kelas',
                'subject' => 'buat mata pelajaran',
                'santri' => 'tambah santri',
                'jadwal' => 'buat jadwal',
            ];

            $missingActions = array_map(fn($m) => $messages[$m] ?? $m, $missing);

            return [
                'can_access' => false,
                'warning' => 'Belum dapat menginput nilai.',
                'message' => 'Silakan ' . implode(', ', $missingActions) . ' terlebih dahulu.',
                'cta_text' => self::getCtaTextForMissing($missing[0]),
                'cta_route' => self::getCtaRouteForMissing($missing[0], $firstProgram?->slug),
                'cta_params' => $firstProgram ? ['programSlug' => $firstProgram->slug] : [],
                'missing' => $missing,
            ];
        }

        return [
            'can_access' => true,
            'program_slug' => $firstProgram->slug,
        ];
    }

    /**
     * Get dependency status for Input Absensi feature
     */
    public static function validateInputAbsensi(?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? tenant_id();
        $missing = [];
        $firstProgram = TenantSetupService::getFirstActiveProgram($tenantId);

        if (!$firstProgram) {
            $missing[] = 'program';
        }

        if (!self::hasKelas($tenantId)) {
            $missing[] = 'kelas';
        }

        if (!self::hasSantri($tenantId)) {
            $missing[] = 'santri';
        }

        if (!self::hasSchedules($tenantId)) {
            $missing[] = 'jadwal';
        }

        if (!empty($missing)) {
            $messages = [
                'program' => 'pilih program',
                'kelas' => 'buat kelas',
                'santri' => 'tambah santri',
                'jadwal' => 'buat jadwal',
            ];

            $missingActions = array_map(fn($m) => $messages[$m] ?? $m, $missing);

            return [
                'can_access' => false,
                'warning' => 'Belum dapat menginput absensi.',
                'message' => 'Silakan ' . implode(', ', $missingActions) . ' terlebih dahulu.',
                'cta_text' => self::getCtaTextForMissing($missing[0]),
                'cta_route' => self::getCtaRouteForMissing($missing[0], $firstProgram?->slug),
                'cta_params' => $firstProgram ? ['programSlug' => $firstProgram->slug] : [],
                'missing' => $missing,
            ];
        }

        return [
            'can_access' => true,
            'program_slug' => $firstProgram->slug,
        ];
    }

    /**
     * Get all dependency status for dashboard display
     */
    public static function getAllDependencies(?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? tenant_id();

        return [
            'program' => self::hasPrograms($tenantId),
            'kelas' => self::hasKelas($tenantId),
            'subject' => self::hasSubjects($tenantId),
            'ustadz' => self::hasUstadz($tenantId),
            'ustadz_kelas' => self::hasUstadzKelas($tenantId),
            'santri' => self::hasSantri($tenantId),
            'jadwal' => self::hasSchedules($tenantId),
        ];
    }

    /**
     * Get missing dependencies list
     */
    public static function getMissingDependencies(?int $tenantId = null): array
    {
        $all = self::getAllDependencies($tenantId);
        return array_keys(array_filter($all, fn($v) => !$v));
    }

    private static function getCtaTextForMissing(string $missing): string
    {
        return match($missing) {
            'program' => 'Pilih Program',
            'kelas' => 'Buat Kelas',
            'subject' => 'Buat Mata Pelajaran',
            'santri' => 'Tambah Santri',
            'ustadz' => 'Tambah Ustadz',
            'ustadz_kelas' => 'Buat Penugasan Mengajar',
            'jadwal' => 'Buat Jadwal',
            default => 'Lanjutkan Setup',
        };
    }

    private static function getCtaRouteForMissing(string $missing, ?string $programSlug): string
    {
        return match($missing) {
            'program' => 'dashboard.onboarding.programs',
            'kelas' => 'dashboard.onboarding.wizard',
            'subject' => 'dashboard.onboarding.wizard',
            'jadwal' => 'dashboard.onboarding.wizard',
            'ustadz_kelas' => 'dashboard.akademik.penugasan.create',
            'santri' => 'dashboard.santri.create',
            'ustadz' => 'dashboard.ustadz.create',
            default => 'dashboard.onboarding.setup-guide',
        };
    }

    /**
     * Get CTA params for missing dependencies.
     * For onboarding wizard steps, adds the step parameter.
     */
    public static function getCtaParamsForMissing(string $missing): array
    {
        return match($missing) {
            'kelas' => ['step' => 'kelas'],
            'subject' => ['step' => 'mapel'],
            'jadwal' => ['step' => 'jadwal'],
            default => [],
        };
    }
}
