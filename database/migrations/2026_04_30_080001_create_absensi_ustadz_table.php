<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_ustadz', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('ustadz_id')->constrained('ustadz')->onDelete('cascade');
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpa']);
            $table->text('catatan')->nullable();
            $table->timestamps();

            // One record per ustadz per date per tenant
            $table->unique(['tenant_id', 'ustadz_id', 'tanggal']);
            $table->index(['tenant_id']);
            $table->index(['ustadz_id', 'tanggal']);
            $table->index(['tenant_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_ustadz');
    }
};
