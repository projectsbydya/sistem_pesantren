<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->date('date');
            $table->enum('type', ['akademik', 'kegiatan', 'libur', 'lainnya']);
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->time('time')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['tenant_id']);
            $table->index(['title']);
            $table->index(['date']);
            $table->index(['type']);
            $table->index(['is_active']);
            $table->index(['created_by']);
            $table->index(['tenant_id', 'date']);
            $table->index(['tenant_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
