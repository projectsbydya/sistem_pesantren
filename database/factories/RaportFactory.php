<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Program;
use App\Models\Raport;
use App\Models\Santri;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Raport>
 */
class RaportFactory extends Factory
{
    protected $model = Raport::class;

    public function definition(): array
    {
        return [
            'tenant_id'        => Tenant::factory(),
            'program_id'       => Program::factory(),
            'santri_id'        => Santri::factory(),
            'kelas_id'         => Kelas::factory(),
            'semester'         => fake()->randomElement(['ganjil', 'genap']),
            'tahun_ajaran'     => fake()->year() . '/' . (fake()->year() + 1),
            'status'           => fake()->randomElement(['draft', 'published', 'archived']),
            'total_hari_efektif' => fake()->numberBetween(1, 100),
            'sakit'            => fake()->numberBetween(0, 10),
            'izin'             => fake()->numberBetween(0, 10),
            'alpa'             => fake()->numberBetween(0, 10),
        ];
    }
}
