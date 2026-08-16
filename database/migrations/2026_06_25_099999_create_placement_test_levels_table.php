<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('placement_test_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('program_id');
            $table->unsignedTinyInteger('min_score');
            $table->unsignedTinyInteger('max_score');
            $table->string('label', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();

            $table->index(['tenant_id', 'program_id', 'min_score', 'max_score'], 'pt_levels_tenant_program_scores_idx');
            $table->index(['tenant_id', 'program_id', 'is_active'], 'pt_levels_tenant_program_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placement_test_levels');
    }
};
