<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite (used in tests) stores enums as VARCHAR — no ALTER needed.
        // MySQL/MariaDB requires explicit MODIFY COLUMN to expand the enum set.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','parent','student','ustadz','bendahara') NOT NULL DEFAULT 'admin'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','parent','student','ustadz') NOT NULL DEFAULT 'admin'");
        }
    }
};
