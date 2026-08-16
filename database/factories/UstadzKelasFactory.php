<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\Ustadz;
use App\Models\UstadzKelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UstadzKelas>
 */
class UstadzKelasFactory extends Factory
{
    protected $model = UstadzKelas::class;

    public function definition(): array
    {
        return [
            'tenant_id'  => Tenant::factory(),
            'program_id' => Program::factory(),
            'ustadz_id'  => Ustadz::factory(),
            'kelas_id'   => Kelas::factory(),
            'subject_id' => Subject::factory(),
        ];
    }
}
