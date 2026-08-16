<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diniyah_hafalan_hadits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('program_id')->index();
            $table->unsignedBigInteger('santri_id')->index();
            $table->unsignedBigInteger('ustadz_kelas_id')->nullable()->index();
            $table->string('judul_hadits', 200);
            $table->text('matan_hadits')->nullable();
            $table->string('perawi', 100)->nullable();
            $table->date('tanggal');
            $table->float('nilai')->nullable();
            $table->string('status', 20)->default('belum');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('santri_id')->references('id')->on('santri')->cascadeOnDelete();
            $table->foreign('ustadz_kelas_id')->references('id')->on('ustadz_kelas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diniyah_hafalan_hadits');
    }
};
