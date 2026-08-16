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
            $this->recreateForSQLite();
        } else {
            $this->upgradeForMySQL();
        }
    }

    private function recreateForSQLite(): void
    {
        $existing = DB::table('subscriptions')->get();

        Schema::dropIfExists('subscriptions');

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('plan_id')->nullable()->constrained('plans')->onDelete('set null');
            $table->string('package_name');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->enum('status', ['trial', 'active', 'suspended', 'expired', 'cancelled'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('grace_period_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('plan_id');
            $table->index('status');
            $table->index('starts_at');
            $table->index('ends_at');
            $table->index('trial_ends_at');
            $table->index('grace_period_ends_at');
            $table->index('cancelled_at');
            $table->index(['tenant_id', 'status', 'ends_at']);
        });

        // Re-insert existing data (without plan_id and cancelled_at)
        foreach ($existing as $row) {
            DB::table('subscriptions')->insert([
                'id'                    => $row->id,
                'tenant_id'             => $row->tenant_id,
                'plan_id'               => null,
                'package_name'          => $row->package_name,
                'billing_cycle'         => $row->billing_cycle,
                'amount'                => $row->amount,
                'starts_at'             => $row->starts_at,
                'ends_at'               => $row->ends_at,
                'status'                => $row->status,
                'trial_ends_at'         => $row->trial_ends_at,
                'grace_period_ends_at'  => $row->grace_period_ends_at,
                'cancelled_at'          => null,
                'created_at'            => $row->created_at,
                'updated_at'            => $row->updated_at,
            ]);
        }
    }

    private function upgradeForMySQL(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('tenant_id')
                ->constrained('plans')->onDelete('set null');
            $table->timestamp('cancelled_at')->nullable()->after('grace_period_ends_at');

            $table->index('plan_id');
            $table->index('cancelled_at');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropIndex(['plan_id']);
            $table->dropIndex(['cancelled_at']);
            $table->dropColumn(['plan_id', 'cancelled_at']);
        });
    }
};
