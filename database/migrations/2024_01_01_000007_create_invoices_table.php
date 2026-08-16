<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['unpaid', 'paid', 'failed'])->default('unpaid');
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id']);
            $table->index(['subscription_id']);
            $table->index(['invoice_number']);
            $table->index(['status']);
            $table->index(['due_date']);
            $table->index(['paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
