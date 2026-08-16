<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('hafalan_nilai');
    }

    public function down(): void
    {
        // Intentionally left blank — old combined table replaced by modular tables
    }
};
