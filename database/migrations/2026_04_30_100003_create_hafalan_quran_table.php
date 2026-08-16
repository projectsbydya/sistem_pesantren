<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hafalan_quran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('ustadz_id')->nullable()->constrained('ustadz')->nullOnDelete();
            $table->date('tanggal');
            $table->string('surah');
            $table->string('ayat_dari')->nullable();
            $table->string('ayat_sampai')->nullable();
            $table->integer('juz')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->enum('status', ['belum', 'proses', 'lulus'])->default('proses');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id']);
            $table->index(['santri_id', 'tanggal']);
            $table->index(['ustadz_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hafalan_quran');
    }
};
