<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('santri_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('method', ['cash', 'transfer', 'qris', 'ewallet', 'lainnya']);
            $table->string('reference_id')->nullable();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['bill_id']);
            $table->index(['method']);
            $table->index(['payment_date']);
            $table->index(['reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri_payments');
    }
};
