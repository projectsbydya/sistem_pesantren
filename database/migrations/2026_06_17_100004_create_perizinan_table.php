<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perizinan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->string('jenis'); // pulang, keluar, etc
            $table->string('alasan');
            $table->text('keterangan')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('destinasi')->nullable();
            $table->string('penjemput')->nullable();
            $table->string('no_hp_penjemput')->nullable();
            $table->string('status')->default('pending'); // pending, disetujui, ditolak, kembali
            $table->foreignId('diajukan_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tanggal_persetujuan')->nullable();
            $table->timestamp('tanggal_kembali')->nullable();
            $table->text('catatan_keamanan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'tanggal_mulai']);
            $table->index(['tenant_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perizinan');
    }
};
