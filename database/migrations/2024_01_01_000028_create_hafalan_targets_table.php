<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hafalan_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->enum('target_type', ['juz', 'surah', 'halaman']);
            $table->string('target_value');
            $table->date('deadline')->nullable();
            $table->boolean('is_achieved')->default(false);
            $table->timestamps();
            
            $table->index(['santri_id']);
            $table->index(['target_type']);
            $table->index(['deadline']);
            $table->index(['is_achieved']);
            $table->index(['santri_id', 'target_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hafalan_targets');
    }
};
