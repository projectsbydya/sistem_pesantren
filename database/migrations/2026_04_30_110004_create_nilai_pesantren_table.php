<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_pesantren', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            // Pesantren doesn't use subjects - uses kitab and materi
            $table->string('kitab')->nullable();
            $table->string('materi')->nullable();
            $table->foreignId('ustadz_id')->nullable()->constrained('ustadz')->nullOnDelete();
            $table->date('tanggal');
            $table->decimal('nilai', 5, 2);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'santri_id', 'tanggal']);
            $table->index(['kitab']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_pesantren');
    }
};
