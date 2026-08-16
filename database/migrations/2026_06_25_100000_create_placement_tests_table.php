<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('placement_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('program_id');
            $table->string('type', 30);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->unsignedTinyInteger('max_score')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();

            $table->index(['tenant_id', 'program_id', 'type'], 'pt_tests_tenant_program_type_idx');
            $table->index(['tenant_id', 'program_id', 'date'], 'pt_tests_tenant_program_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placement_tests');
    }
};
