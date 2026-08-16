<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Ustadz;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ustadz>
 */
class UstadzFactory extends Factory
{
    protected $model = Ustadz::class;

    public function definition(): array
    {
        return [
            'tenant_id'      => Tenant::factory(),
            'user_id'        => User::factory(),
            'bio'            => fake()->sentence(),
            'phone'          => fake()->phoneNumber(),
            'role'           => Ustadz::ROLE_PENGAJAR,
            'jam_per_minggu' => 20,
            'performa'       => 80,
            'status'         => Ustadz::STATUS_ACTIVE,
        ];
    }
}
