<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-student assignment progress and evaluation.
     */
    public function up(): void
    {
        Schema::create('assignment_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('assignment_id')->index();
            $table->unsignedBigInteger('santri_id')->index();
            $table->text('progress')->nullable();
            $table->string('status', 20)->default('belum');
            $table->integer('score')->nullable();
            $table->text('notes')->nullable();
            // Type-specific extras (e.g. muhadhoroh's performed_at, submission_url,
            // is_video_submission) live in metadata — they are not reused across all
            // assignment types, unlike progress/status/score/notes.
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('assignment_id')->references('id')->on('assignments')->cascadeOnDelete();
            $table->foreign('santri_id')->references('id')->on('santri')->cascadeOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['assignment_id', 'santri_id'], 'assignment_members_assignment_santri_unique');
            $table->index(['tenant_id', 'santri_id', 'status']);
            $table->index(['santri_id', 'status']);
            $table->index(['assignment_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_members');
    }
};
