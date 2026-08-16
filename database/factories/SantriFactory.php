<?php

namespace Database\Factories;

use App\Models\Santri;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class SantriFactory extends Factory
{
    protected $model = Santri::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'nis' => fake()->unique()->numerify('####'),
            'name' => fake()->name(),
            'gender' => fake()->randomElement(['L', 'P']),
            'birth_date' => fake()->date('Y-m-d', '-10 years'),
            'address' => fake()->address(),
            'status' => 'active',
            'school_level' => fake()->randomElement(['SD', 'SMP', 'SMA']),
            'wali_id' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenant->id,
        ]);
    }
}
