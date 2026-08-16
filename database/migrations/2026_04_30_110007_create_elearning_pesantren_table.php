<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elearning_pesantren', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ustadz_id')->constrained('ustadz')->cascadeOnDelete();
            // Pesantren elearning doesn't use subject - uses kitab instead
            $table->string('kitab')->nullable();
            $table->string('materi')->nullable();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('file_path')->nullable();
            $table->string('link')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'kitab']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elearning_pesantren');
    }
};
