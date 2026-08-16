<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\SaasPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaasPaymentFactory extends Factory
{
    protected $model = SaasPayment::class;

    public function definition(): array
    {
        return [
            'invoice_id'     => Invoice::factory(),
            'amount'         => fake()->randomElement([99000, 199000, 299000, 499000]),
            'payment_method' => fake()->randomElement(['transfer_bank', 'va', 'qris', 'cash']),
            'transfer_proof' => null,
            'reference_id'   => null,
            'status'         => 'pending',
            'notes'          => null,
            'confirmed_by'   => null,
            'confirmed_at'   => null,
            'paid_at'        => null,
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => [
            'status'       => 'confirmed',
            'confirmed_at' => now(),
            'paid_at'      => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => 'rejected',
        ]);
    }

    public function withProof(): static
    {
        return $this->state(fn () => [
            'transfer_proof' => 'proofs/test-proof-' . fake()->uuid() . '.jpg',
        ]);
    }
}
