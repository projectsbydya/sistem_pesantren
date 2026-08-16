<?php

namespace App\Services;

use App\Models\Parents;
use App\Models\Santri;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\PasswordResetCredentialsNotification;
use App\Notifications\WelcomeNotification;
use App\Services\TenantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Centralised user provisioning service.
 *
 * Responsibilities:
 *  - Auto-generate email identifiers and secure passwords
 *  - Create User records with correct role and tenant_id
 *  - Link entity (Santri / Parent / Ustadz) → user_id atomically
 *  - Set must_change_password = true so first-login flow is triggered
 *  - Return plain-text password ONCE (only on creation)
 *
 * Constraints:
 *  - Idempotent: will NOT overwrite existing user accounts
 *  - No hardcoded domains: all email suffixes come from config('app.provisioning')
 *  - No controller logic: callers just handle the returned credentials
 *  - Super admin is NOT a provisioned role — never touched here
 */
class UserProvisioningService
{
    // =========================================================================
    // Password Generation
    // =========================================================================

    /**
     * Generate a cryptographically random, human-readable password.
     * Guarantees at least one lowercase, uppercase, and digit character.
     */
    public function generatePassword(int $length = 10): string
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers   = '0123456789';

        // Guarantee character class coverage
        $password = [
            $lowercase[random_int(0, strlen($lowercase) - 1)],
            $uppercase[random_int(0, strlen($uppercase) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
        ];

        $all = $lowercase . $uppercase . $numbers;
        for ($i = 3; $i < $length; $i++) {
            $password[] = $all[random_int(0, strlen($all) - 1)];
        }

        shuffle($password);
        return implode('', $password);
    }

    // =========================================================================
    // Email Generation — config-driven, no hardcoded domains
    // =========================================================================

    /**
     * Generate unique login email for a Santri.
     * Format: santri-{nis}@{tenant-slug}.{configured-student-domain}
     */
    public function generateSantriEmail(Santri $santri, Tenant $tenant): string
    {
        $base   = 'santri-' . Str::slug($santri->nis);
        $suffix = Str::slug($tenant->slug) . '.' . config('app.provisioning.email_domain_student');
        return $this->uniqueEmail($base, $suffix);
    }

    /**
     * Generate unique login email for a Parent.
     * Format: ortu-{parent-id}@{tenant-slug}.{configured-parent-domain}
     */
    public function generateParentEmail(Parents $parent, Tenant $tenant): string
    {
        $base   = 'ortu-' . $parent->id;
        $suffix = Str::slug($tenant->slug) . '.' . config('app.provisioning.email_domain_parent');
        return $this->uniqueEmail($base, $suffix);
    }

    /**
     * Generate unique login email for an Ustadz.
     * Format: ustadz-{slug-name}@{tenant-slug}.{configured-ustadz-domain}
     */
    public function generateUstadzEmail(string $name, Tenant $tenant): string
    {
        $base   = 'ustadz-' . Str::slug(explode(' ', trim($name))[0]);
        $suffix = Str::slug($tenant->slug) . '.' . config('app.provisioning.email_domain_ustadz');
        return $this->uniqueEmail($base, $suffix);
    }

    /**
     * Generate unique login email for an Admin.
     * Format: admin-{first-name}@{tenant-slug}.{configured-admin-domain}
     */
    public function generateAdminEmail(string $name, Tenant $tenant): string
    {
        $base   = 'admin-' . Str::slug(explode(' ', trim($name))[0]);
        $suffix = Str::slug($tenant->slug) . '.' . config('app.provisioning.email_domain_admin');
        return $this->uniqueEmail($base, $suffix);
    }

    /**
     * Generate unique login email for a Bendahara.
     * Format: bendahara-{first-name}@{tenant-slug}.{configured-bendahara-domain}
     */
    public function generateBendaharaEmail(string $name, Tenant $tenant): string
    {
        $base   = 'bendahara-' . Str::slug(explode(' ', trim($name))[0]);
        $suffix = Str::slug($tenant->slug) . '.' . config('app.provisioning.email_domain_bendahara');
        return $this->uniqueEmail($base, $suffix);
    }

    // =========================================================================
    // Ensure Methods — mandatory creation-time provisioning (domain-driven API)
    // =========================================================================
    //
    // These are the PRIMARY entry points for all controllers.
    // They are idempotent by design:
    //   - user_id already set → return existing user, password = null (never re-expose)
    //   - user_id null        → provision now, return credentials once
    //
    // Returning null for password on existing users is INTENTIONAL:
    //   callers must check isset($result['password']) before displaying credentials.

    /**
     * Ensure a Santri has a user account. Creates one if missing.
     *
     * @return array{user: User, password: string|null, created: bool}
     */
    public function ensureSantriHasUser(Santri $santri): array
    {
        if ($santri->user_id !== null) {
            // Already provisioned — return existing, never re-expose password
            return ['user' => $santri->user, 'password' => null, 'created' => false];
        }

        $result = $this->createSantriUser(['santri_id' => $santri->id]);
        return ['user' => $result['user'], 'password' => $result['password'], 'created' => true];
    }

    /**
     * Ensure a Parent has a user account. Creates one if missing.
     *
     * @return array{user: User, password: string|null, created: bool}
     */
    public function ensureParentHasUser(Parents $parent): array
    {
        if ($parent->user_id !== null) {
            return ['user' => $parent->user, 'password' => null, 'created' => false];
        }

        $result = $this->createParentUser(['parent_id' => $parent->id]);
        return ['user' => $result['user'], 'password' => $result['password'], 'created' => true];
    }

    /**
     * Ensure an Ustadz has a user account. Creates one if missing.
     *
     * @param  string $name  Display name to use when creating the user record.
     * @return array{user: User, password: string|null, created: bool}
     */
    public function ensureUstadzHasUser(\App\Models\Ustadz $ustadz, string $name): array
    {
        if ($ustadz->user_id !== null) {
            return ['user' => $ustadz->user, 'password' => null, 'created' => false];
        }

        $result = $this->createUstadzUser(['ustadz_id' => $ustadz->id, 'name' => $name]);
        return ['user' => $result['user'], 'password' => $result['password'], 'created' => true];
    }

    // =========================================================================
    // User-First Provisioning — create User before entity exists
    // =========================================================================

    /**
     * Create a User account for a new Bendahara BEFORE any entity record exists.
     *
     * Bendahara is a standalone user (no separate entity table like Ustadz/Santri).
     * User-first order: User is created directly with role=bendahara.
     * Must be called inside a DB::transaction by the controller.
     *
     * @param  array{name: string, tenant_id: int, email?: string, password?: string} $data
     * @return array{user: User, password: string}
     */
    public function provisionNewBendahara(array $data): array
    {
        $this->assertEntityBelongsToActiveTenant($data['tenant_id']);

        $tenant   = Tenant::findOrFail($data['tenant_id']);
        $password = $data['password'] ?? $this->generatePassword();
        $email    = $data['email']    ?? $this->generateBendaharaEmail($data['name'], $tenant);

        $user = $this->createUser([
            'name'      => $data['name'],
            'email'     => $email,
            'password'  => $password,
            'tenant_id' => $tenant->id,
            'role'      => User::ROLE_BENDAHARA,
        ]);

        return ['user' => $user, 'password' => $password];
    }

    /**
     * Create a User account for a new Ustadz BEFORE the Ustadz record exists.
     *
     * User-first order: User → Ustadz(user_id=user.id).
     * Returns user + plain password. Caller must pass user_id when creating Ustadz.
     * Must be called inside a DB::transaction by the controller.
     *
     * @param  array{name: string, tenant_id: int, email?: string, password?: string} $data
     * @return array{user: User, password: string}
     */
    public function provisionNewUstadz(array $data): array
    {
        $this->assertEntityBelongsToActiveTenant($data['tenant_id']);

        $tenant   = Tenant::findOrFail($data['tenant_id']);
        $password = $data['password'] ?? $this->generatePassword();
        $email    = $data['email']    ?? $this->generateUstadzEmail($data['name'], $tenant);

        $user = $this->createUser([
            'name'      => $data['name'],
            'email'     => $email,
            'password'  => $password,
            'tenant_id' => $tenant->id,
            'role'      => User::ROLE_USTADZ,
        ]);

        return ['user' => $user, 'password' => $password];
    }

    /**
     * Create a User account for a new Santri BEFORE the Santri record exists.
     *
     * User-first order: User → Santri(user_id=user.id).
     * Caller must pass user_id when creating Santri.
     * Must be called inside a DB::transaction by the controller.
     *
     * @param  array{name: string, nis: string, tenant_id: int, email?: string, password?: string} $data
     * @return array{user: User, password: string}
     */
    public function provisionNewSantri(array $data): array
    {
        $this->assertEntityBelongsToActiveTenant($data['tenant_id']);

        $tenant   = Tenant::findOrFail($data['tenant_id']);
        $password = $data['password'] ?? $this->generatePassword();

        $base   = 'santri-' . \Illuminate\Support\Str::slug($data['nis']);
        $suffix = \Illuminate\Support\Str::slug($tenant->slug) . '.' . config('app.provisioning.email_domain_student');
        $email  = $data['email'] ?? $this->uniqueEmail($base, $suffix);

        $user = $this->createUser([
            'name'      => $data['name'],
            'email'     => $email,
            'password'  => $password,
            'tenant_id' => $tenant->id,
            'role'      => User::ROLE_STUDENT,
        ]);

        return ['user' => $user, 'password' => $password];
    }

    /**
     * Create a User account for a new Parent BEFORE the Parent record exists.
     *
     * User-first order: User → Parents(user_id=user.id).
     * Caller must pass user_id when creating Parent.
     * Must be called inside a DB::transaction by the controller.
     *
     * @param  array{name: string, tenant_id: int, email?: string, password?: string} $data
     * @return array{user: User, password: string}
     */
    public function provisionNewParent(array $data): array
    {
        $this->assertEntityBelongsToActiveTenant($data['tenant_id']);

        $tenant   = Tenant::findOrFail($data['tenant_id']);
        $password = $data['password'] ?? $this->generatePassword();

        // Temporary placeholder ID for email uniqueness — will be replaced after parent is saved
        $base   = 'ortu-' . \Illuminate\Support\Str::slug($data['name']);
        $suffix = \Illuminate\Support\Str::slug($tenant->slug) . '.' . config('app.provisioning.email_domain_parent');
        $email  = $data['email'] ?? $this->uniqueEmail($base, $suffix);

        $user = $this->createUser([
            'name'      => $data['name'],
            'email'     => $email,
            'password'  => $password,
            'tenant_id' => $tenant->id,
            'role'      => User::ROLE_PARENT,
        ]);

        return ['user' => $user, 'password' => $password];
    }

    // =========================================================================
    // User Creation — entity-specific, idempotent
    // =========================================================================

    /**
     * Create a User account for a Santri (role: student).
     *
     * Idempotent: throws if santri already has user_id set.
     * Returns plain password only on creation.
     *
     * @param  array{santri_id: int, email?: string, password?: string} $data
     * @return array{user: User, password: string}
     */
    public function createSantriUser(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $santri = Santri::withoutGlobalScopes()->findOrFail($data['santri_id']);
            $this->assertEntityBelongsToActiveTenant($santri->tenant_id);

            // Idempotency guard — never overwrite existing account
            if ($santri->user_id !== null) {
                throw new \RuntimeException('Santri ini sudah memiliki user account.');
            }

            $tenant   = Tenant::findOrFail($santri->tenant_id);
            $password = $data['password'] ?? $this->generatePassword();
            $email    = $data['email']    ?? $this->generateSantriEmail($santri, $tenant);

            $user = $this->createUser([
                'name'      => $santri->name,
                'email'     => $email,
                'password'  => $password,
                'tenant_id' => $tenant->id,
                'role'      => User::ROLE_STUDENT,
            ]);

            // Atomically link entity → user within the same transaction
            $santri->update(['user_id' => $user->id]);

            $this->dispatchWelcomeNotification($user, $tenant, $password);

            return ['user' => $user, 'password' => $password];
        });
    }

    /**
     * Create a User account for a Parent (role: parent).
     *
     * Idempotent: throws if parent already has user_id set.
     * Returns plain password only on creation.
     *
     * @param  array{parent_id: int, email?: string, password?: string} $data
     * @return array{user: User, password: string}
     */
    public function createParentUser(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $parent = Parents::withoutGlobalScopes()->findOrFail($data['parent_id']);
            $this->assertEntityBelongsToActiveTenant($parent->tenant_id);

            // Idempotency guard — never overwrite existing account
            if ($parent->user_id !== null) {
                throw new \RuntimeException('Parent ini sudah memiliki user account.');
            }

            $tenant   = Tenant::findOrFail($parent->tenant_id);
            $password = $data['password'] ?? $this->generatePassword();
            $email    = $data['email']    ?? $this->generateParentEmail($parent, $tenant);

            $user = $this->createUser([
                'name'      => $parent->name,
                'email'     => $email,
                'password'  => $password,
                'tenant_id' => $tenant->id,
                'role'      => User::ROLE_PARENT,
            ]);

            // Atomically link entity → user within the same transaction
            $parent->update(['user_id' => $user->id]);

            $this->dispatchWelcomeNotification($user, $tenant, $password);

            return ['user' => $user, 'password' => $password];
        });
    }

    /**
     * Create a User account for an Ustadz (role: ustadz).
     *
     * Idempotent: throws if ustadz already has user_id set.
     * Returns plain password only on creation.
     *
     * @param  array{ustadz_id: int, name: string, email?: string, password?: string} $data
     * @return array{user: User, password: string}
     */
    public function createUstadzUser(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $ustadz = \App\Models\Ustadz::withoutGlobalScopes()->findOrFail($data['ustadz_id']);
            $this->assertEntityBelongsToActiveTenant($ustadz->tenant_id);

            // Idempotency guard — never overwrite existing account
            if ($ustadz->user_id !== null) {
                throw new \RuntimeException('Ustadz ini sudah memiliki user account.');
            }

            $tenant   = Tenant::findOrFail($ustadz->tenant_id);
            $password = $data['password'] ?? $this->generatePassword();
            $email    = $data['email']    ?? $this->generateUstadzEmail($data['name'], $tenant);

            $user = $this->createUser([
                'name'      => $data['name'],
                'email'     => $email,
                'password'  => $password,
                'tenant_id' => $tenant->id,
                'role'      => User::ROLE_USTADZ,
            ]);

            // Atomically link entity → user within the same transaction
            $ustadz->update(['user_id' => $user->id]);

            $this->dispatchWelcomeNotification($user, $tenant, $password);

            return ['user' => $user, 'password' => $password];
        });
    }

    /**
     * Create a User account for a Tenant Admin (role: admin).
     *
     * Used by super admin when onboarding a new tenant.
     * No entity record to link — admin is a standalone user.
     *
     * @param  array{name: string, tenant_id: int, email?: string, password?: string} $data
     * @return array{user: User, password: string}
     */
    public function createAdmin(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $tenant   = Tenant::findOrFail($data['tenant_id']);
            $password = $data['password'] ?? $this->generatePassword();
            $email    = $data['email']    ?? $this->generateAdminEmail($data['name'], $tenant);

            $user = $this->createUser([
                'name'      => $data['name'],
                'email'     => $email,
                'password'  => $password,
                'tenant_id' => $tenant->id,
                'role'      => User::ROLE_ADMIN,
            ]);

            $this->dispatchWelcomeNotification($user, $tenant, $password);

            return ['user' => $user, 'password' => $password];
        });
    }

    // =========================================================================
    // Bulk Operations
    // =========================================================================

    /**
     * Bulk-create accounts for all Santri in a tenant who have no user_id yet.
     * Each santri is wrapped in its own transaction so one failure doesn't abort all.
     *
     * @return array{created: int, failed: int, users: array}
     */
    public function bulkCreateSantriUsers(int $tenantId): array
    {
        $santris = Santri::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereNull('user_id')
            ->get();

        $results = ['created' => 0, 'failed' => 0, 'users' => []];

        foreach ($santris as $santri) {
            try {
                $result = $this->createSantriUser(['santri_id' => $santri->id]);
                $results['created']++;
                $results['users'][] = [
                    'santri_id' => $santri->id,
                    'name'      => $santri->name,
                    'email'     => $result['user']->email,
                ];
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return $results;
    }

    // =========================================================================
    // Password Management
    // =========================================================================

    /**
     * Reset a user's password and flag must_change_password so they are
     * forced to set a new one on next login.
     * Dispatches a PasswordResetCredentialsNotification if user has email.
     *
     * @return array{user: User, password: string}
     */
    public function resetPassword(int $userId): array
    {
        $user     = User::findOrFail($userId);
        $password = $this->generatePassword();

        $user->update([
            'password'            => Hash::make($password),
            'must_change_password' => true,
        ]);

        if ($user->tenant_id && $user->email) {
            try {
                $tenant = Tenant::find($user->tenant_id);
                if ($tenant) {
                    Notification::send($user, new PasswordResetCredentialsNotification(
                        $tenant,
                        $password,
                        $user->tenant_id
                    ));
                }
            } catch (\Throwable $e) {
                Log::warning('PasswordResetCredentialsNotification dispatch failed', [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage(),
                ]);
            }
        }

        return ['user' => $user, 'password' => $password];
    }

    // =========================================================================
    // Internal Helpers
    // =========================================================================

    /**
     * Create the User record with common defaults applied consistently.
     *
     * must_change_password = true is set on EVERY provisioned account so that
     * the first-login password-change flow is always triggered, regardless of
     * which entity type (santri / parent / ustadz / admin) was provisioned.
     */
    private function createUser(array $data): User
    {
        return User::create([
            'name'                 => $data['name'],
            'email'                => $data['email'],
            'password'             => Hash::make($data['password']),
            'tenant_id'            => $data['tenant_id'],
            'role'                 => $data['role'],
            'is_super_admin'       => false,
            'is_active'            => true,
            'must_change_password' => true,
        ]);
    }

    /**
     * Dispatch a WelcomeNotification for a newly provisioned user.
     * Fails safely — a notification failure never aborts provisioning.
     */
    private function dispatchWelcomeNotification(User $user, Tenant $tenant, string $plainPassword): void
    {
        if (empty($user->email)) {
            return;
        }

        $roleLabels = [
            User::ROLE_ADMIN      => 'Admin',
            User::ROLE_BENDAHARA  => 'Bendahara',
            User::ROLE_USTADZ     => 'Ustadz/Ustadzah',
            User::ROLE_PARENT     => 'Orang Tua',
            User::ROLE_STUDENT    => 'Santri',
        ];

        $roleLabel = $roleLabels[$user->role] ?? ucfirst($user->role);

        try {
            Notification::send($user, new WelcomeNotification(
                $tenant,
                $user->email,
                $plainPassword,
                $roleLabel,
                $tenant->id
            ));
        } catch (\Throwable $e) {
            Log::warning('WelcomeNotification dispatch failed', [
                'user_id'   => $user->id,
                'tenant_id' => $tenant->id,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Assert that an entity belongs to the currently active tenant.
     *
     * Defence-in-depth: even though controllers load entities through TenantScope,
     * the create*User() methods use withoutGlobalScopes()->findOrFail() to re-fetch.
     * This guard ensures a forged ID from another tenant is always rejected.
     *
     * If no tenant context is active (e.g. console commands, queue jobs, seeders)
     * the check is skipped — the caller is responsible for correctness.
     */
    private function assertEntityBelongsToActiveTenant(int $entityTenantId): void
    {
        $activeTenantId = TenantService::getTenantId();

        if ($activeTenantId !== null && $entityTenantId !== $activeTenantId) {
            abort(403, 'Unauthorized tenant access');
        }
    }

    /**
     * Return a unique email address for the given base and domain suffix.
     * Appends an incrementing counter if the base address is already taken.
     *
     * Example: santri-001@pondok-a.student.pesantren.local
     *          santri-001-2@pondok-a.student.pesantren.local  (if first exists)
     */
    private function uniqueEmail(string $base, string $suffix): string
    {
        $email   = "{$base}@{$suffix}";
        $counter = 1;

        while (User::withoutGlobalScopes()->where('email', $email)->exists()) {
            $email = "{$base}-{$counter}@{$suffix}";
            $counter++;
        }

        return $email;
    }
}
