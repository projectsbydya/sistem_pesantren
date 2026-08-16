<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('jadwal_id')->nullable();
            $table->unsignedBigInteger('ustadz_kelas_id');
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('subject_id');
            $table->date('tanggal');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->text('tujuan_pembelajaran')->nullable();
            $table->text('aktivitas')->nullable();
            $table->text('referensi')->nullable();
            $table->enum('status', ['draft', 'published', 'completed'])->default('draft');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('jadwal_id')->references('id')->on('jadwal')->nullOnDelete();
            $table->foreign('ustadz_kelas_id')->references('id')->on('ustadz_kelas')->cascadeOnDelete();
            $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();

            $table->index(['tenant_id', 'program_id']);
            $table->index(['tenant_id', 'jadwal_id']);
            $table->index(['tenant_id', 'kelas_id']);
            $table->index(['tenant_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materi');
    }
};
