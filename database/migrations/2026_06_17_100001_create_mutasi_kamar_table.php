<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mutasi_kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kamar_asal_id')->constrained('kamar')->cascadeOnDelete();
            $table->foreignId('kamar_tujuan_id')->constrained('kamar')->cascadeOnDelete();
            $table->date('tanggal_mutasi');
            $table->string('alasan');
            $table->text('keterangan')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'kamar_asal_id']);
            $table->index(['tenant_id', 'kamar_tujuan_id']);
            $table->index(['tenant_id', 'tanggal_mutasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mutasi_kamar');
    }
};
