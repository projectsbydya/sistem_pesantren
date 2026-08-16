<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add program_id to all academic tables
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
            if (!Schema::hasColumn($table, 'program_id')) {
                Schema::table($table, function (Blueprint $table) {
                    // Add as nullable first — existing rows cannot have NOT NULL without default.
                    // The next migration (migrate_academic_data_to_program_id) fills the values,
                    // then makes this column NOT NULL.
                    $table->foreignId('program_id')->nullable()->constrained('programs')->after('id')->cascadeOnDelete();
                    $table->index('program_id');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'kelas',
            'subjects',
            'jadwal',
            'nilai',
            'absensi_santri',
            'elearning',
            'ustadz_kelas'
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName) || !Schema::hasColumn($tableName, 'program_id')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $blueprint) use ($tableName) {
                // FK first, then index, then column (MySQL 1553 rule)
                try {
                    $blueprint->dropForeign(['program_id']);
                } catch (\Exception $e) {}

                $indexName = $tableName . '_program_id_index';
                if (Schema::hasIndex($tableName, $indexName)) {
                    $blueprint->dropIndex($indexName);
                }

                $blueprint->dropColumn('program_id');
            });
        }
    }
};
