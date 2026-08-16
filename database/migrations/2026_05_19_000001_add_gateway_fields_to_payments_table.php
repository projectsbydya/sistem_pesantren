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
        Schema::dropIfExists('payments');

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->nullable();
            $table->string('transfer_proof')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('external_id')->nullable();
            $table->string('payment_url')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('payment_method');
            $table->index('status');
            $table->index('reference_id');
            $table->index('external_id');
            $table->index('confirmed_by');
            $table->index('paid_at');
            $table->index('confirmed_at');
        });
    }

    private function upgradeForMySQL(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('external_id')->nullable()->after('reference_id');
            $table->string('payment_url')->nullable()->after('external_id');
            $table->index('external_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['external_id']);
            $table->dropColumn(['external_id', 'payment_url']);
        });
    }
};
