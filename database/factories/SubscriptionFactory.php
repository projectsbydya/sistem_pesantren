<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'tenant_id'             => Tenant::factory(),
            'plan_id'               => Plan::factory(),
            'package_name'          => fake()->randomElement(['Basic', 'Standard', 'Premium', 'Enterprise']),
            'billing_cycle'         => fake()->randomElement(['monthly', 'yearly']),
            'amount'                => fake()->randomFloat(2, 100000, 2000000),
            'starts_at'             => now(),
            'ends_at'               => now()->addMonth(),
            'status'                => 'active',
            'trial_ends_at'         => null,
            'grace_period_ends_at'  => null,
            'cancelled_at'          => null,
        ];
    }

    public function trial(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'trial',
            'starts_at' => null,
            'ends_at' => null,
            'trial_ends_at' => now()->addDays(14),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'trial_ends_at' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subDay(),
            'trial_ends_at' => null,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
            'starts_at' => now()->subWeek(),
            'ends_at' => now()->addWeeks(3),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'       => 'cancelled',
            'starts_at'    => now()->subMonths(2),
            'ends_at'      => now()->subMonth(),
            'cancelled_at' => now()->subMonth(),
        ]);
    }

    public function inGracePeriod(int $days = 7): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'ends_at' => now()->subDay(),
            'grace_period_ends_at' => now()->addDays($days),
        ]);
    }

    public function endingSoon(int $days = 3): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'ends_at' => now()->addDays($days),
        ]);
    }

    public function yearly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => 'yearly',
            'ends_at' => now()->addYear(),
        ]);
    }

    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'billing_cycle' => 'monthly',
            'ends_at' => now()->addMonth(),
        ]);
    }
}
