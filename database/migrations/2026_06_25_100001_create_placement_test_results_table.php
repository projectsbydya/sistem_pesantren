<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('placement_test_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('placement_test_id');
            $table->unsignedBigInteger('santri_id');
            $table->unsignedTinyInteger('score')->nullable();
            $table->unsignedBigInteger('level_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('placement_test_id')->references('id')->on('placement_tests')->cascadeOnDelete();
            $table->foreign('santri_id')->references('id')->on('santri')->cascadeOnDelete();
            $table->foreign('level_id')->references('id')->on('placement_test_levels')->nullOnDelete();

            $table->index(['tenant_id', 'program_id', 'placement_test_id'], 'pt_results_tenant_program_test_idx');
            $table->index(['tenant_id', 'program_id', 'santri_id'], 'pt_results_tenant_program_santri_idx');
            $table->unique(['placement_test_id', 'santri_id'], 'pt_results_test_santri_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placement_test_results');
    }
};
