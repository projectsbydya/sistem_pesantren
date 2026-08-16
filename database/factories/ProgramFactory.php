<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Program;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => fn (array $attributes) => str($attributes['name'])->slug()->toString(),
            'description' => fake()->sentence(),
            'is_active' => true,
            'is_available_for_tenants' => true,
        ];
    }
}
