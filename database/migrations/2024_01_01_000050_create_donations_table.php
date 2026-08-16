<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('donor_name');
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->enum('type', ['uang', 'barang', 'jasa', 'lainnya']);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'received', 'confirmed'])->default('pending');
            $table->foreignId('received_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['tenant_id']);
            $table->index(['donor_name']);
            $table->index(['amount']);
            $table->index(['date']);
            $table->index(['type']);
            $table->index(['status']);
            $table->index(['received_by']);
            $table->index(['tenant_id', 'date']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
