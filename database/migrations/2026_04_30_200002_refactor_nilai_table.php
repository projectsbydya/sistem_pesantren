<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('nilai', function (Blueprint $table) {
            // Add ustadz_kelas_id (the authoritative assignment reference)
            $table->foreignId('ustadz_kelas_id')->nullable()->after('kelas_id')->constrained('ustadz_kelas')->nullOnDelete();

            $table->index(['ustadz_kelas_id']);
        });

        // Backfill ustadz_kelas_id using subquery (SQLite compatible)
        \Illuminate\Support\Facades\DB::table('nilai')
            ->whereNotNull('ustadz_id')
            ->whereNotNull('kelas_id')
            ->whereNotNull('tenant_id')
            ->update([
                'ustadz_kelas_id' => \Illuminate\Support\Facades\DB::raw("
                    (SELECT uk.id FROM ustadz_kelas uk 
                     WHERE uk.ustadz_id = nilai.ustadz_id 
                       AND uk.kelas_id = nilai.kelas_id 
                       AND uk.tenant_id = nilai.tenant_id 
                       AND (uk.subject_id = nilai.subject_id OR (uk.subject_id IS NULL AND nilai.subject_id IS NULL))
                       LIMIT 1)
                ")
            ]);

        Schema::table('nilai', function (Blueprint $table) {
            // Drop old direct FK
            $table->dropForeign(['ustadz_id']);
            $table->dropColumn('ustadz_id');
        });
    }

    public function down(): void
    {
        Schema::table('nilai', function (Blueprint $table) {
            // Drop FK first, then standalone index, then column
            $table->dropForeign(['ustadz_kelas_id']);

            if (Schema::hasIndex('nilai', 'nilai_ustadz_kelas_id_index')) {
                $table->dropIndex('nilai_ustadz_kelas_id_index');
            }

            $table->dropColumn('ustadz_kelas_id');

            // Restore ustadz_id
            $table->foreignId('ustadz_id')->nullable()->after('kelas_id')->constrained('ustadz')->nullOnDelete();
        });
    }
};
