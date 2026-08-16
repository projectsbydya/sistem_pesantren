<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_santri', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                ->constrained("parents")
                ->cascadeOnDelete();
            $table->foreignId('santri_id')
                ->constrained("santri")
                ->cascadeOnDelete();
            $table->enum('relationship', ['father', 'mother', 'guardian'])->default('father');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            
            // Composite unique to prevent duplicate relationships
            $table->unique(['parent_id', 'santri_id']);
            $table->index(['santri_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_santri');
    }
};
