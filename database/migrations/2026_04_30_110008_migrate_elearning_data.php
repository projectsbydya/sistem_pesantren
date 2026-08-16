<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate diniyah elearning data
        DB::statement('
            INSERT INTO elearning_diniyah (tenant_id, ustadz_id, subject_id, kelas_id, judul, deskripsi, file_path, link, created_at, updated_at)
            SELECT tenant_id, ustadz_id, subject_id, kelas_id, judul, deskripsi, file_path, link, created_at, updated_at
            FROM elearning
            WHERE type = \'diniyah\'
        ');

        // Migrate pesantren elearning data (without subject_id and kelas_id)
        DB::statement('
            INSERT INTO elearning_pesantren (tenant_id, ustadz_id, kitab, materi, judul, deskripsi, file_path, link, created_at, updated_at)
            SELECT tenant_id, ustadz_id, NULL, NULL, judul, deskripsi, file_path, link, created_at, updated_at
            FROM elearning
            WHERE type = \'pesantren\'
        ');
    }

    public function down(): void
    {
        DB::table('elearning_diniyah')->delete();
        DB::table('elearning_pesantren')->delete();
    }
};
