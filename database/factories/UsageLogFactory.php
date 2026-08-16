<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\UsageLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class UsageLogFactory extends Factory
{
    protected $model = UsageLog::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'metric' => fake()->randomElement(['user_count', 'santri_count', 'branch_count', 'storage_usage_mb']),
            'value' => fake()->numberBetween(1, 1000),
            'recorded_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'metadata' => null,
        ];
    }

    public function forMetric(string $metric): static
    {
        return $this->state(fn () => ['metric' => $metric]);
    }

    public function withValue(int|float $value): static
    {
        return $this->state(fn () => ['value' => $value]);
    }

    public function recent(): static
    {
        return $this->state(fn () => ['recorded_at' => now()]);
    }

    public function withMetadata(array $metadata): static
    {
        return $this->state(fn () => ['metadata' => $metadata]);
    }
}
