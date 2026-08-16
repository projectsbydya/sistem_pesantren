<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate diniyah nilai data
        DB::statement('
            INSERT INTO nilai_diniyah (tenant_id, santri_id, subject_id, kelas_id, ustadz_id, tanggal, materi, nilai, catatan, created_at, updated_at)
            SELECT n.tenant_id, n.santri_id, n.subject_id, n.kelas_id, n.ustadz_id, n.tanggal, n.materi, n.nilai, n.catatan, n.created_at, n.updated_at
            FROM nilai n
            WHERE n.type = \'diniyah\'
        ');

        // Migrate pesantren nilai data (without subject_id and kelas_id)
        DB::statement('
            INSERT INTO nilai_pesantren (tenant_id, santri_id, kitab, materi, ustadz_id, tanggal, nilai, catatan, created_at, updated_at)
            SELECT n.tenant_id, n.santri_id, NULL, n.materi, n.ustadz_id, n.tanggal, n.nilai, n.catatan, n.created_at, n.updated_at
            FROM nilai n
            WHERE n.type = \'pesantren\'
        ');
    }

    public function down(): void
    {
        DB::table('nilai_diniyah')->delete();
        DB::table('nilai_pesantren')->delete();
    }
};
