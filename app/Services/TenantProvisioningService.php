<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use App\Notifications\WelcomeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Provisions a new Tenant + admin User in a single atomic transaction.
 *
 * Usage:
 *   $result = app(TenantProvisioningService::class)->provision([
 *       'name'           => 'Ustadz Ahmad',        // user display name
 *       'email'          => 'ahmad@pesantren.id',   // user + tenant email
 *       'pesantren_name' => 'Pesantren Al-Ihsan',   // optional — overrides auto name
 *       'plan'           => 'trial',                // optional
 *   ]);
 *
 *   ['user' => $user, 'tenant' => $tenant, 'plain_password' => $plainPassword] = $result;
 */
class TenantProvisioningService
{
    const PLAN_TRIAL  = 'trial';
    const TRIAL_DAYS  = 14;

    /**
     * Generate a cryptographically secure random password.
     * 12 characters: mix of uppercase, lowercase, numbers, symbols.
     * This is shown ONCE to the Super Admin and never stored in plain text.
     */
    public function generateSecurePassword(int $length = 12): string
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*';

        // Ensure at least one of each type
        $password = [
            $lowercase[random_int(0, 25)],
            $uppercase[random_int(0, 25)],
            $numbers[random_int(0, 9)],
            $symbols[random_int(0, 7)],
        ];

        // Fill remaining with random mix
        $all = $lowercase . $uppercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        // Shuffle to avoid predictable position of required characters
        shuffle($password);

        return implode('', $password);
    }

    /**
     * Provision a new tenant and its first admin user.
     * Password is auto-generated and returned in plain text for ONE-TIME display.
     *
     * @param  array{name: string, email: string, password?: string, pesantren_name?: string, plan?: string} $data
     * @return array{user: User, tenant: Tenant, plain_password: string}
     *
     * @throws \Throwable
     */
    public function provision(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $plan        = $data['plan'] ?? self::PLAN_TRIAL;
            $isTrial     = ($plan === self::PLAN_TRIAL);
            $trialDays   = (int) ($data['trial_days'] ?? self::TRIAL_DAYS);
            $trialEndsAt = $isTrial ? now()->addDays($trialDays) : null;

            // Auto-generate secure password if not provided
            $plainPassword = $data['password'] ?? $this->generateSecurePassword();

            // Derive tenant name: explicit pesantren_name wins, else auto-generate from user name
            $tenantName = !empty($data['pesantren_name'])
                ? $data['pesantren_name']
                : 'Pesantren ' . $data['name'];

            // Slug is derived from the slug-source, not the full display name:
            //   - pesantren_name given → slug from pesantren_name  (e.g. "Pesantren Test" → "pesantren-test")
            //   - auto-name from user  → slug from user name only  (e.g. user "Ahmad" → slug "ahmad")
            $slugSource = !empty($data['pesantren_name']) ? $data['pesantren_name'] : $data['name'];
            $slug       = $this->generateUniqueSlug($slugSource);

            // 1. Create Tenant — email stored so tenant has its own contact identity
            $tenant = Tenant::create([
                'name'          => $tenantName,
                'slug'          => $slug,
                'email'         => $data['email'],  // ✅ owner/admin email
                'plan'          => $plan,
                'status'        => 'active',
                'is_active'     => true,
                'is_trial'      => $isTrial,
                'trial_ends_at' => $trialEndsAt,
            ]);

            // 2. Create admin User linked to the tenant
            // CRITICAL: Force password change on first login for security
            $user = User::create([
                'name'                 => $data['name'],
                'email'                => $data['email'],
                'password'             => Hash::make($plainPassword),
                'must_change_password' => true,  // Force password change on first login
                'password_changed_at'  => null, // Never changed yet
                'tenant_id'            => $tenant->id,
                'role'                 => User::ROLE_ADMIN,
                'is_super_admin'       => false,
                'is_active'            => true,
            ]);

            // 3. Send welcome email after commit — includes credentials for first login
            $user->notify(
                (new WelcomeNotification(
                    tenant: $tenant,
                    loginEmail: $user->email,
                    plainPassword: $plainPassword,
                    roleLabel: 'Admin Pesantren',
                    tenantId: $tenant->id,
                ))->afterCommit()
            );

            return [
                'user'           => $user,
                'tenant'         => $tenant,
                'plain_password' => $plainPassword, // ONE-TIME display only
            ];
        });
    }

    /**
     * Generate a URL-safe slug unique within the tenants table.
     * Appends -1, -2, … until a free slot is found.
     */
    private function generateUniqueSlug(string $name): string
    {
        $base    = Str::slug($name);
        $slug    = $base;
        $counter = 1;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
