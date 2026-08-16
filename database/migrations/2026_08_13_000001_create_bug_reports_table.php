<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Core bug/error reports submitted by authenticated tenant users.
     */
    public function up(): void
    {
        Schema::create('bug_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('category', 30);
            $table->string('status', 30)->default('open');
            $table->string('severity', 30);
            $table->string('source_url', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'severity']);
            $table->index(['tenant_id', 'category']);
            $table->index(['reporter_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bug_reports');
    }
};
