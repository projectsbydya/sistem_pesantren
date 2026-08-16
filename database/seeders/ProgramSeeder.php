<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;

class ProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'slug'               => 'diniyah',
                'name'               => 'Diniyah',
                'description'        => 'Program pendidikan agama Islam secara formal dengan kurikulum diniyah terstruktur.',
                'is_active'          => true,
                'is_available_for_tenants' => true,   // Pack: production-ready
            ],
            [
                'slug'               => 'formal',
                'name'               => 'Formal',
                'description'        => 'Program pendidikan formal (SD/SMP/SMA sederajat) yang diselenggarakan di lingkungan pesantren.',
                'is_active'          => true,
                'is_available_for_tenants' => false,  // Pack: not yet implemented
            ],
            [
                'slug'               => 'pesantren',
                'name'               => 'Pesantren',
                'description'        => 'Program pendidikan pesantren klasik yang mencakup kehidupan pondok dan kajian kitab. (Digabung ke Pesantren Core — bukan program onboarding)',
                'is_active'          => false,
                'is_available_for_tenants' => false,  // Moved to Pesantren Core, not an onboarding program
            ],
            [
                'slug'               => 'pesantren-quran-tahfidz',
                'name'               => 'Pesantren Quran & Tahfidz',
                'description'        => 'Program pondok yang berfokus pada ilmu Al-Quran, tahfidz, tajwid, qiraah, dan tafsir dengan target capaian hafalan terstruktur.',
                'is_active'          => true,
                'is_available_for_tenants' => false,  // Pack: not yet implemented
            ],
            [
                'slug'               => 'salafiyah',
                'name'               => 'Salafiyah',
                'description'        => 'Program pendidikan berbasis metode salaf dengan fokus kajian kitab kuning dan ilmu agama klasik.',
                'is_active'          => true,
                'is_available_for_tenants' => false,  // Pack: not yet implemented
            ],
            [
                'slug'               => 'modern',
                'name'               => 'Modern',
                'description'        => 'Program pendidikan pesantren modern yang mengintegrasikan kurikulum agama dan umum secara terpadu.',
                'is_active'          => true,
                'is_available_for_tenants' => true,   // Pack: production-ready
            ],
            [
                'slug'               => 'terpadu',
                'name'               => 'Terpadu',
                'description'        => 'Program pendidikan terpadu yang mengombinasikan kurikulum nasional dengan pendidikan agama Islam secara intensif.',
                'is_active'          => true,
                'is_available_for_tenants' => false,  // Pack: not yet implemented
            ],
        ];

        foreach ($programs as $program) {
            Program::updateOrCreate(
                ['slug' => $program['slug']],
                [
                    'name'               => $program['name'],
                    'description'        => $program['description'],
                    'is_active'          => $program['is_active'],
                    'is_available_for_tenants' => $program['is_available_for_tenants'],
                ]
            );
        }
    }
}
