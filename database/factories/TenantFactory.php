<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'name' => $name,
            'slug' => substr(preg_replace('/[^a-z0-9]+/', '-', strtolower(fake()->unique()->word())), 0, 30),
            'domain' => null,
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->email(),
            'logo' => null,
            'is_active' => true,
            'is_trial' => false,
            'trial_ends_at' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_trial' => true,
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function expiredTrial(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_trial' => true,
            'trial_ends_at' => now()->subDay(),
        ]);
    }
}
