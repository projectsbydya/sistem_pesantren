<?php

namespace App\Policies;

use App\Models\Kelas;
use App\Models\Role;
use App\Models\Santri;
use App\Models\User;

/**
 * SantriPolicy — relation-based access control.
 *
 * USER → RELATION → RESOURCE
 * - Admin   : full access (tenant-scoped)
 * - Ustadz  : view only (ustadz_kelas → santri in their kelas)
 * - Parent  : view own children via parent_santri pivot
 * - Student : view only themselves via santri.user_id
 */
class SantriPolicy
{
    public function viewAny(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Relation-based access only (no role checks)
        return $user->ustadz !== null
            || $user->parent !== null
            || $user->santri !== null
            || $user->tenant_id !== null; // Tenant member (admin/staff)
    }

    public function view(User $user, Santri $santri): bool
    {
        // Super Admin: block tenant data
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $santri->tenant_id) {
            return false;
        }

        // Student: only themselves (strictest check first)
        if ($user->santri) {
            return (int) $user->santri->id === (int) $santri->id;
        }

        // Parent: only their own children via parent_santri pivot
        if ($user->parent) {
            return $user->parent->santri()
                ->where('santri.id', $santri->id)
                ->exists();
        }

        // Ustadz: only santri in kelas they are assigned to (via ustadz_kelas)
        if ($user->ustadz) {
            $ustadzKelasIds = $user->ustadz->ustadzKelas()->pluck('kelas_id');
            return $santri->programs()
                ->whereIn('kelas_id', $ustadzKelasIds)
                ->exists();
        }

        // Tenant member (admin/bendahara/staff) with no specific relation: full tenant access
        if ($user->tenant_id !== null) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Only Tenant Admin can create santri
        return $user->hasRole(Role::TENANT_ADMIN);
    }

    public function update(User $user, Santri $santri): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $santri->tenant_id) {
            return false;
        }

        // Santri: cannot update any santri data (including self)
        if ($user->santri) {
            return false;
        }

        // Parent: cannot update santri data (only view their children)
        if ($user->parent) {
            return false;
        }

        // Ustadz: cannot update santri data (only view via ustadz_kelas)
        if ($user->ustadz) {
            return false;
        }

        // Only Tenant Admin can update santri
        return $user->hasRole(Role::TENANT_ADMIN);
    }

    public function delete(User $user, Santri $santri): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $santri->tenant_id) {
            return false;
        }

        // Santri: cannot delete any santri data
        if ($user->santri) {
            return false;
        }

        // Parent: cannot delete santri data
        if ($user->parent) {
            return false;
        }

        // Ustadz: cannot delete santri data
        if ($user->ustadz) {
            return false;
        }

        // Only Tenant Admin can delete santri
        return $user->hasRole(Role::TENANT_ADMIN);
    }

    public function downloadCredentials(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Only Tenant Admin can download santri credentials
        return $user->hasRole(Role::TENANT_ADMIN);
    }
}
