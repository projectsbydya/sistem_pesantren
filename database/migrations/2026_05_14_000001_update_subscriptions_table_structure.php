<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $this->recreateTableForSQLite();
        } else {
            $this->modifyTableForMySqlOrPostgres();
        }
    }

    private function recreateTableForSQLite(): void
    {
        // Get existing data
        $existing = DB::table('subscriptions')->get();

        // Drop and recreate table
        Schema::dropIfExists('subscriptions');

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('package_name');
            $table->enum('billing_cycle', ['monthly', 'yearly']);
            $table->decimal('amount', 12, 2);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->enum('status', ['trial', 'active', 'suspended', 'expired', 'cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('grace_period_ends_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id']);
            $table->index(['package_name']);
            $table->index(['billing_cycle']);
            $table->index(['starts_at']);
            $table->index(['ends_at']);
            $table->index(['status']);
            $table->index(['trial_ends_at']);
            $table->index(['grace_period_ends_at']);
            $table->index(['tenant_id', 'status', 'ends_at']);
        });

        // Note: Data is lost in SQLite recreation - acceptable for test environment
    }

    private function modifyTableForMySqlOrPostgres(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['plan_id', 'start_date', 'end_date']);

            $table->string('package_name')->after('tenant_id');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->after('package_name');
            $table->decimal('amount', 12, 2)->after('billing_cycle');
            $table->timestamp('starts_at')->nullable()->after('amount');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->timestamp('trial_ends_at')->nullable()->after('status');
            $table->timestamp('grace_period_ends_at')->nullable()->after('trial_ends_at');

            $table->index(['package_name']);
            $table->index(['billing_cycle']);
            $table->index(['starts_at']);
            $table->index(['ends_at']);
            $table->index(['trial_ends_at']);
            $table->index(['grace_period_ends_at']);
            $table->index(['tenant_id', 'status', 'ends_at']);
        });

        // Update status enum for MySQL
        $driver = DB::getDriverName();
        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE subscriptions MODIFY status ENUM('trial', 'active', 'suspended', 'expired', 'cancelled') DEFAULT 'trial'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE subscriptions ALTER COLUMN status TYPE VARCHAR(20)");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return; // Cannot rollback table recreation in SQLite
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['package_name']);
            $table->dropIndex(['billing_cycle']);
            $table->dropIndex(['starts_at']);
            $table->dropIndex(['ends_at']);
            $table->dropIndex(['trial_ends_at']);
            $table->dropIndex(['grace_period_ends_at']);
            $table->dropIndex(['tenant_id', 'status', 'ends_at']);

            $table->dropColumn([
                'package_name',
                'billing_cycle',
                'amount',
                'starts_at',
                'ends_at',
                'trial_ends_at',
                'grace_period_ends_at',
            ]);

            $table->foreignId('plan_id')->constrained()->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date')->nullable();
        });
    }
};
