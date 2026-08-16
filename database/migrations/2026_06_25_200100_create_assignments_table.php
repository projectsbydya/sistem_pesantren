<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Class-centric assignments shared data.
     *
     * Per-student evaluation lives in assignment_members.
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('program_id')->index();
            $table->unsignedBigInteger('kelas_id')->nullable()->index();
            $table->string('type', 50)->index();
            $table->string('title', 255);
            $table->text('target')->nullable();
            // Lifecycle of the assignment itself (draft/published/archived) — distinct
            // from AssignmentMember.status, which represents a student's progress.
            $table->string('state', 20)->default('published');
            $table->timestamp('published_at')->nullable();
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('programs')->restrictOnDelete();
            $table->foreign('kelas_id')->references('id')->on('kelas')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['tenant_id', 'program_id', 'type']);
            $table->index(['tenant_id', 'type', 'state']);
            $table->index(['program_id', 'kelas_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
