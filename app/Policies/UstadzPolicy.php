<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\Ustadz;
use App\Models\User;

/**
 * UstadzPolicy — relation-based access control.
 *
 * USER → RELATION → RESOURCE
 * - Admin  : full CRUD (tenant-scoped)
 * - Ustadz : view only (themselves + peers in same tenant)
 */
class UstadzPolicy
{
    public function viewAny(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Parent: no access to staff modules (relation-based)
        if ($user->parent !== null) {
            return false;
        }

        // Santri: no access to staff modules (relation-based)
        if ($user->santri !== null) {
            return false;
        }

        // Relation-based: ustadz can view peers, tenant members can view all
        return $user->ustadz !== null || $user->tenant_id !== null;
    }

    public function view(User $user, Ustadz $ustadz): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $ustadz->tenant_id) {
            return false;
        }

        // Ustadz: can view themselves and peers in same tenant
        if ($user->ustadz) {
            return true;
        }

        // Tenant member (admin/staff): full access within tenant
        return $user->tenant_id !== null;
    }

    public function create(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Parent: no access to staff modules (relation-based)
        if ($user->parent !== null) {
            return false;
        }

        // Santri: no access to staff modules (relation-based)
        if ($user->santri !== null) {
            return false;
        }

        // Only Tenant Admin can create ustadz
        return $user->hasRole(Role::TENANT_ADMIN);
    }

    public function update(User $user, Ustadz $ustadz): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $ustadz->tenant_id) {
            return false;
        }

        // Parent: no access to staff modules (relation-based)
        if ($user->parent !== null) {
            return false;
        }

        // Santri: no access to staff modules (relation-based)
        if ($user->santri !== null) {
            return false;
        }

        // Only Tenant Admin can update ustadz
        return $user->hasRole(Role::TENANT_ADMIN);
    }

    public function delete(User $user, Ustadz $ustadz): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $ustadz->tenant_id) {
            return false;
        }

        // Parent: no access to staff modules (relation-based)
        if ($user->parent !== null) {
            return false;
        }

        // Santri: no access to staff modules (relation-based)
        if ($user->santri !== null) {
            return false;
        }

        // Only Tenant Admin can delete ustadz
        return $user->hasRole(Role::TENANT_ADMIN);
    }

    public function downloadCredentials(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Only Tenant Admin can download ustadz credentials
        return $user->hasRole(Role::TENANT_ADMIN);
    }
}
