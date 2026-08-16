<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->integer('points_required')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['name']);
            $table->index(['is_active']);
            $table->index(['points_required']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
