<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support multiple ALTER commands well, so we recreate
        if (DB::getDriverName() === 'sqlite') {
            $this->recreateForSQLite();
            return;
        }

        // MySQL/MariaDB: drop and recreate with new schema
        Schema::dropIfExists('usage_logs');

        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('metric', 50); // user_count, santri_count, branch_count, storage_usage_mb
            $table->decimal('value', 15, 2); // Support large numbers with decimals for storage
            $table->timestamp('recorded_at');
            $table->json('metadata')->nullable(); // Context like triggered_by, ip, etc.
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['tenant_id', 'metric', 'recorded_at']);
            $table->index(['metric', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_logs');

        // Recreate original schema
        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('feature_name');
            $table->integer('usage_count')->default(0);
            $table->date('date');
            $table->timestamps();

            $table->unique(['tenant_id', 'feature_name', 'date']);
            $table->index(['tenant_id']);
            $table->index(['feature_name']);
            $table->index(['date']);
            $table->index(['tenant_id', 'date']);
        });
    }

    private function recreateForSQLite(): void
    {
        // Get existing data (if any)
        $existing = DB::table('usage_logs')->get();

        // Drop and recreate
        Schema::dropIfExists('usage_logs');

        Schema::create('usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('metric', 50);
            $table->decimal('value', 15, 2);
            $table->timestamp('recorded_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'metric', 'recorded_at']);
            $table->index(['metric', 'recorded_at']);
        });

        // Note: Old data is incompatible, so we don't restore it
    }
};
