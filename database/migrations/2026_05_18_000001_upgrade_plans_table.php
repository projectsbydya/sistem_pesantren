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
        Schema::dropIfExists('plans');
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->unsignedInteger('trial_days')->default(14);
            $table->unsignedInteger('santri_limit')->default(0);
            $table->unsignedInteger('user_limit')->default(0);
            $table->unsignedInteger('branch_limit')->default(1);
            $table->unsignedInteger('storage_limit_mb')->default(512);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('billing_cycle');
            $table->index('is_active');
            $table->index('price');
        });
    }

    private function upgradeForMySQL(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('code')->nullable()->after('name');
            $table->text('description')->nullable()->after('code');
            $table->unsignedInteger('trial_days')->default(14)->after('billing_cycle');
            $table->unsignedInteger('user_limit')->default(0)->after('santri_limit');
            $table->unsignedInteger('storage_limit_mb')->default(512)->after('branch_limit');

            $table->unique('code');
        });

        // Back-fill code from name for existing rows
        DB::table('plans')->get()->each(function ($plan) {
            DB::table('plans')->where('id', $plan->id)->update([
                'code' => \Illuminate\Support\Str::slug($plan->name . '-' . $plan->id),
            ]);
        });

        // Now make code non-nullable
        Schema::table('plans', function (Blueprint $table) {
            $table->string('code')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('plans', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn(['code', 'description', 'trial_days', 'user_limit', 'storage_limit_mb']);
        });
    }
};
