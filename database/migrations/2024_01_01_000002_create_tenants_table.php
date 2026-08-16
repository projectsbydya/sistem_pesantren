<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('domain')->nullable()->unique();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('logo')->nullable();
            $table->string('plan')->default('trial');
            $table->string('status')->default('active');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_trial')->default(true);
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->index(['slug']);
            $table->index(['domain']);
            $table->index(['is_active']);
            $table->index(['is_trial']);
            $table->index(['trial_ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
