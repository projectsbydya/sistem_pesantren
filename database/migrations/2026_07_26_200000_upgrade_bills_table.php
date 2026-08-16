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
            $this->upgradeForSQLite();
        } else {
            $this->upgradeForMySQL();
        }
    }

    // =========================================================================
    // SQLite: Recreate table (SQLite cannot ALTER COLUMN)
    // =========================================================================

    private function upgradeForSQLite(): void
    {
        // 1. Save existing data
        $bills = DB::table('bills')->get();
        $reminderLogs = DB::table('bill_reminder_logs')->get();

        // 2. Drop dependent tables first (FK constraints)
        Schema::dropIfExists('bill_reminder_logs');
        Schema::dropIfExists('bills');

        // 3. Recreate bills with new schema
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('santri_id')->constrained('santri')->cascadeOnDelete();
            $table->string('type', 50);
            $table->decimal('amount', 10, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->date('due_date');
            $table->string('status', 20)->default('unpaid');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['tenant_id']);
            $table->index(['santri_id']);
            $table->index(['type']);
            $table->index(['status']);
            $table->index(['due_date']);
            $table->index(['tenant_id', 'santri_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['santri_id', 'status']);
            $table->index(['tenant_id', 'type', 'status']);
            $table->index(['tenant_id', 'due_date']);
        });

        // 4. Restore data with status migration (overdue → unpaid)
        foreach ($bills as $bill) {
            DB::table('bills')->insert([
                'id'          => $bill->id,
                'tenant_id'   => $bill->tenant_id,
                'santri_id'   => $bill->santri_id,
                'type'        => $bill->type,
                'amount'      => $bill->amount,
                'paid_amount' => 0,
                'due_date'    => $bill->due_date,
                'status'      => $bill->status === 'overdue' ? 'unpaid' : $bill->status,
                'description' => $bill->description,
                'created_at'  => $bill->created_at,
                'updated_at'  => $bill->updated_at,
            ]);
        }

        // 5. Recreate bill_reminder_logs
        Schema::create('bill_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $table->date('reminder_date');
            $table->timestamps();

            $table->unique(['bill_id', 'reminder_date']);
        });

        // Restore reminder logs
        foreach ($reminderLogs as $log) {
            DB::table('bill_reminder_logs')->insert([
                'id'            => $log->id,
                'bill_id'       => $log->bill_id,
                'reminder_date' => $log->reminder_date,
                'created_at'    => $log->created_at,
                'updated_at'    => $log->updated_at,
            ]);
        }
    }

    // =========================================================================
    // MySQL: ALTER in place
    // =========================================================================

    private function upgradeForMySQL(): void
    {
        // 1. Add paid_amount column
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('amount');
        });

        // 2. Convert type from enum to string(50)
        DB::statement("ALTER TABLE bills MODIFY `type` VARCHAR(50) NOT NULL");

        // 3. Convert status from enum to string(20), migrate 'overdue' → 'unpaid'
        DB::statement("UPDATE bills SET status = 'unpaid' WHERE status = 'overdue'");
        DB::statement("ALTER TABLE bills MODIFY `status` VARCHAR(20) NOT NULL DEFAULT 'unpaid'");

        // 5. Add new composite indexes (safe — these don't exist yet)
        Schema::table('bills', function (Blueprint $table) {
            $table->index(['tenant_id', 'type', 'status'], 'bills_tenant_type_status_index');
            $table->index(['tenant_id', 'due_date'], 'bills_tenant_due_date_index');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // SQLite rollback: not safe to reverse table recreation
            return;
        }

        // MySQL rollback
        Schema::table('bills', function (Blueprint $table) {
            $table->dropIndex('bills_tenant_type_status_index');
            $table->dropIndex('bills_tenant_due_date_index');
        });

        DB::statement("ALTER TABLE bills MODIFY `status` ENUM('unpaid','paid','overdue') NOT NULL DEFAULT 'unpaid'");
        DB::statement("ALTER TABLE bills MODIFY `type` ENUM('spp','uang_pangkal','uang_buku','uang_seragam','lainnya') NOT NULL");

        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn('paid_amount');
        });
    }
};
