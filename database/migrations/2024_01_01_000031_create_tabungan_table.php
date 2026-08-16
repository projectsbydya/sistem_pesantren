<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tabungan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->enum('jenis', ['setor', 'tarik'])->comment('setor = menabung, tarik = penarikan');
            $table->decimal('jumlah', 12, 2);
            $table->date('tanggal');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id']);
            $table->index(['santri_id']);
            $table->index(['tanggal']);
            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tabungan');
    }
};
