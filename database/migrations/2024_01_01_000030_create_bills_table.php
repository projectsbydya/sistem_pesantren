<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->enum('type', ['spp', 'uang_pangkal', 'uang_buku', 'uang_seragam', 'lainnya']);
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->enum('status', ['unpaid', 'paid', 'overdue'])->default('unpaid');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id']);
            $table->index(['santri_id']);
            $table->index(['type']);
            $table->index(['status']);
            $table->index(['due_date']);
            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['santri_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
