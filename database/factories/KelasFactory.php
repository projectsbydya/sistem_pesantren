<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Program;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kelas>
 */
class KelasFactory extends Factory
{
    protected $model = Kelas::class;

    public function definition(): array
    {
        return [
            'tenant_id'   => Tenant::factory(),
            'program_id'  => Program::factory(),
            'name'        => 'Kelas ' . fake()->randomNumber(1, true),
            'description' => fake()->sentence(),
        ];
    }
}
