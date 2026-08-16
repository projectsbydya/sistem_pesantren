<?php

namespace App\Policies;

use App\Models\Parents;
use App\Models\Role;
use App\Models\User;

class ParentPolicy
{
    /**
     * Determine if the user can view any parents.
     * Relation-based: USER -> parent relation OR tenant membership
     */
    public function viewAny(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Santri: no access to parent records
        if ($user->santri !== null) {
            return false;
        }

        // Relation-based: parent can view (themselves), ustadz can view, tenant members can view
        return $user->parent !== null || $user->ustadz !== null || $user->tenant_id !== null;
    }

    /**
     * Determine if the user can view a specific parent.
     * Relation-based: USER -> parent (self) OR tenant membership
     */
    public function view(User $user, Parents $parent): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Santri: no access to parent records
        if ($user->santri !== null) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $parent->tenant_id) {
            return false;
        }

        // Parent: can only view themselves via parent relation (ortu_santri pivot owner)
        if ($user->parent && (int) $user->parent->id === (int) $parent->id) {
            return true;
        }

        // Ustadz: can view parents of their students (via ortu_santri)
        if ($user->ustadz) {
            // Ustadz can view any parent in their kelas via student relation
            $ustadzKelasIds = $user->ustadz->ustadzKelas()->pluck('kelas_id');
            return $parent->santri()
                ->whereHas('programs', fn ($q) => $q->whereIn('kelas_id', $ustadzKelasIds))
                ->exists();
        }

        // Tenant member (admin/staff): full access within tenant
        return $user->tenant_id !== null;
    }

    /**
     * Determine if the user can create parents.
     */
    public function create(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Only Tenant Admin can create parents
        return $user->hasRole(Role::TENANT_ADMIN);
    }

    /**
     * Determine if the user can update a parent.
     */
    public function update(User $user, Parents $parent): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $parent->tenant_id) {
            return false;
        }

        // Parent: can update themselves (self-service)
        if ($user->parent && (int) $user->parent->id === (int) $parent->id) {
            return true;
        }

        // Only Tenant Admin can update other parents
        return $user->hasRole(Role::TENANT_ADMIN);
    }

    /**
     * Determine if the user can delete a parent.
     */
    public function delete(User $user, Parents $parent): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $parent->tenant_id) {
            return false;
        }

        // Only Tenant Admin can delete parents
        return $user->hasRole(Role::TENANT_ADMIN);
    }
}
