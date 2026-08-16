<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('capacity')->default(0);
            $table->text('description')->nullable();
            $table->string('building')->nullable();
            $table->timestamps();
            
            $table->unique(['tenant_id', 'name']);
            $table->index(['tenant_id']);
            $table->index(['name']);
            $table->index(['building']);
            $table->index(['capacity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
