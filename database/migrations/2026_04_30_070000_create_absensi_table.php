<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('jadwal_id')->constrained('jadwal')->onDelete('cascade');
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa']);
            $table->text('catatan')->nullable();
            $table->timestamps();

            // One record per santri per jadwal per date
            $table->unique(['tenant_id', 'jadwal_id', 'santri_id', 'tanggal']);
            $table->index(['tenant_id']);
            $table->index(['jadwal_id', 'tanggal']);
            $table->index(['santri_id', 'tanggal']);
            $table->index(['tenant_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
