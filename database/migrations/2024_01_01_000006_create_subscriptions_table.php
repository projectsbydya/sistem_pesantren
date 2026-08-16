<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->constrained()->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['trial', 'active', 'expired', 'cancelled'])->default('trial');
            $table->timestamps();
            
            $table->index(['tenant_id']);
            $table->index(['plan_id']);
            $table->index(['status']);
            $table->index(['start_date']);
            $table->index(['end_date']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
