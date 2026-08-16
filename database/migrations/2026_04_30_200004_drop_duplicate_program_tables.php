<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('elearning_pesantren');
        Schema::dropIfExists('elearning_diniyah');
        Schema::dropIfExists('nilai_pesantren');
        Schema::dropIfExists('nilai_diniyah');
        Schema::dropIfExists('absensi_pesantren');
        Schema::dropIfExists('absensi_diniyah');
    }

    public function down(): void
    {
        // Tables dropped intentionally; restore manually if needed.
        // Refer to migrations 2026_04_30_110000 through 2026_04_30_110008.
    }
};
