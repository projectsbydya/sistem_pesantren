<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add school_name column to santri
        Schema::table('santri', function (Blueprint $table) {
            $table->string('school_name')->nullable()->after('school_level');
        });

        // 2. Fix santri_programs FK — MySQL only (SQLite handles CASCADE differently)
        if (\DB::getDriverName() === 'mysql') {
            \DB::statement('SET FOREIGN_KEY_CHECKS=0');

            try {
                \DB::statement('ALTER TABLE `santri_programs` DROP FOREIGN KEY `santri_programs_santri_id_foreign`');
            } catch (\Exception $e) {
                // FK may have different name, ignore
            }

            \DB::statement('ALTER TABLE `santri_programs` ADD CONSTRAINT `santri_programs_santri_id_foreign` FOREIGN KEY (`santri_id`) REFERENCES `santri` (`id`) ON DELETE CASCADE');

            \DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->dropColumn('school_name');
        });
    }
};
