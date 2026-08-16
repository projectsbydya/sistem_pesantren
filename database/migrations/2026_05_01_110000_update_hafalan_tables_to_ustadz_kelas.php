<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to update hafalan tables to use ustadz_kelas_id.
     */
    public function up(): void
    {
        // Idempotent: skip if already migrated
        if (Schema::hasColumn('hafalan_quran', 'ustadz_kelas_id') &&
            !Schema::hasColumn('hafalan_quran', 'ustadz_id') &&
            Schema::hasColumn('hafalan_kitab', 'ustadz_kelas_id') &&
            !Schema::hasColumn('hafalan_kitab', 'ustadz_id')) {
            return;
        }

        // --- hafalan_quran ---
        // Step 1: Add ustadz_kelas_id (nullable) if not exists
        if (!Schema::hasColumn('hafalan_quran', 'ustadz_kelas_id')) {
            Schema::table('hafalan_quran', function (Blueprint $table) {
                $table->foreignId('ustadz_kelas_id')
                    ->nullable()
                    ->after('santri_id')
                    ->constrained('ustadz_kelas')
                    ->cascadeOnDelete();
            });
        }

        // Step 2: Drop old ustadz_id — FK first, then index, then column (MySQL 1553 rule)
        if (Schema::hasColumn('hafalan_quran', 'ustadz_id')) {
            Schema::table('hafalan_quran', function (Blueprint $table) {
                try { $table->dropForeign(['ustadz_id']); } catch (\Exception $e) {}
                if (Schema::hasIndex('hafalan_quran', 'hafalan_quran_ustadz_id_index')) {
                    $table->dropIndex('hafalan_quran_ustadz_id_index');
                }
                $table->dropColumn('ustadz_id');
            });
        }

        // --- hafalan_kitab ---
        // Step 1: Add ustadz_kelas_id (nullable) if not exists
        if (!Schema::hasColumn('hafalan_kitab', 'ustadz_kelas_id')) {
            Schema::table('hafalan_kitab', function (Blueprint $table) {
                $table->foreignId('ustadz_kelas_id')
                    ->nullable()
                    ->after('santri_id')
                    ->constrained('ustadz_kelas')
                    ->cascadeOnDelete();
            });
        }

        // Step 2: Drop old ustadz_id — FK first, then index, then column
        if (Schema::hasColumn('hafalan_kitab', 'ustadz_id')) {
            Schema::table('hafalan_kitab', function (Blueprint $table) {
                try { $table->dropForeign(['ustadz_id']); } catch (\Exception $e) {}
                if (Schema::hasIndex('hafalan_kitab', 'hafalan_kitab_ustadz_id_index')) {
                    $table->dropIndex('hafalan_kitab_ustadz_id_index');
                }
                $table->dropColumn('ustadz_id');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // hafalan_quran: drop FK first, then index, then column, then restore ustadz_id
        if (Schema::hasColumn('hafalan_quran', 'ustadz_kelas_id')) {
            Schema::table('hafalan_quran', function (Blueprint $table) {
                try { $table->dropForeign(['ustadz_kelas_id']); } catch (\Exception $e) {}
                $table->dropColumn('ustadz_kelas_id');
                $table->foreignId('ustadz_id')->nullable()->after('santri_id')->constrained('ustadz')->nullOnDelete();
            });
        }

        // hafalan_kitab: same pattern
        if (Schema::hasColumn('hafalan_kitab', 'ustadz_kelas_id')) {
            Schema::table('hafalan_kitab', function (Blueprint $table) {
                try { $table->dropForeign(['ustadz_kelas_id']); } catch (\Exception $e) {}
                $table->dropColumn('ustadz_kelas_id');
                $table->foreignId('ustadz_id')->nullable()->after('santri_id')->constrained('ustadz')->nullOnDelete();
            });
        }
    }
};
