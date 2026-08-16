<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->string('jenis_kegiatan'); // sholat, tilawah, dzikir, etc
            $table->string('kategori')->nullable(); // wajib, sunnah, ekstra
            $table->date('tanggal');
            $table->time('waktu_mulai')->nullable();
            $table->time('waktu_selesai')->nullable();
            $table->string('status')->default('terjadwal'); // terjadwal, dilaksanakan, tidak_dilaksanakan
            $table->text('catatan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'jenis_kegiatan']);
            $table->index(['tenant_id', 'tanggal']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_harian');
    }
};
