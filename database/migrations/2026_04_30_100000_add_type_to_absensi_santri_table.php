<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_santri', function (Blueprint $table) {
            $table->enum('type', ['diniyah', 'pesantren'])->default('diniyah')->after('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_santri', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
