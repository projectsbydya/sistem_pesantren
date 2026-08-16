<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main Raport table
        Schema::create('raport', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('santri_id');
            $table->unsignedBigInteger('kelas_id');
            $table->enum('semester', ['ganjil', 'genap']);
            $table->string('tahun_ajaran');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->date('tanggal_diterbitkan')->nullable();
            $table->text('catatan_umum')->nullable();
            $table->integer('sakit')->default(0);
            $table->integer('izin')->default(0);
            $table->integer('alpa')->default(0);
            $table->integer('total_hari_efektif')->default(0);
            $table->string('kepala_pesantren')->nullable();
            $table->string('nip_kepala')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('santri_id')->references('id')->on('santri')->cascadeOnDelete();
            $table->foreign('kelas_id')->references('id')->on('kelas')->cascadeOnDelete();

            $table->index(['tenant_id', 'program_id']);
            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'semester', 'tahun_ajaran']);
            $table->unique(['tenant_id', 'santri_id', 'semester', 'tahun_ajaran'], 'unique_raport_per_semester');
        });

        // Raport Nilai Detail table
        Schema::create('raport_nilai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('raport_id');
            $table->unsignedBigInteger('subject_id');
            $table->decimal('nilai_harian', 5, 2)->nullable();
            $table->decimal('nilai_uts', 5, 2)->nullable();
            $table->decimal('nilai_uas', 5, 2)->nullable();
            $table->decimal('nilai_praktik', 5, 2)->nullable();
            $table->decimal('nilai_akhir', 5, 2)->default(0);
            $table->string('predikat', 2)->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('raport_id')->references('id')->on('raport')->cascadeOnDelete();
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();

            $table->index(['tenant_id', 'raport_id']);
            $table->unique(['raport_id', 'subject_id'], 'unique_nilai_per_subject');
        });

        // Raport Hafalan Detail table
        Schema::create('raport_hafalan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('raport_id');
            $table->enum('type', ['quran', 'kitab']);
            // Quran fields
            $table->string('surah_dari')->nullable();
            $table->integer('ayat_dari')->nullable();
            $table->string('surah_sampai')->nullable();
            $table->integer('ayat_sampai')->nullable();
            $table->integer('juz')->nullable();
            // Kitab fields
            $table->string('kitab')->nullable();
            $table->string('bab')->nullable();
            $table->integer('halaman')->nullable();
            // Summary
            $table->integer('total_hafalan')->default(0);
            $table->text('keterangan')->nullable();
            $table->string('predikat', 2)->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('raport_id')->references('id')->on('raport')->cascadeOnDelete();

            $table->index(['tenant_id', 'raport_id']);
            $table->index(['raport_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raport_hafalan');
        Schema::dropIfExists('raport_nilai');
        Schema::dropIfExists('raport');
    }
};
