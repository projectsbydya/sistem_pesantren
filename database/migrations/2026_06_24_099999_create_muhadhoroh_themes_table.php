<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('muhadhoroh_themes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('program_id');
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();

            $table->index(['tenant_id', 'program_id', 'is_active']);
            $table->unique(['tenant_id', 'program_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('muhadhoroh_themes');
    }
};
