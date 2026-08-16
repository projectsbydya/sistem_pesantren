<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penempatan_kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kamar_id')->constrained('kamar')->cascadeOnDelete();
            $table->date('tanggal_masuk');
            $table->date('tanggal_keluar')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'kamar_id']);
            $table->index(['tenant_id', 'tanggal_masuk']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penempatan_kamar');
    }
};
