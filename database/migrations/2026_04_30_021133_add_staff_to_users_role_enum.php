<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite (used in tests) stores enums as VARCHAR — no ALTER needed.
        // MySQL/MariaDB requires explicit MODIFY COLUMN to expand the enum set.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','parent','student','ustadz') NOT NULL DEFAULT 'admin'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','parent','student') NOT NULL DEFAULT 'admin'");
        }
    }
};
