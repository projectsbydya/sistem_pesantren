<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->date('date');
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpa']);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['tenant_id', 'santri_id', 'date']);
            $table->index(['tenant_id']);
            $table->index(['santri_id']);
            $table->index(['date']);
            $table->index(['status']);
            $table->index(['tenant_id', 'date']);
            $table->index(['santri_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
