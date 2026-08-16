<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->integer('graduation_year');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('occupation')->nullable();
            $table->string('university')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['santri_id']);
            $table->index(['santri_id']);
            $table->index(['graduation_year']);
            $table->index(['occupation']);
            $table->index(['university']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni');
    }
};
