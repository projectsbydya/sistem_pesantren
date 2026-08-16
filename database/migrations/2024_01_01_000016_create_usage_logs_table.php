<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('feature_name');
            $table->integer('usage_count')->default(0);
            $table->date('date');
            $table->timestamps();
            
            $table->unique(['tenant_id', 'feature_name', 'date']);
            $table->index(['tenant_id']);
            $table->index(['feature_name']);
            $table->index(['date']);
            $table->index(['tenant_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_logs');
    }
};
