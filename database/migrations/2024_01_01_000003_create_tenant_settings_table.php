<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('app_name')->default('Sistem Pesantren');
            $table->string('logo')->nullable();
            $table->string('primary_color')->default('#3B82F6');
            $table->string('timezone')->default('Asia/Jakarta');
            $table->string('currency')->default('IDR');
            $table->timestamps();
            
            $table->unique(['tenant_id']);
            $table->index(['tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
