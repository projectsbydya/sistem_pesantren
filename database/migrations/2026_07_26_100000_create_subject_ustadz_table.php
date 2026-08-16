<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('subject_ustadz')) {
            return;
        }

        Schema::create('subject_ustadz', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ustadz_id')->constrained('ustadz')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['ustadz_id', 'subject_id']);
            $table->index(['ustadz_id']);
            $table->index(['subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subject_ustadz');
    }
};
