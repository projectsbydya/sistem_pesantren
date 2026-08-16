<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProgramAssessmentConfigBackfillSeeder extends Seeder
{
    /**
     * Backfill program_assessment_configs for every tenant+program pair.
     *
     * Creates one active config row per assessment_type if it does not exist.
     */
    public function run(): void
    {
        $types = DB::table('assessment_types')
            ->orderBy('id')
            ->get(['id', 'code']);

        if ($types->isEmpty()) {
            return;
        }

        $pairs = DB::table('tenant_programs')
            ->select('tenant_id', 'program_id')
            ->distinct()
            ->get();

        foreach ($pairs as $pair) {
            foreach ($types as $index => $type) {
                DB::table('program_assessment_configs')->updateOrInsert(
                    [
                        'tenant_id'          => $pair->tenant_id,
                        'program_id'         => $pair->program_id,
                        'assessment_type_id' => $type->id,
                    ],
                    [
                        'weight'      => null,
                        'sort_order'  => $index,
                        'is_active'   => true,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]
                );
            }
        }
    }
}
