<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            // Add parent_id for primary parent reference
            $table->foreignId('parent_id')
                ->nullable()
                ->after('wali_id')
                ->constrained('parents')
                ->nullOnDelete();
            
            // Add user_id for santri login account
            $table->foreignId('user_id')
                ->nullable()
                ->after('parent_id')
                ->constrained("users")
                ->nullOnDelete();
                
            $table->index(['parent_id']);
            $table->index(['user_id']);
            $table->index(['tenant_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::table('santri', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['parent_id', 'user_id']);
        });
    }
};
