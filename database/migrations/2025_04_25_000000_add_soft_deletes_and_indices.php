<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambah soft deletes dan index untuk sistem user management
     */
    public function up(): void
    {
        // Index untuk performa query user lookup
        Schema::table('users', function (Blueprint $table) {
            $table->index(['tenant_id', 'role', 'is_active']);
            $table->index(['email', 'is_active']);
        });

        // Pastikan santri bisa dicari by user_id efisien
        Schema::table('santri', function (Blueprint $table) {
            if (!Schema::hasColumn('santri', 'deleted_at')) {
                $table->softDeletes();
            }
            $table->index(['user_id', 'tenant_id']);
        });

        // Pastikan parents bisa dicari by user_id efisien
        Schema::table('parents', function (Blueprint $table) {
            if (!Schema::hasColumn('parents', 'deleted_at')) {
                $table->softDeletes();
            }
            $table->index(['user_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'role', 'is_active']);
            $table->dropIndex(['email', 'is_active']);
        });

        Schema::table('santri', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['user_id', 'tenant_id']);
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropIndex(['user_id', 'tenant_id']);
        });
    }
};
