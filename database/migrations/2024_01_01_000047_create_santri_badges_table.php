<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade');
            $table->timestamp('awarded_at');
            $table->foreignId('awarded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['santri_id', 'badge_id']);
            $table->index(['santri_id']);
            $table->index(['badge_id']);
            $table->index(['awarded_at']);
            $table->index(['awarded_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri_badges');
    }
};
