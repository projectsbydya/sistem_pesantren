<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

/**
 * TenantManagementPolicy — super admin only.
 *
 * Super admin is identified by the is_super_admin flag, NOT by role string.
 * This replaces the role:super_admin middleware on super-admin routes.
 */
class TenantManagementPolicy
{
    public function before(User $user): ?bool
    {
        // Only super admin can do anything here
        if (!$user->isSuperAdmin()) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->isSuperAdmin();
    }

    public function suspendTenant(User $user, Tenant $tenant): bool
    {
        return $user->isSuperAdmin();
    }

    public function activateTenant(User $user, Tenant $tenant): bool
    {
        return $user->isSuperAdmin();
    }
}
