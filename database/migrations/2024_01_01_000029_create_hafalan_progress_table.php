<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hafalan_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->text('progress');
            $table->timestamp('last_update');
            $table->timestamps();
            
            $table->index(['santri_id']);
            $table->index(['last_update']);
            $table->unique(['santri_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hafalan_progress');
    }
};
