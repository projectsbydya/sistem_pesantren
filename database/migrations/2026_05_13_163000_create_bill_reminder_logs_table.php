<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->date('reminder_date');
            $table->timestamps();

            // Prevent duplicate reminders on same day
            $table->unique(['bill_id', 'reminder_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_reminder_logs');
    }
};
