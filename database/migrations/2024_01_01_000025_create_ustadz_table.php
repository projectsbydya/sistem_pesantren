<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ustadz', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('specialization')->nullable();
            $table->text('bio')->nullable();
            $table->timestamps();
            
            // Note: user_id is nullable (ustadz may not have login account yet).
            // MySQL UNIQUE with nullable column allows multiple NULLs (each NULL is distinct),
            // so this constraint correctly prevents duplicate user_id per tenant when set.
            $table->unique(['tenant_id', 'user_id']);
            $table->index(['tenant_id']);
            $table->index(['user_id']);
            $table->index(['specialization']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ustadz');
    }
};
