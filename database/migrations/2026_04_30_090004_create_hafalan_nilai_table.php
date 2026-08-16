<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hafalan_nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('ustadz_id')->constrained('ustadz')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('jenis', ['hafalan', 'nilai']);
            $table->string('materi')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            // One record per santri+subject+tanggal+jenis
            $table->unique(['tenant_id', 'santri_id', 'subject_id', 'tanggal', 'jenis'], 'hn_unique');
            $table->index(['tenant_id']);
            $table->index(['santri_id', 'tanggal']);
            $table->index(['kelas_id', 'subject_id']);
            $table->index(['ustadz_id']);
            $table->index(['tenant_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hafalan_nilai');
    }
};
