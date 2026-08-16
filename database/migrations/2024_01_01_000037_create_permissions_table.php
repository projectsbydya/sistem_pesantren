<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table was never used by any model; conflicts with Spatie permissions schema.
        // Superseded by 2026_05_08_000000_create_permission_tables (Spatie schema)
    }

    public function down(): void
    {
        // Superseded by 2026_05_08_000000_create_permission_tables (Spatie schema)
    }
};
