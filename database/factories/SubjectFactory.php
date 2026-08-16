<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\Subject;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    protected $model = Subject::class;

    public function definition(): array
    {
        return [
            'tenant_id'   => Tenant::factory(),
            'program_id'  => Program::factory(),
            'name'        => fake()->unique()->words(2, true),
            'code'        => strtoupper(fake()->lexify('???')),
            'description' => fake()->sentence(),
        ];
    }
}
