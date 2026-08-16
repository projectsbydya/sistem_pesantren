<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_karakter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->string('aspek'); // akhlak, disiplin, tanggung_jawab, etc
            $table->integer('skor'); // 1-100
            $table->string('predikat')->nullable(); // sangat_baik, baik, cukup, kurang
            $table->text('deskripsi')->nullable();
            $table->date('tanggal');
            $table->string('periode')->nullable(); // mingguan, bulanan, semester
            $table->foreignId('dinilai_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'aspek']);
            $table->index(['tenant_id', 'tanggal']);
            $table->index(['tenant_id', 'periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_karakter');
    }
};
