<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_UNIQUE = 'absensi_ustadz_tenant_id_ustadz_id_tanggal_unique';

    private const NEW_UNIQUE = 'absensi_ustadz_tenant_schedule_tanggal_unique';

    public function up(): void
    {
        Schema::table('absensi_ustadz', function (Blueprint $table) {
            $table->foreignId('schedule_id')
                ->nullable()
                ->after('ustadz_id')
                ->constrained('jadwal')
                ->nullOnDelete();
        });

        Schema::table('absensi_ustadz', function (Blueprint $table) {
            $table->dropUnique(self::OLD_UNIQUE);
            $table->unique(
                ['tenant_id', 'schedule_id', 'tanggal'],
                self::NEW_UNIQUE
            );
        });
    }

    public function down(): void
    {
        Schema::table('absensi_ustadz', function (Blueprint $table) {
            $table->dropUnique(self::NEW_UNIQUE);
            $table->unique(
                ['tenant_id', 'ustadz_id', 'tanggal'],
                self::OLD_UNIQUE
            );
            $table->dropConstrainedForeignId('schedule_id');
        });
    }
};
