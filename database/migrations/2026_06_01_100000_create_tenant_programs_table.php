<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Table ini menyimpan program-program yang dipilih/digunakan oleh tenant.
     * Program adalah master global, tenant hanya memilih yang akan digunakan.
     */
    public function up(): void
    {
        Schema::create('tenant_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('activated_at')->nullable();
            $table->json('settings')->nullable(); // Untuk custom settings per tenant per program
            $table->timestamps();

            $table->unique(['tenant_id', 'program_id']);
            $table->index(['tenant_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_programs');
    }
};
