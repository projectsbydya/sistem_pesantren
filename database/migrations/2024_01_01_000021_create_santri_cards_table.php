<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')
                ->constrained('santri')
                ->cascadeOnDelete();
            $table->string('qr_code')->unique()->nullable();
            $table->string('rfid_code')->unique()->nullable();
            $table->timestamps();
            
            $table->unique(['santri_id']);
            $table->index(['qr_code']);
            $table->index(['rfid_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri_cards');
    }
};
