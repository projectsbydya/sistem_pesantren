<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Already migrated — skip idempotently
        if (Schema::hasIndex('santri_programs', 'santri_programs_tenant_id_santri_id_program_unique')) {
            return;
        }

        Schema::table('santri_programs', function (Blueprint $table) {
            // Step 1: Drop the santri_id FK first.
            // MySQL cannot drop an index that backs a FK constraint.
            // The old unique(santri_id, program) was created before the FK, so MySQL
            // uses it as the backing index for the santri_id FK — we must drop FK first.
            $table->dropForeign(['santri_id']);

            // Step 2: Drop the old non-tenant-aware unique index.
            if (Schema::hasIndex('santri_programs', 'santri_programs_santri_id_program_unique')) {
                $table->dropUnique('santri_programs_santri_id_program_unique');
            }

            // Step 3: Add the new tenant-aware unique constraint.
            $table->unique(['tenant_id', 'santri_id', 'program'], 'santri_programs_tenant_id_santri_id_program_unique');

            // Step 4: Recreate the santri_id FK (now backed by the new composite unique index).
            $table->foreign('santri_id')
                ->references('id')
                ->on('santri')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('santri_programs', function (Blueprint $table) {
            // Drop FK before touching indexes
            $table->dropForeign(['santri_id']);

            // Drop the tenant-aware unique
            if (Schema::hasIndex('santri_programs', 'santri_programs_tenant_id_santri_id_program_unique')) {
                $table->dropUnique('santri_programs_tenant_id_santri_id_program_unique');
            }

            // Restore original non-tenant-aware unique
            $table->unique(['santri_id', 'program'], 'santri_programs_santri_id_program_unique');

            // Recreate original FK
            $table->foreign('santri_id')
                ->references('id')
                ->on('santri')
                ->cascadeOnDelete();
        });
    }
};