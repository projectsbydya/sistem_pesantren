<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Add assessment_type to the nilai table.
 *
 * Design decisions:
 * - VARCHAR(20), not ENUM — adding new types never requires DDL in production.
 * - Default 'harian' backfills all existing rows correctly.
 * - The old nilai_unique(tenant_id, santri_id, tanggal) was too narrow:
 *   it allowed at most one record per santri per day, regardless of subject.
 *   That was a pre-existing bug. The new constraint is the correct one:
 *   (tenant_id, santri_id, subject_id, kelas_id, program_id, tanggal, assessment_type)
 * - subject_id and kelas_id are NOT NULL in the current schema (verified: 0 NULLs),
 *   so the unique index is safe without COALESCE workarounds.
 * - MySQL raw SQL is used for the old constraint drop because Blueprint::dropUnique
 *   may silently fail when the FK backs the same index name.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Step 1: Add assessment_type column ──────────────────────────────
        Schema::table('nilai', function (Blueprint $table) {
            $table->string('assessment_type', 20)
                ->notNull()
                ->default('harian')
                ->after('tanggal');
        });

        // ── Step 2: Backfill existing rows (already covered by column default,
        //            but explicit UPDATE ensures data consistency on any engine)
        DB::table('nilai')
            ->whereNull('assessment_type')
            ->orWhere('assessment_type', '')
            ->update(['assessment_type' => 'harian']);

        // ── Step 3: Drop old constraint & recreate correctly ─────────────────
        if (DB::getDriverName() === 'mysql') {
            // Check which index names exist before attempting drops
            $existingIndexes = collect(DB::select("SHOW INDEX FROM `nilai`"))
                ->pluck('Key_name')
                ->unique()
                ->values()
                ->toArray();

            $dropOld  = in_array('nilai_unique', $existingIndexes)
                ? 'DROP INDEX `nilai_unique`,'
                : '';

            // Single ALTER TABLE: drop old + add new atomically
            DB::statement("
                ALTER TABLE `nilai`
                {$dropOld}
                ADD CONSTRAINT `nilai_unique`
                UNIQUE (tenant_id, santri_id, subject_id, kelas_id, program_id, tanggal, assessment_type)
            ");
        } else {
            // SQLite path (test environment): drop old, add new
            Schema::table('nilai', function (Blueprint $table) {
                try {
                    $table->dropUnique('nilai_unique');
                } catch (\Exception $e) {
                    // May not exist under this name in SQLite
                }
                $table->unique(
                    ['tenant_id', 'santri_id', 'subject_id', 'kelas_id', 'program_id', 'tanggal', 'assessment_type'],
                    'nilai_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // Restore old (narrow) constraint — only safe when no multi-type data exists
            try {
                DB::statement("ALTER TABLE `nilai` DROP INDEX `nilai_unique`");
            } catch (\Exception $e) {
                // May not exist
            }

            DB::statement("
                ALTER TABLE `nilai`
                ADD CONSTRAINT `nilai_unique`
                UNIQUE (tenant_id, santri_id, tanggal)
            ");
        }

        Schema::table('nilai', function (Blueprint $table) {
            $table->dropColumn('assessment_type');
        });
    }
};
