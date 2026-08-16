<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Backfill raport_nilai_components from the legacy fixed columns on
     * raport_nilai. This migration is idempotent: existing rows are skipped.
     *
     * Legacy assessment type codes are used only to map historical data to
     * the assessment type registry. Weight/contribution are left null because
     * they will be populated by the raport service when it consumes the
     * program_assessment_configs registry.
     */
    public function up(): void
    {
        if (! Schema::hasTable('raport_nilai_components')) {
            return;
        }

        $typeCodes = ['harian', 'uts', 'uas', 'praktik'];
        $columnMap = [
            'harian'  => 'nilai_harian',
            'uts'     => 'nilai_uts',
            'uas'     => 'nilai_uas',
            'praktik' => 'nilai_praktik',
        ];

        $types = DB::table('assessment_types')
            ->whereIn('code', $typeCodes)
            ->get(['id', 'code', 'label'])
            ->keyBy('code');

        if ($types->isEmpty()) {
            return;
        }

        $now = now();

        foreach ($columnMap as $code => $column) {
            if (! isset($types[$code])) {
                continue;
            }

            $type = $types[$code];

            DB::insert(
                "INSERT INTO raport_nilai_components
                    (tenant_id, raport_nilai_id, assessment_type_id, assessment_code, assessment_label, score, weight, contribution, created_at, updated_at)
                 SELECT
                    rn.tenant_id,
                    rn.id,
                    ?,
                    ?,
                    ?,
                    rn.{$column},
                    NULL,
                    NULL,
                    ?,
                    ?
                 FROM raport_nilai rn
                 WHERE rn.{$column} IS NOT NULL
                   AND NOT EXISTS (
                       SELECT 1
                       FROM raport_nilai_components rnc
                       WHERE rnc.tenant_id = rn.tenant_id
                         AND rnc.raport_nilai_id = rn.id
                         AND rnc.assessment_code = ?
                   )",
                [$type->id, $type->code, $type->label, $now, $now, $type->code]
            );
        }
    }

    /**
     * No-op: the table created by the preceding migration will be dropped
     * on rollback, making row-level deletion unnecessary.
     */
    public function down(): void
    {
        //
    }
};
