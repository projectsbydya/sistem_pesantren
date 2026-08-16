<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Migrate diniyah absensi data
        DB::statement('
            INSERT INTO absensi_diniyah (id, tenant_id, jadwal_id, santri_id, tanggal, status, catatan, created_at, updated_at)
            SELECT id, tenant_id, jadwal_id, santri_id, tanggal, status, catatan, created_at, updated_at
            FROM absensi_santri
            WHERE type = \'diniyah\'
        ');

        // Migrate pesantren absensi data
        DB::statement('
            INSERT INTO absensi_pesantren (tenant_id, santri_id, tanggal, status, catatan, created_at, updated_at)
            SELECT tenant_id, santri_id, tanggal, status, catatan, created_at, updated_at
            FROM absensi_santri
            WHERE type = \'pesantren\'
        ');
    }

    public function down(): void
    {
        // Data migration rollback not supported (would need ID mapping)
        DB::table('absensi_diniyah')->delete();
        DB::table('absensi_pesantren')->delete();
    }
};
