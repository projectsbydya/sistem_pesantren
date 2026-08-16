<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('ustadz_id');
            $table->string('mata_pelajaran');
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Ahad']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('kelas');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('ustadz_id')->references('id')->on('ustadz')->cascadeOnDelete();

            $table->index(['tenant_id', 'hari']);
            $table->index(['tenant_id', 'ustadz_id', 'hari']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};
