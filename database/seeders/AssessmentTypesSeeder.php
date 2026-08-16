<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssessmentTypesSeeder extends Seeder
{
    /**
     * Seed the assessment type registry.
     */
    public function run(): void
    {
        $types = [
            ['code' => 'quiz',    'label' => 'Quiz'],
            ['code' => 'tugas',   'label' => 'Tugas'],
            ['code' => 'harian',  'label' => 'Harian'],
            ['code' => 'praktik', 'label' => 'Praktik'],
            ['code' => 'uts',     'label' => 'UTS'],
            ['code' => 'uas',     'label' => 'UAS'],
        ];

        foreach ($types as $type) {
            $existing = DB::table('assessment_types')->where('code', $type['code'])->first();

            if ($existing) {
                DB::table('assessment_types')
                    ->where('id', $existing->id)
                    ->update([
                        'label'      => $type['label'],
                        'is_active'  => true,
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table('assessment_types')->insert([
                'code'       => $type['code'],
                'label'      => $type['label'],
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
