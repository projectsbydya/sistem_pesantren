<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            // Add structured kelas FK (replaces raw `kelas` string)
            $table->foreignId('kelas_id')->nullable()->after('ustadz_id')->constrained('kelas')->nullOnDelete();

            // Add ustadz_kelas_id as the canonical assignment FK
            $table->foreignId('ustadz_kelas_id')->nullable()->after('kelas_id')->constrained('ustadz_kelas')->nullOnDelete();

            // Add type to distinguish program context
            $table->enum('type', ['diniyah', 'pesantren'])->default('diniyah')->after('ustadz_kelas_id');

            $table->index(['kelas_id', 'hari']);
            $table->index(['ustadz_kelas_id']);
        });
    }

    public function down(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropForeign(['ustadz_kelas_id']);
            $table->dropColumn(['kelas_id', 'ustadz_kelas_id', 'type']);
        });
    }
};
