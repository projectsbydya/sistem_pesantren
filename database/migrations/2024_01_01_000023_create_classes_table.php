<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('level')->nullable(); // Kelas 1, Kelas 2, etc
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->unique(['tenant_id', 'name']);
            $table->index(['tenant_id']);
            $table->index(['name']);
            $table->index(['level']);
            $table->index(['tenant_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
