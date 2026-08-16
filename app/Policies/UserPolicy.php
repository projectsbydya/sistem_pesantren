<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * UserPolicy — role-based access control for User account management.
 *
 * Scope:
 * - Super Admin : full CRUD on any user (cross-tenant platform management)
 * - Tenant Admin: CRUD on users within their own tenant only
 *                 Cannot touch super admins or users from other tenants
 * - Ustadz/Santri/Parent: no access
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        // Super Admin: cross-tenant user management
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Tenant Admin only
        return $user->hasRole(Role::TENANT_ADMIN);
    }

    public function view(User $user, User $target): bool
    {
        // Super Admin: any user
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Tenant Admin: same tenant, non-super-admin targets only
        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return (int) $user->tenant_id === (int) $target->tenant_id
                && ! $target->is_super_admin;
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Super Admin: can create any user
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Tenant Admin only
        return $user->hasRole(Role::TENANT_ADMIN);
    }

    public function update(User $user, User $target): bool
    {
        // Super Admin: any user (except self-demotion guard — not the policy's concern)
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Tenant Admin: same tenant, non-super-admin targets only
        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return (int) $user->tenant_id === (int) $target->tenant_id
                && ! $target->is_super_admin;
        }

        return false;
    }

    public function delete(User $user, User $target): bool
    {
        // Prevent self-deletion
        if ($user->id === $target->id) {
            return false;
        }

        // Super Admin: any user (except another super admin)
        if ($user->isSuperAdmin()) {
            return ! $target->is_super_admin;
        }

        // Tenant Admin: same tenant, non-super-admin targets only
        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return (int) $user->tenant_id === (int) $target->tenant_id
                && ! $target->is_super_admin;
        }

        return false;
    }

    public function resetPassword(User $user, User $target): bool
    {
        // Prevent resetting own password via admin flow (use profile/settings instead)
        if ($user->id === $target->id) {
            return false;
        }

        // Super Admin: any non-super-admin user
        if ($user->isSuperAdmin()) {
            return ! $target->is_super_admin;
        }

        // Tenant Admin: same tenant, non-super-admin targets only
        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return (int) $user->tenant_id === (int) $target->tenant_id
                && ! $target->is_super_admin;
        }

        return false;
    }
}
