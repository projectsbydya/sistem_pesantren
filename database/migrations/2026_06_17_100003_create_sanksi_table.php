<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sanksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pelanggaran_id')->constrained('pelanggaran')->cascadeOnDelete();
            $table->string('jenis'); // peringatan, tugas, skorsing, etc
            $table->text('deskripsi');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->string('status')->default('aktif'); // aktif, selesai, dibatalkan
            $table->text('hasil_evaluasi')->nullable();
            $table->foreignId('diberikan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'pelanggaran_id']);
            $table->index(['tenant_id', 'jenis']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'tanggal_mulai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanksi');
    }
};
