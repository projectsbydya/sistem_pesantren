<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $tenant = Tenant::factory()->create();
        $subscription = Subscription::factory()->for($tenant)->create();

        return [
            'tenant_id'       => $tenant->id,
            'subscription_id' => $subscription->id,
            'invoice_number'  => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'amount'          => fake()->randomElement([99000, 199000, 299000, 499000]),
            'status'          => 'unpaid',
            'due_date'        => now()->addDays(7)->toDateString(),
            'period_label'    => now()->format('F Y'),
            'notes'           => null,
            'paid_at'         => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status'  => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'status'   => 'unpaid',
            'due_date' => now()->subDays(3)->toDateString(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled']);
    }

    public function failed(): static
    {
        return $this->state(fn () => ['status' => 'failed']);
    }
}
