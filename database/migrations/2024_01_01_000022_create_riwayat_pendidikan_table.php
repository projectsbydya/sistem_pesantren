<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_pendidikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->string('institution');
            $table->integer('year_start');
            $table->integer('year_end')->nullable();
            $table->string('name'); // nama sekolah/lembaga
            $table->string('level')->nullable(); // SD, SMP, SMA, etc
            $table->timestamps();
            
            $table->index(['santri_id']);
            $table->index(['institution']);
            $table->index(['year_start']);
            $table->index(['level']);
            $table->index(['santri_id', 'year_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_pendidikan');
    }
};
