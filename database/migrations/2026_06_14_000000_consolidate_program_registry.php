<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Consolidate Program Registry — Phase 1F Prerequisite
 *
 * Changes:
 * 1. Ensure canonical program exists: pesantren-quran-tahfidz
 * 2. Migrate all FK references from legacy slugs to canonical:
 *    - tahfidz        → pesantren-quran-tahfidz
 *    - rumah-tahfidz  → pesantren-quran-tahfidz
 *    - pondok-quran   → pesantren-quran-tahfidz
 * 3. Remove duplicate tenant_program/santri_program pivot rows after migration
 * 4. Deactivate legacy programs (is_active = false) — never hard-delete
 *
 * Affected tables (program_id FK):
 *   programs, tenant_programs, santri_programs, ustadz_kelas,
 *   kelas, subjects, schedules (jadwal), nilai, absensi, materi,
 *   elearning, raport
 *
 * This migration is idempotent: safe to run multiple times.
 */
return new class extends Migration
{
    /**
     * Legacy slugs that are consolidated into the canonical program.
     */
    private const LEGACY_TAHFIDZ_SLUGS = ['tahfidz', 'rumah-tahfidz', 'pondok-quran'];

    private const CANONICAL_SLUG = 'pesantren-quran-tahfidz';

    private const MAHASISWA_SLUG = 'mahasiswa';

    /**
     * Tables that have a direct program_id foreign key to programs.id
     * (order matters for FK safety — no explicit order needed since we
     *  reassign before deleting the parent row)
     */
    private const PROGRAM_FK_TABLES = [
        'santri_programs',
        'ustadz_kelas',
        'kelas',
        'subjects',
        'schedules',
        'nilai',
        'absensi',
        'materi',
        'elearning',
        'raport',
    ];

    public function up(): void
    {
        // =====================================================================
        // 1. Ensure canonical program row exists
        // =====================================================================
        $canonical = DB::table('programs')->where('slug', self::CANONICAL_SLUG)->first();

        if (!$canonical) {
            DB::table('programs')->insert([
                'slug'        => self::CANONICAL_SLUG,
                'name'        => 'Pesantren Quran & Tahfidz',
                'description' => 'Program pondok yang berfokus pada ilmu Al-Quran, tahfidz, tajwid, qiraah, dan tafsir dengan target capaian hafalan terstruktur.',
                'is_active'   => true,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $canonical = DB::table('programs')->where('slug', self::CANONICAL_SLUG)->first();
        }

        $canonicalId = $canonical->id;

        // =====================================================================
        // 2. Migrate each legacy tahfidz program → canonical
        // =====================================================================
        foreach (self::LEGACY_TAHFIDZ_SLUGS as $legacySlug) {
            $legacy = DB::table('programs')->where('slug', $legacySlug)->first();

            if (!$legacy) {
                continue;
            }

            $legacyId = $legacy->id;

            // Reassign all FK references to canonical program
            foreach (self::PROGRAM_FK_TABLES as $table) {
                if (!$this->tableHasColumn($table, 'program_id')) {
                    continue;
                }

                // For tables with unique constraints (santri_programs) we must
                // avoid creating duplicate rows. Reassign where no conflict exists,
                // then delete orphaned rows that would violate the constraint.
                if ($table === 'santri_programs') {
                    $this->migrateSantriPrograms($legacyId, $canonicalId);
                } else {
                    DB::table($table)
                        ->where('program_id', $legacyId)
                        ->update(['program_id' => $canonicalId]);
                }
            }

            // Migrate tenant_programs pivot (no program_id column — uses program FK as pivot)
            $this->migrateTenantPrograms($legacyId, $canonicalId);

            // Deactivate legacy program — never hard-delete for referential integrity and auditability
            DB::table('programs')
                ->where('id', $legacyId)
                ->update(['is_active' => false, 'updated_at' => now()]);
        }

        // =====================================================================
        // 3. Deactivate mahasiswa program
        // =====================================================================
        $mahasiswa = DB::table('programs')->where('slug', self::MAHASISWA_SLUG)->first();

        if ($mahasiswa) {
            DB::table('programs')
                ->where('id', $mahasiswa->id)
                ->update(['is_active' => false, 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        // Reversal not supported: data from multiple programs has been merged.
        // In an emergency, restore from a database backup taken before this migration.
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    private function tableHasColumn(string $table, string $column): bool
    {
        $result = DB::select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
        ", [$table, $column]);

        return !empty($result);
    }

    private function tableExists(string $table): bool
    {
        $result = DB::select("
            SELECT TABLE_NAME
            FROM INFORMATION_SCHEMA.TABLES
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
        ", [$table]);

        return !empty($result);
    }

    /**
     * Migrate santri_programs rows from legacyId to canonicalId.
     * Unique constraint: (tenant_id, santri_id, program_id).
     * If a santri is already enrolled in canonical, skip (avoid duplicate).
     */
    private function migrateSantriPrograms(int $legacyId, int $canonicalId): void
    {
        if (!$this->tableExists('santri_programs')) {
            return;
        }

        // Get all rows referencing legacy program
        $legacyRows = DB::table('santri_programs')
            ->where('program_id', $legacyId)
            ->get();

        foreach ($legacyRows as $row) {
            // Check if santri already enrolled in canonical program for same tenant
            $alreadyExists = DB::table('santri_programs')
                ->where('tenant_id', $row->tenant_id)
                ->where('santri_id', $row->santri_id)
                ->where('program_id', $canonicalId)
                ->exists();

            if ($alreadyExists) {
                // Already covered by canonical enrollment — remove legacy row
                DB::table('santri_programs')
                    ->where('tenant_id', $row->tenant_id)
                    ->where('santri_id', $row->santri_id)
                    ->where('program_id', $legacyId)
                    ->delete();
            } else {
                // Reassign to canonical
                DB::table('santri_programs')
                    ->where('tenant_id', $row->tenant_id)
                    ->where('santri_id', $row->santri_id)
                    ->where('program_id', $legacyId)
                    ->update(['program_id' => $canonicalId]);
            }
        }
    }

    /**
     * Migrate tenant_programs pivot rows from legacyId to canonicalId.
     * If tenant already has canonical, remove the legacy pivot row.
     */
    private function migrateTenantPrograms(int $legacyId, int $canonicalId): void
    {
        if (!$this->tableExists('tenant_programs')) {
            return;
        }

        $legacyPivots = DB::table('tenant_programs')
            ->where('program_id', $legacyId)
            ->get();

        foreach ($legacyPivots as $pivot) {
            $alreadyExists = DB::table('tenant_programs')
                ->where('tenant_id', $pivot->tenant_id)
                ->where('program_id', $canonicalId)
                ->exists();

            if ($alreadyExists) {
                // Tenant already has canonical — remove duplicate legacy pivot
                DB::table('tenant_programs')
                    ->where('tenant_id', $pivot->tenant_id)
                    ->where('program_id', $legacyId)
                    ->delete();
            } else {
                // Reassign pivot to canonical
                DB::table('tenant_programs')
                    ->where('tenant_id', $pivot->tenant_id)
                    ->where('program_id', $legacyId)
                    ->update(['program_id' => $canonicalId]);
            }
        }
    }

};
