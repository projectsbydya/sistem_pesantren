<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vocabularies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('program_id')->index();
            $table->unsignedBigInteger('santri_id')->index();
            $table->string('type', 30)->index();
            $table->string('word', 255);
            $table->string('language', 10)->default('en');
            $table->string('translation', 255)->nullable();
            $table->text('example_sentence')->nullable();
            $table->string('category', 100)->nullable();
            $table->integer('score')->nullable();
            $table->string('status', 20)->default('belum');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('santri_id')->references('id')->on('santri')->cascadeOnDelete();

            $table->index(['tenant_id', 'program_id', 'santri_id']);
            $table->index(['tenant_id', 'type']);
            $table->index(['santri_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocabularies');
    }
};
