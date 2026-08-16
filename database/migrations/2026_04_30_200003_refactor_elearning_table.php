<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elearning', function (Blueprint $table) {
            // Add ustadz_kelas_id only if it doesn't exist
            if (!Schema::hasColumn('elearning', 'ustadz_kelas_id')) {
                $table->foreignId('ustadz_kelas_id')->nullable()->after('kelas_id')->constrained('ustadz_kelas')->nullOnDelete();
                $table->index(['ustadz_kelas_id']);
            }
        });

        // Backfill ustadz_kelas_id using subquery (SQLite compatible)
        \Illuminate\Support\Facades\DB::table('elearning')
            ->whereNotNull('ustadz_id')
            ->whereNotNull('kelas_id')
            ->whereNotNull('tenant_id')
            ->update([
                'ustadz_kelas_id' => \Illuminate\Support\Facades\DB::raw("
                    (SELECT uk.id FROM ustadz_kelas uk 
                     WHERE uk.ustadz_id = elearning.ustadz_id 
                       AND uk.kelas_id = elearning.kelas_id 
                       AND uk.tenant_id = elearning.tenant_id 
                       AND (uk.subject_id = elearning.subject_id OR (uk.subject_id IS NULL AND elearning.subject_id IS NULL))
                       LIMIT 1)
                ")
            ]);

        // Drop ustadz_id FK, then its index, then the column.
        // Order is mandatory on MySQL: dropping an index that backs a FK constraint
        // raises error 1553. Always drop the FK first.
        if (Schema::hasColumn('elearning', 'ustadz_id')) {
            Schema::table('elearning', function (Blueprint $table) {
                // Step 1: Drop FK first (removes backing-index dependency)
                try {
                    $table->dropForeign(['ustadz_id']);
                } catch (\Exception $e) {
                    // Already dropped or never existed
                }

                // Step 2: Now safe to drop the standalone index
                if (Schema::hasIndex('elearning', 'elearning_ustadz_id_index')) {
                    $table->dropIndex('elearning_ustadz_id_index');
                }

                // Step 3: Drop the column
                $table->dropColumn('ustadz_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('elearning', function (Blueprint $table) {
            // Drop FK on ustadz_kelas_id before dropping column
            $table->dropForeign(['ustadz_kelas_id']);

            // Drop standalone index on ustadz_kelas_id if present
            if (Schema::hasIndex('elearning', 'elearning_ustadz_kelas_id_index')) {
                $table->dropIndex('elearning_ustadz_kelas_id_index');
            }

            $table->dropColumn('ustadz_kelas_id');

            // Restore ustadz_id
            $table->foreignId('ustadz_id')->nullable()->after('kelas_id')->constrained('ustadz')->nullOnDelete();
        });
    }
};
