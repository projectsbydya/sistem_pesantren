<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('event_name'); // login, create_santri, payment, dll
            $table->text('value')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id']);
            $table->index(['event_name']);
            $table->index(['created_at']);
            $table->index(['tenant_id', 'event_name']);
            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_logs');
    }
};
