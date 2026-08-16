<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ustadz', function (Blueprint $table) {
            $table->string('role')->nullable()->after('bio')->comment('Peran: pengajar, wali_kelas, kepala_ponpes, dll');
            $table->unsignedTinyInteger('jam_per_minggu')->nullable()->after('role')->comment('Jam mengajar per minggu');
            $table->unsignedTinyInteger('performa')->nullable()->after('jam_per_minggu')->comment('Nilai performa 0-100');
            $table->string('status')->default('active')->after('performa')->comment('active, inactive, cuti');
            
            $table->index(['role']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ustadz', function (Blueprint $table) {
            // Drop indexes before columns (MySQL 1553: cannot drop indexed column)
            if (Schema::hasIndex('ustadz', 'ustadz_role_index')) {
                $table->dropIndex('ustadz_role_index');
            }
            if (Schema::hasIndex('ustadz', 'ustadz_status_index')) {
                $table->dropIndex('ustadz_status_index');
            }
            $table->dropColumn(['role', 'jam_per_minggu', 'performa', 'status']);
        });
    }
};
