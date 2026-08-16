<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add is_available_for_tenants flag to programs.
     *
     * Controls whether a program appears in the onboarding program-selection UI.
     * Schema-only: initial data is seeded by ProgramSeeder.
     * The 7 canonical programs remain in the DB regardless of this flag.
     */
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->boolean('is_available_for_tenants')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn('is_available_for_tenants');
        });
    }
};
