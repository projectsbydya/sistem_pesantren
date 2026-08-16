<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = fake()->randomElement(['Starter', 'Basic', 'Standard', 'Professional', 'Enterprise']);
        $name = $name . ' ' . fake()->randomNumber(3, false);

        return [
            'name'             => $name,
            'code'             => Str::slug($name),
            'description'      => fake()->sentence(),
            'price'            => fake()->randomElement([99000, 199000, 299000, 499000, 999000]),
            'billing_cycle'    => fake()->randomElement(['monthly', 'yearly']),
            'trial_days'       => 14,
            'santri_limit'     => fake()->randomElement([50, 100, 200, 500, 0]),
            'user_limit'       => fake()->randomElement([10, 20, 50, 100, 0]),
            'branch_limit'     => fake()->randomElement([1, 3, 5, 0]),
            'storage_limit_mb' => fake()->randomElement([512, 1024, 2048, 5120]),
            'features'         => [
                'spp'              => true,
                'tabungan'         => true,
                'hafalan'          => true,
                'elearning'        => fake()->boolean(),
                'live_pengajian'   => fake()->boolean(),
                'multi_program'    => fake()->boolean(),
            ],
            'is_active'        => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function monthly(): static
    {
        return $this->state(fn () => ['billing_cycle' => 'monthly']);
    }

    public function yearly(): static
    {
        return $this->state(fn () => [
            'billing_cycle' => 'yearly',
            'price'         => fake()->randomElement([990000, 1990000, 2990000]),
        ]);
    }

    public function free(): static
    {
        return $this->state(fn () => [
            'price'        => 0,
            'trial_days'   => 0,
            'santri_limit' => 25,
            'user_limit'   => 5,
        ]);
    }

    public function unlimited(): static
    {
        return $this->state(fn () => [
            'santri_limit'     => 0,
            'user_limit'       => 0,
            'branch_limit'     => 0,
            'storage_limit_mb' => 0,
        ]);
    }
}
