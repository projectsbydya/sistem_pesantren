<?php

/**
 * ARCHITECTURE FROZEN: Remove predikat column from diniyah_assessments
 *
 * Predikat is derived from score via DiniyahAssessment::hitungPredikat()
 * Storing it in DB is redundant and can cause data inconsistency.
 *
 * @frozen 2026-06-14
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diniyah_assessments', function (Blueprint $table) {
            $table->dropColumn('predikat');
        });
    }

    public function down(): void
    {
        Schema::table('diniyah_assessments', function (Blueprint $table) {
            $table->string('predikat', 2)->nullable()->after('score');
        });
    }
};
