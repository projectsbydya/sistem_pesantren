<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop duplicate tables if they exist
        $duplicateTables = [
            'absensi_diniyah',
            'absensi_pesantren', 
            'nilai_diniyah',
            'nilai_pesantren',
            'elearning_diniyah',
            'elearning_pesantren'
        ];

        foreach ($duplicateTables as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }
        }

        // Drop old program type columns if they exist.
        //
        // IMPORTANT — MySQL error 1553:
        // Tables like `nilai` and `elearning` have a composite index (tenant_id, type).
        // MySQL uses that composite index as the backing index for the tenant_id FK.
        // You CANNOT drop the index while the FK still references it.
        // Fix: drop FK first (raw SQL), drop index, drop column, recreate FK.
        //
        // For tables that only have a plain ->index(['type']), no FK issue exists.

        $this->dropTypeColumn('nilai');
        $this->dropTypeColumn('elearning');

        // These tables either have no composite (tenant_id, type) index or no type column
        $simpleTables = ['kelas', 'subjects', 'jadwal', 'absensi_santri', 'ustadz_kelas'];
        foreach ($simpleTables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'type')) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $blueprint) use ($tableName) {
                foreach ([
                    $tableName . '_type_index',
                    $tableName . '_tenant_id_type_index',
                ] as $idx) {
                    if (Schema::hasIndex($tableName, $idx)) {
                        $blueprint->dropIndex($idx);
                    }
                }
                $blueprint->dropColumn('type');
            });
        }

        // Drop program_type column (no FK dependency expected)
        foreach (['kelas', 'subjects', 'jadwal', 'nilai', 'absensi_santri', 'elearning', 'ustadz_kelas'] as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'program_type')) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $blueprint) use ($tableName) {
                $idx = $tableName . '_program_type_index';
                if (Schema::hasIndex($tableName, $idx)) {
                    $blueprint->dropIndex($idx);
                }
                $blueprint->dropColumn('program_type');
            });
        }

        // Note: santri_programs table is kept as it's still needed for santri-program relationships
    }

    /**
     * Drop the `type` column from a table that has a composite (tenant_id, type) index.
     *
     * MySQL error 1553: the composite index is the backing index for the tenant_id FK.
     * Order: drop FK → drop composite index → drop column → recreate FK.
     * Raw SQL is used because Blueprint::dropForeign() may silently fail.
     */
    private function dropTypeColumn(string $tableName): void
    {
        if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'type')) {
            return;
        }

        $compositeIndex = $tableName . '_tenant_id_type_index';
        $plainIndex       = $tableName . '_type_index';
        $fkName           = $tableName . '_tenant_id_foreign';

        // Step 1: Drop tenant_id FK that backs the composite index (raw SQL untuk reliability)
        if (DB::getDriverName() === 'mysql') {
            try {
                DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$fkName}`");
            } catch (\Exception $e) {
                // FK may have already been dropped or named differently — proceed
            }

            // Step 2 & 3: Drop indexes dan column menggunakan raw SQL untuk atomicity
            try {
                if (Schema::hasIndex($tableName, $compositeIndex)) {
                    DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$compositeIndex}`");
                }
            } catch (\Exception $e) {
                // Index may not exist
            }

            try {
                if (Schema::hasIndex($tableName, $plainIndex)) {
                    DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$plainIndex}`");
                }
            } catch (\Exception $e) {
                // Index may not exist
            }

            // Drop type column
            try {
                DB::statement("ALTER TABLE `{$tableName}` DROP COLUMN `type`");
            } catch (\Exception $e) {
                // Column may have already been dropped
            }

            // Step 4: Recreate tenant_id FK
            try {
                DB::statement("ALTER TABLE `{$tableName}` ADD CONSTRAINT `{$fkName}` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE");
            } catch (\Exception $e) {
                // FK may already exist if it was never dropped
            }
        }
    }

    public function down(): void
    {
        // This migration is destructive — rollback would require recreating tables.
        // Ensure backups exist before running this migration in production.
    }
};
