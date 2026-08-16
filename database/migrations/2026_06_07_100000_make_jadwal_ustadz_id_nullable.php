<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make jadwal.ustadz_id nullable to support onboarding jadwal creation
     * before ustadz exists. Jadwal (slot waktu) and ustadz assignment
     * are separate concerns - ustadz can be assigned later via Penugasan.
     */
    public function up(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            // Drop existing foreign key first
            $table->dropForeign(['ustadz_id']);
            // Make nullable
            $table->unsignedBigInteger('ustadz_id')->nullable()->change();
            // Recreate FK with nullOnDelete
            $table->foreign('ustadz_id')->references('id')->on('ustadz')->nullOnDelete();
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->dropForeign(['ustadz_id']);
            $table->unsignedBigInteger('ustadz_id')->nullable(false)->change();
            $table->foreign('ustadz_id')->references('id')->on('ustadz')->cascadeOnDelete();
        });
    }
};
