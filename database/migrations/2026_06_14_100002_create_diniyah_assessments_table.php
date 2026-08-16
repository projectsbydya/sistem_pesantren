<?php

/**
 * ARCHITECTURE FROZEN: Unified DiniyahAssessment table
 *
 * Replaces legacy tables: diniyah_nilai_keagamaan, diniyah_nilai_akhlak
 * Single entity with 'type' column (keagamaan|akhlak) for all assessment types.
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
        Schema::create('diniyah_assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('program_id')->index();
            $table->unsignedBigInteger('santri_id')->index();
            $table->enum('type', ['keagamaan', 'akhlak'])->index();
            $table->string('aspect', 100)->nullable(); // assessment aspect/topic
            $table->decimal('score', 5, 2); // numeric score
            $table->string('predikat', 2)->nullable(); // A, B, C, D
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('santri_id')->references('id')->on('santri')->cascadeOnDelete();

            $table->index(['tenant_id', 'program_id', 'santri_id']);
            $table->index(['tenant_id', 'type']);
            $table->index(['santri_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diniyah_assessments');
    }
};
