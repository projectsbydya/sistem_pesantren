<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('reminder_type', 30)->default('trial_expiring'); // trial_expiring|sub_expiring|sub_expired
            $table->integer('days_before')->default(0); // days before expiration (0 = expired notification)
            $table->date('reminder_date'); // Date reminder was sent
            $table->timestamps();

            // Unique constraint to prevent duplicate reminders per day
            $table->unique(
                ['subscription_id', 'reminder_type', 'days_before', 'reminder_date'],
                'subs_reminder_unique'
            );

            // Indexes
            $table->index(['tenant_id'], 'subs_reminder_tenant_idx');
            $table->index(['reminder_date'], 'subs_reminder_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_reminder_logs');
    }
};
