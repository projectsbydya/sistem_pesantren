<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop orphaned `schedules` table (Batch 1 scaffolding, never implemented).
     * No Model, Controller, or route ever referenced this table.
     * Active jadwal feature uses the `jadwal` table instead.
     */
    public function up(): void
    {
        Schema::dropIfExists('schedules');
    }

    public function down(): void
    {
        // Restore the original schema if rolling back
        Schema::create('schedules', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('ustadz_id')->constrained('ustadz')->onDelete('cascade');
            $table->enum('day', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->timestamps();
        });
    }
};
