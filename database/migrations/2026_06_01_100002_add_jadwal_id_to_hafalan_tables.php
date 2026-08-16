<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add jadwal_id to hafalan_quran table
        Schema::table('hafalan_quran', function (Blueprint $table) {
            $table->unsignedBigInteger('jadwal_id')->nullable()->after('ustadz_kelas_id');
            $table->foreign('jadwal_id')->references('id')->on('jadwal')->nullOnDelete();
            $table->index(['tenant_id', 'jadwal_id']);
        });

        // Add jadwal_id to hafalan_kitab table
        Schema::table('hafalan_kitab', function (Blueprint $table) {
            $table->unsignedBigInteger('jadwal_id')->nullable()->after('ustadz_kelas_id');
            $table->foreign('jadwal_id')->references('id')->on('jadwal')->nullOnDelete();
            $table->index(['tenant_id', 'jadwal_id']);
        });
    }

    public function down(): void
    {
        Schema::table('hafalan_quran', function (Blueprint $table) {
            $table->dropForeign(['jadwal_id']);
            $table->dropIndex(['tenant_id', 'jadwal_id']);
            $table->dropColumn('jadwal_id');
        });

        Schema::table('hafalan_kitab', function (Blueprint $table) {
            $table->dropForeign(['jadwal_id']);
            $table->dropIndex(['tenant_id', 'jadwal_id']);
            $table->dropColumn('jadwal_id');
        });
    }
};
