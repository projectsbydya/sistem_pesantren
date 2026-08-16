<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Basic / Pro / Enterprise
            $table->decimal('price', 10, 2);
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->integer('santri_limit')->default(0);
            $table->integer('branch_limit')->default(1);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['name']);
            $table->index(['billing_cycle']);
            $table->index(['is_active']);
            $table->index(['price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
