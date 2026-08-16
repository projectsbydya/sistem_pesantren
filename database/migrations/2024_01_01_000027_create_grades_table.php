<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->enum('type', ['tugas', 'uts', 'uas', 'harian', 'lainnya']);
            $table->decimal('score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->date('date');
            $table->timestamps();
            
            $table->index(['santri_id']);
            $table->index(['subject_id']);
            $table->index(['type']);
            $table->index(['date']);
            $table->index(['santri_id', 'subject_id']);
            $table->index(['santri_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
