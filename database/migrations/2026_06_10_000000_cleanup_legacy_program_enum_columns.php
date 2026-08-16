<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cleanup: Remove legacy 'program' enum columns from santri_programs and ustadz_kelas.
 * 
 * PRODUCTION SCHEMA VERIFIED:
 * - santri_programs: UNIQUE INDEX on (tenant_id, santri_id, program)
 * - ustadz_kelas: INDEXES on (tenant_id, program), named lookup indexes
 * 
 * PREREQUISITES:
 * - Models use program_id (FK) as sole source of truth
 * - No code references legacy 'program' enum column
 * - Zero records use legacy column values
 * - FK constraints properly configured on program_id
 * 
 * This migration is idempotent and production-safe.
 */
return new class extends Migration
{
    /**
     * Run the migration.
     * 
     * HANDLES ALL PRODUCTION CONDITIONS:
     * - program_id may or may not exist
     * - program enum may or may not exist  
     * - Data may or may not be migrated
     * - Indexes may be in any state
     */
    public function up(): void
    {
        // =================================================================
        // TABLE 1: santri_programs
        // =================================================================

        // Step 0: Ensure program_id column exists (add if missing)
        if (!Schema::hasColumn('santri_programs', 'program_id')) {
            Schema::table('santri_programs', function (Blueprint $table) {
                $table->foreignId('program_id')
                    ->nullable()
                    ->after('santri_id')
                    ->constrained('programs')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('santri_programs', 'program')) {
            // Migrate any unsynced data before dropping column
            $programMap = DB::table('programs')
                ->pluck('id', 'slug')
                ->toArray();

            foreach ($programMap as $slug => $programId) {
                DB::table('santri_programs')
                    ->where('program', $slug)
                    ->whereNull('program_id')
                    ->update(['program_id' => $programId]);
            }

            // Step 1: Create new unique constraint FIRST
            // Must exist before dropping old one to maintain constraint coverage
            // PRODUCTION: new index = (tenant_id, santri_id, program_id)
            $newUniqueExists = DB::select("
                SELECT INDEX_NAME
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'santri_programs'
                AND INDEX_NAME = 'santri_programs_tenant_id_santri_id_program_id_unique'
            ");

            if (empty($newUniqueExists)) {
                Schema::table('santri_programs', function (Blueprint $table) {
                    $table->unique(['tenant_id', 'santri_id', 'program_id']);
                });
            }

            // Step 2: Drop old unique constraint AFTER new one is in place
            // PRODUCTION INDEX NAME: santri_programs_tenant_id_santri_id_program_unique
            $oldUniqueExists = DB::select("
                SELECT INDEX_NAME
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'santri_programs'
                AND INDEX_NAME = 'santri_programs_tenant_id_santri_id_program_unique'
            ");

            if (!empty($oldUniqueExists)) {
                DB::statement('ALTER TABLE santri_programs DROP INDEX santri_programs_tenant_id_santri_id_program_unique');
            }

            // Step 3: Drop legacy program column
            Schema::table('santri_programs', function (Blueprint $table) {
                $table->dropColumn('program');
            });
        }

        // Step 4: Ensure program_id is NOT NULL
        // PRODUCTION FK: santri_programs_program_id_foreign ON DELETE SET NULL
        // MySQL error 1830: cannot set NOT NULL while FK action is SET NULL
        // Fix: DROP FK → MODIFY NOT NULL → RECREATE FK with ON DELETE RESTRICT
        $columnInfo = DB::select("
            SELECT IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'santri_programs'
            AND COLUMN_NAME = 'program_id'
        ");

        if (!empty($columnInfo) && $columnInfo[0]->IS_NULLABLE === 'YES') {
            // Drop FK with SET NULL action (incompatible with NOT NULL column)
            DB::statement('ALTER TABLE santri_programs DROP FOREIGN KEY santri_programs_program_id_foreign');
            // Make column NOT NULL
            DB::statement('ALTER TABLE santri_programs MODIFY COLUMN program_id BIGINT UNSIGNED NOT NULL');
            // Recreate FK with RESTRICT (program cannot be deleted while enrollments exist)
            DB::statement('ALTER TABLE santri_programs ADD CONSTRAINT santri_programs_program_id_foreign FOREIGN KEY (program_id) REFERENCES programs (id) ON DELETE RESTRICT');
        }

        // =================================================================
        // TABLE 2: ustadz_kelas
        // =================================================================

        // Step 0: Ensure program_id column exists (add if missing)
        if (!Schema::hasColumn('ustadz_kelas', 'program_id')) {
            Schema::table('ustadz_kelas', function (Blueprint $table) {
                $table->foreignId('program_id')
                    ->nullable()
                    ->after('kelas_id')
                    ->constrained('programs')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasColumn('ustadz_kelas', 'program')) {
            // Migrate any unsynced data before dropping column
            $programMap = DB::table('programs')
                ->pluck('id', 'slug')
                ->toArray();

            foreach ($programMap as $slug => $programId) {
                DB::table('ustadz_kelas')
                    ->where('program', $slug)
                    ->whereNull('program_id')
                    ->update(['program_id' => $programId]);
            }

            // Step 1: Create new indexes on program_id FIRST
            // CRITICAL: ustadz_kelas_subject_lookup starts with tenant_id and is the ONLY
            // index covering tenant_id FK. MySQL (error 1553) will refuse to drop it unless
            // another index starting with tenant_id already exists.
            // Creating (tenant_id, program_id) and ustadz_kelas_lookup first provides
            // MySQL with an alternative FK support index before we drop the old one.
            $indexesToCreate = [
                'ustadz_kelas_tenant_id_program_id_index' => '(tenant_id, program_id)',
                'ustadz_kelas_lookup'                     => '(tenant_id, ustadz_id, kelas_id, program_id)',
            ];

            foreach ($indexesToCreate as $indexName => $columns) {
                $exists = DB::select("
                    SELECT INDEX_NAME
                    FROM INFORMATION_SCHEMA.STATISTICS
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'ustadz_kelas'
                    AND INDEX_NAME = ?
                ", [$indexName]);

                if (empty($exists)) {
                    DB::statement("CREATE INDEX `{$indexName}` ON ustadz_kelas{$columns}");
                }
            }

            // Step 2: Now safe to drop old indexes referencing 'program' column
            // PRODUCTION INDEXES:
            // - ustadz_kelas_tenant_id_program_index  (tenant_id, program) — may not exist
            // - ustadz_kelas_subject_lookup            (tenant_id, ustadz_id, kelas_id, program, subject_id)
            // NOTE: ustadz_kelas_lookup is NOT in this list — we just created it on program_id
            $indexesToDrop = [
                'ustadz_kelas_tenant_id_program_index',
                'ustadz_kelas_subject_lookup',
            ];

            foreach ($indexesToDrop as $indexName) {
                $exists = DB::select("
                    SELECT INDEX_NAME
                    FROM INFORMATION_SCHEMA.STATISTICS
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'ustadz_kelas'
                    AND INDEX_NAME = ?
                ", [$indexName]);

                if (!empty($exists)) {
                    DB::statement("ALTER TABLE ustadz_kelas DROP INDEX `{$indexName}`");
                }
            }

            // Step 3: Drop legacy program column
            Schema::table('ustadz_kelas', function (Blueprint $table) {
                $table->dropColumn('program');
            });

            // Step 4: Create subject_lookup AFTER program column is dropped
            // (same name as old index — must not exist before creation)
            $exists = DB::select("
                SELECT INDEX_NAME
                FROM INFORMATION_SCHEMA.STATISTICS
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'ustadz_kelas'
                AND INDEX_NAME = 'ustadz_kelas_subject_lookup'
            ");

            if (empty($exists)) {
                DB::statement('CREATE INDEX ustadz_kelas_subject_lookup ON ustadz_kelas(tenant_id, ustadz_id, kelas_id, program_id, subject_id)');
            }
        }

        // Step 4: Ensure program_id is NOT NULL
        // PRODUCTION FK: ustadz_kelas_program_id_foreign ON DELETE CASCADE
        // CASCADE is compatible with NOT NULL — no FK recreation needed
        $columnInfo = DB::select("
            SELECT IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'ustadz_kelas'
            AND COLUMN_NAME = 'program_id'
        ");

        if (!empty($columnInfo) && $columnInfo[0]->IS_NULLABLE === 'YES') {
            DB::statement('ALTER TABLE ustadz_kelas MODIFY COLUMN program_id BIGINT UNSIGNED NOT NULL');
        }
    }

    /**
     * Reverse the migration.
     * 
     * NOTE: Reversing would require restoring the enum columns, which could lead
     * to data integrity issues. This migration is designed to be one-way.
     * In case of emergency, a new migration should be created rather than rolling back.
     */
    public function down(): void
    {
        // This is a cleanup migration - no down() implemented by design.
        // The program enum column was legacy and has been replaced by program_id FK.
        // 
        // Rollback not supported because:
        // 1. The enum column would need to be recreated
        // 2. Data would need to be migrated back from program_id
        // 3. This creates risk of data loss/corruption
        //
        // If emergency rollback is needed, create a new forward migration instead.
    }
};
