<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->enum('type', ['ringan', 'sedang', 'berat']);
            $table->integer('points')->default(0);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['santri_id']);
            $table->index(['type']);
            $table->index(['points']);
            $table->index(['date']);
            $table->index(['reported_by']);
            $table->index(['santri_id', 'date']);
            $table->index(['santri_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('violations');
    }
};
