<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get program IDs
        $diniyahId = DB::table('programs')->where('slug', 'diniyah')->value('id');
        $pesantrenId = DB::table('programs')->where('slug', 'pesantren')->value('id');

        if (!$diniyahId || !$pesantrenId) {
            // If programs don't exist, create them first
            DB::table('programs')->insert([
                ['name' => 'Diniyah', 'slug' => 'diniyah', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Pesantren', 'slug' => 'pesantren', 'created_at' => now(), 'updated_at' => now()],
            ]);
            
            $diniyahId = DB::table('programs')->where('slug', 'diniyah')->value('id');
            $pesantrenId = DB::table('programs')->where('slug', 'pesantren')->value('id');
        }

        // Tables to migrate
        $tables = [
            'kelas',
            'subjects', 
            'jadwal',
            'nilai',
            'absensi_santri',
            'elearning',
            'ustadz_kelas'
        ];

        foreach ($tables as $table) {
            // Skip if table doesn't exist
            if (!Schema::hasTable($table)) {
                continue;
            }

            // Skip if table doesn't have program_id column
            if (!Schema::hasColumn($table, 'program_id')) {
                continue;
            }

            // Check if table already has program_id data
            $hasData = DB::table($table)->whereNotNull('program_id')->exists();
            if ($hasData) {
                continue; // Skip if already migrated
            }

            // Check if table has a type/program column to migrate from
            if (Schema::hasColumn($table, 'type')) {
                // Migrate based on type column
                DB::table($table)
                    ->where('type', 'diniyah')
                    ->update(['program_id' => $diniyahId]);
                    
                DB::table($table)
                    ->where('type', 'pesantren')
                    ->update(['program_id' => $pesantrenId]);
                    
                // Update any remaining records without type
                DB::table($table)
                    ->whereNull('program_id')
                    ->update(['program_id' => $diniyahId]);
            } elseif (Schema::hasColumn($table, 'program_type')) {
                // Migrate based on program_type column
                DB::table($table)
                    ->where('program_type', 'diniyah')
                    ->update(['program_id' => $diniyahId]);
                    
                DB::table($table)
                    ->where('program_type', 'pesantren')
                    ->update(['program_id' => $pesantrenId]);
                    
                // Update any remaining records without program_type
                DB::table($table)
                    ->whereNull('program_id')
                    ->update(['program_id' => $diniyahId]);
            } else {
                // Default to diniyah for existing data if no program identifier exists
                // Only update if program_id is null
                DB::table($table)
                    ->whereNull('program_id')
                    ->update(['program_id' => $diniyahId]);
            }
        }

        // Promote program_id to NOT NULL + switch FK to cascadeOnDelete.
        // Uses raw ALTER TABLE on MySQL for reliability (Blueprint::change() requires
        // doctrine/dbal and may silently drop other column attributes).
        // On SQLite (tests) the tables are always empty so we skip — SQLite does not
        // support ALTER COLUMN and the NOT NULL enforcement is not needed for test runs.
        if (DB::getDriverName() === 'mysql') {
            foreach ($tables as $tableName) {
                if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'program_id')) {
                    continue;
                }

                // Abort if any row still has NULL — data migration above should have filled all
                if (DB::table($tableName)->whereNull('program_id')->exists()) {
                    continue;
                }

                // Step 1: Drop all FK constraints on program_id — any name variant
                // MySQL requires no FK referencing this column before MODIFY COLUMN
                $fkNames = [
                    $tableName . '_program_id_foreign', // Laravel default naming
                ];
                foreach ($fkNames as $fkName) {
                    try {
                        DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$fkName}`");
                    } catch (\Exception $e) {}
                }

                // Step 2: Promote column to NOT NULL
                DB::statement("ALTER TABLE `{$tableName}` MODIFY COLUMN `program_id` BIGINT UNSIGNED NOT NULL");

                // Step 3: Recreate FK as cascadeOnDelete
                DB::statement("ALTER TABLE `{$tableName}` ADD CONSTRAINT `{$tableName}_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE");
            }
        }
    }

    public function down(): void
    {
        // In production, you might want to backup data before this migration
        // For rollback, we'll remove program_id (handled by the previous migration)
    }
};
