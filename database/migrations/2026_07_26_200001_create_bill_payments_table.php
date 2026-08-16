<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 30);
            $table->date('payment_date');
            $table->string('reference_number')->nullable();
            $table->string('transfer_proof')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'bill_id']);
            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['bill_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
    }
};
