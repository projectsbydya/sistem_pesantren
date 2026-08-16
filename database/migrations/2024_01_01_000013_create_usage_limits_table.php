<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('feature_name');
            $table->integer('limit_value')->default(0);
            $table->timestamps();
            
            $table->unique(['plan_id', 'feature_name']);
            $table->index(['plan_id']);
            $table->index(['feature_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_limits');
    }
};
