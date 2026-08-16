<?php

/**
 * ARCHITECTURE FROZEN: Unified DiniyahMonitoring table
 *
 * Replaces legacy tables: diniyah_monitoring_sholat, diniyah_monitoring_adab, diniyah_monitoring_akhlak
 * Single entity with 'type' column (sholat|adab|akhlak) for all monitoring types.
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
        Schema::create('diniyah_monitorings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('program_id')->index();
            $table->unsignedBigInteger('santri_id')->index();
            $table->enum('type', ['sholat', 'adab', 'akhlak'])->index();
            $table->date('date');
            $table->string('aspect', 100)->nullable(); // sholat time, adab aspect, akhlak aspect
            $table->string('category', 50)->nullable(); // for akhlak category
            $table->string('status', 30);
            $table->integer('score')->nullable(); // 1-4 scale
            $table->boolean('flag')->nullable()->default(false); // for sholat berjamaah
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('santri_id')->references('id')->on('santri')->cascadeOnDelete();

            $table->index(['tenant_id', 'program_id', 'santri_id']);
            $table->index(['tenant_id', 'type']);
            $table->index(['santri_id', 'date']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diniyah_monitorings');
    }
};
