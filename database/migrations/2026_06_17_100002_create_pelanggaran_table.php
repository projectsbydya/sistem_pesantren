<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->string('jenis'); // ringan, sedang, berat
            $table->string('kategori'); // akademik, disiplin, kebersihan, etc
            $table->text('deskripsi');
            $table->date('tanggal');
            $table->string('lokasi')->nullable();
            $table->foreignId('pelapor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending'); // pending, diproses, selesai
            $table->text('tindak_lanjut')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'jenis']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran');
    }
};
