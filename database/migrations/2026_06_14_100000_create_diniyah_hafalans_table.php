<?php

/**
 * ARCHITECTURE FROZEN: Unified DiniyahHafalan table
 *
 * Replaces legacy tables: diniyah_hafalan_doa, diniyah_hafalan_hadits, diniyah_hafalan_surat
 * Single entity with 'type' column (doa|hadits|surat) for all hafalan types.
 *
 * @frozen 2026-06-14
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diniyah_hafalans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('program_id')->index();
            $table->unsignedBigInteger('santri_id')->index();
            $table->enum('type', ['doa', 'hadits', 'surat'])->index();
            $table->string('title', 255);
            $table->text('target')->nullable();
            $table->text('achievement')->nullable();
            $table->string('status', 20)->default('belum');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('santri_id')->references('id')->on('santri')->cascadeOnDelete();

            $table->index(['tenant_id', 'program_id', 'santri_id']);
            $table->index(['tenant_id', 'type']);
            $table->index(['santri_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diniyah_hafalans');
    }
};
