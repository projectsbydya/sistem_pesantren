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
        Schema::dropIfExists('invoices');

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['unpaid', 'paid', 'failed', 'cancelled'])->default('unpaid');
            $table->date('due_date');
            $table->string('period_label')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('subscription_id');
            $table->index('invoice_number');
            $table->index('status');
            $table->index('due_date');
            $table->index('paid_at');
        });
    }

    private function upgradeForMySQL(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('period_label')->nullable()->after('status');
            $table->text('notes')->nullable()->after('period_label');
        });

        // Update status enum for MySQL
        DB::statement("ALTER TABLE invoices MODIFY status ENUM('unpaid','paid','failed','cancelled') DEFAULT 'unpaid'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['period_label', 'notes']);
        });

        DB::statement("ALTER TABLE invoices MODIFY status ENUM('unpaid','paid','failed') DEFAULT 'unpaid'");
    }
};
