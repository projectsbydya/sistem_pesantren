<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->date('assigned_at');
            $table->date('left_at')->nullable();
            $table->timestamps();
            
            $table->unique(['room_id', 'santri_id', 'assigned_at']);
            $table->index(['room_id']);
            $table->index(['santri_id']);
            $table->index(['assigned_at']);
            $table->index(['left_at']);
            $table->index(['santri_id', 'assigned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_members');
    }
};
