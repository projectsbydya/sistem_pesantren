<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_hafalan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->enum('type', ['quran', 'kitab']);
            $table->string('target');
            $table->date('deadline')->nullable();
            $table->enum('status', ['belum', 'proses', 'tercapai'])->default('belum');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id']);
            $table->index(['santri_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_hafalan');
    }
};
