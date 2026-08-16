<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('absensi', 'absensi_santri');
    }

    public function down(): void
    {
        Schema::rename('absensi_santri', 'absensi');
    }
};
