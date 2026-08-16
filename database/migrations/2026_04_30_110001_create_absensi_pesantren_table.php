<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_pesantren', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            // Pesantren absensi doesn't use jadwal, it's based on kitab/materi
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->string('kitab')->nullable(); // Kitab being studied
            $table->string('materi')->nullable(); // Materi/session
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa'])->default('hadir');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'tanggal']);
            $table->index(['santri_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_pesantren');
    }
};
