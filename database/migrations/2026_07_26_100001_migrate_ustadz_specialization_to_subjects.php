<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('subject_ustadz') || !Schema::hasColumn('ustadz', 'specialization')) {
            return;
        }

        $activeProgramIdsByTenant = [];
        $matched = 0;
        $unmatched = 0;

        DB::table('ustadz')
            ->select(['id', 'tenant_id', 'specialization'])
            ->whereNotNull('specialization')
            ->where('specialization', '!=', '')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use (&$activeProgramIdsByTenant, &$matched, &$unmatched) {
                foreach ($rows as $row) {
                    $tenantId = (int) $row->tenant_id;

                    if (!isset($activeProgramIdsByTenant[$tenantId])) {
                        $activeProgramIdsByTenant[$tenantId] = DB::table('tenant_programs')
                            ->where('tenant_id', $tenantId)
                            ->where('is_active', true)
                            ->pluck('program_id')
                            ->all();
                    }

                    $subject = DB::table('subjects')
                        ->where('tenant_id', $tenantId)
                        ->where('name', $row->specialization)
                        ->first();

                    if ($subject && in_array((int) $subject->program_id, $activeProgramIdsByTenant[$tenantId], true)) {
                        DB::table('subject_ustadz')->insertOrIgnore([
                            'ustadz_id' => (int) $row->id,
                            'subject_id' => (int) $subject->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        $matched++;
                    } else {
                        Log::warning('Ustadz specialization migration: no matching active subject', [
                            'ustadz_id' => (int) $row->id,
                            'tenant_id' => $tenantId,
                            'specialization' => $row->specialization,
                        ]);
                        $unmatched++;
                    }
                }
            });

        Log::info('Ustadz specialization migration completed', [
            'matched' => $matched,
            'unmatched' => $unmatched,
        ]);
    }

    public function down(): void
    {
        // Irreversible: this migration only copied legacy data into subject_ustadz.
        // Rolling back would not safely restore any state, and must not delete
        // production data that may have been created after this migration ran.
    }
};
