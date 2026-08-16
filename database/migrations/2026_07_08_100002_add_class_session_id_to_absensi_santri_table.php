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
        Schema::table('absensi_santri', function (Blueprint $table) {
            $table->foreignId('class_session_id')->nullable()->after('jadwal_id')->constrained('class_sessions')->nullOnDelete();
            $table->index(['class_session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_santri', function (Blueprint $table) {
            $table->dropForeign(['class_session_id']);
            $table->dropIndex(['class_session_id']);
            $table->dropColumn('class_session_id');
        });
    }
};
