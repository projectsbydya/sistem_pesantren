<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'tenant_id' => \App\Models\Tenant::factory(),
            'role' => User::ROLE_ADMIN,
            'is_super_admin' => false,
            'is_active' => true,
        ];
    }

    /**
     * Automatically assign a Spatie role matching the legacy role column.
     * This bridges test factories with the new Spatie-based authorization checks.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            $roleMap = [
                User::ROLE_ADMIN       => Role::TENANT_ADMIN,
                User::ROLE_SUPER_ADMIN => Role::SUPER_ADMIN,
                User::ROLE_PARENT      => Role::PARENT,
                User::ROLE_STUDENT     => Role::SANTRI,
                User::ROLE_USTADZ      => Role::USTADZ,
                User::ROLE_BENDAHARA   => Role::BENDAHARA,
            ];

            if (isset($roleMap[$user->role])) {
                $roleName = $roleMap[$user->role];
                $spatieRole = \Spatie\Permission\Models\Role::firstOrCreate(
                    ['name' => $roleName, 'guard_name' => 'web']
                );
                $user->assignRole($spatieRole);
            }
        });
    }

    /**
     * Create Santri relation for this user (student role).
     */
    public function withSantri(): static
    {
        return $this->afterCreating(function (User $user) {
            if ($user->santri === null) {
                \App\Models\Santri::factory()->create([
                    'user_id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                ]);
            }
            $user->refresh();
        });
    }

    /**
     * Create Parents relation for this user (parent role).
     */
    public function withParent(): static
    {
        return $this->afterCreating(function (User $user) {
            if ($user->parent === null) {
                \App\Models\Parents::factory()->create([
                    'user_id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                ]);
            }
            $user->refresh();
        });
    }

    /**
     * Create Ustadz relation for this user (ustadz role).
     */
    public function withUstadz(): static
    {
        return $this->afterCreating(function (User $user) {
            if ($user->ustadz === null) {
                \App\Models\Ustadz::factory()->create([
                    'user_id' => $user->id,
                    'tenant_id' => $user->tenant_id,
                ]);
            }
            $user->refresh();
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a super admin user.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_SUPER_ADMIN,
            'is_super_admin' => true,
            'tenant_id' => null,
        ]);
    }

    /**
     * Create an admin user (tenant admin).
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
        ]);
    }

    /**
     * Create a parent user.
     */
    public function parent(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_PARENT,
        ]);
    }

    /**
     * Create a student user.
     */
    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_STUDENT,
        ]);
    }

    /**
     * Assign user to a specific tenant.
     */
    public function forTenant(\App\Models\Tenant $tenant): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenant->id,
        ]);
    }

    /**
     * Create inactive user.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
