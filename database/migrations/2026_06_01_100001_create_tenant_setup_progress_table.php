<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Table untuk tracking progress onboarding/setup tenant.
     * Setiap step bisa di-track statusnya.
     */
    public function up(): void
    {
        Schema::create('tenant_setup_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            
            // Steps onboarding
            $table->boolean('step_program_selected')->default(false);
            $table->timestamp('step_program_selected_at')->nullable();
            
            $table->boolean('step_branches_setup')->default(false);
            $table->timestamp('step_branches_setup_at')->nullable();
            
            $table->boolean('step_kelas_template_applied')->default(false);
            $table->timestamp('step_kelas_template_applied_at')->nullable();
            
            $table->boolean('step_subjects_template_applied')->default(false);
            $table->timestamp('step_subjects_template_applied_at')->nullable();
            
            $table->boolean('step_first_santri_added')->default(false);
            $table->timestamp('step_first_santri_added_at')->nullable();
            
            $table->boolean('step_first_ustadz_added')->default(false);
            $table->timestamp('step_first_ustadz_added_at')->nullable();
            
            $table->boolean('step_jadwal_setup')->default(false);
            $table->timestamp('step_jadwal_setup_at')->nullable();
            
            // Overall status
            $table->enum('setup_status', ['pending', 'in_progress', 'siap_operasional', 'completed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            
            // Percentage 0-100
            $table->unsignedTinyInteger('progress_percentage')->default(0);
            
            $table->timestamps();
            
            $table->unique(['tenant_id']);
            $table->index(['setup_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_setup_progress');
    }
};
