<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->string('illness')->nullable();
            $table->text('treatment')->nullable();
            $table->date('date');
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['santri_id']);
            $table->index(['date']);
            $table->index(['recorded_by']);
            $table->index(['santri_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_records');
    }
};
