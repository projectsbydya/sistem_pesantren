<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('program_assessment_configs')) {
            return;
        }

        Schema::create('program_assessment_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('assessment_type_id')->constrained('assessment_types')->cascadeOnDelete();
            $table->decimal('weight', 5, 2)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['tenant_id', 'program_id', 'assessment_type_id'],
                'pac_tenant_program_assessment_unique'
            );
            $table->index(
                ['tenant_id', 'program_id', 'is_active', 'sort_order'],
                'pac_tenant_program_active_order_index'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_assessment_configs');
    }
};
