<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained("users")
                ->nullOnDelete();
            
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->enum('relationship', ['father', 'mother', 'guardian'])->default('father');
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index(['tenant_id']);
            $table->index(['tenant_id', 'user_id']);
            $table->index(['phone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
