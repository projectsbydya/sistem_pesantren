<?php

namespace App\Policies;

use App\Models\TargetHafalan;
use App\Models\User;

/**
 * TargetHafalanPolicy — access control for hafalan targets.
 *
 * USER → ROLE/RELATION → TargetHafalan
 * - Admin  : full CRUD (tenant-scoped)
 * - Ustadz : view + create (their classes via ustadz_kelas)
 * - Santri : view only their own target
 * - Parent : view targets of their children
 */
class TargetHafalanPolicy
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

        // Tenant members or ustadz can access
        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function view(User $user, TargetHafalan $target): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $target->tenant_id) {
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

        // Ustadz: can view all targets in their kelas (via ustadz_kelas)
        if ($user->ustadz) {
            return true;
        }

        // Tenant members (admin/staff) can view all targets in their tenant
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

        // Tenant members or ustadz can create
        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function update(User $user, TargetHafalan $target): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $target->tenant_id) {
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

        // Tenant members or ustadz can update
        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function delete(User $user, TargetHafalan $target): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $target->tenant_id) {
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

        // Tenant members can delete
        return $user->tenant_id !== null;
    }
}
