<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            $table->string('status')->default('aktif')->after('kapasitas');
            $table->string('lokasi')->nullable()->after('status');
            $table->text('fasilitas')->nullable()->after('lokasi');
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('kamar', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropColumn(['status', 'lokasi', 'fasilitas']);
        });
    }
};
