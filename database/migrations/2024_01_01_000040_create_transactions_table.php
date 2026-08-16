<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_id')->constrained('santri')->onDelete('cascade');
            $table->decimal('total', 10, 2);
            $table->date('date');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['santri_id']);
            $table->index(['date']);
            $table->index(['status']);
            $table->index(['cashier_id']);
            $table->index(['santri_id', 'date']);
            $table->index(['santri_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
