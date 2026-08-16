<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Set is_available_for_tenants = true for production-ready program packs.
     *
     * Idempotent: safe to run multiple times. Only updates rows matching the given slugs.
     */
    public function up(): void
    {
        DB::table('programs')
            ->whereIn('slug', ['diniyah', 'modern'])
            ->update(['is_available_for_tenants' => true]);
    }

    /**
     * No-op: rolling back a data correction should not blindly set false,
     * as the column may have been updated by subsequent migrations or admin actions.
     */
    public function down(): void
    {
        // intentionally empty
    }
};
