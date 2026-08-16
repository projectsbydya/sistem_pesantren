<?php

namespace App\Policies;

use App\Models\Kelas;
use App\Models\User;

/**
 * KelasPolicy — relation-based access control for Kelas resources.
 *
 * USER → ROLE/RELATION → Kelas
 * - Admin  : full CRUD (tenant-scoped)
 * - Ustadz : view only (classes they are assigned to via ustadz_kelas)
 */
class KelasPolicy
{
    public function viewAny(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Only admin or ustadz can access the Kelas management page
        return $user->isAdmin() || $user->isUstadz();
    }

    public function view(User $user, Kelas $kelas): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $kelas->tenant_id) {
            return false;
        }

        // Admin: full access within tenant
        if ($user->isAdmin()) {
            return true;
        }

        // Ustadz: can view all kelas in the tenant (management restricted to admin)
        if ($user->isUstadz()) {
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

        // Only tenant admins can create kelas
        return $user->isAdmin();
    }

    public function update(User $user, Kelas $kelas): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation + admin check
        return (int) $user->tenant_id === (int) $kelas->tenant_id
            && $user->isAdmin();
    }

    public function delete(User $user, Kelas $kelas): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation + admin check
        return (int) $user->tenant_id === (int) $kelas->tenant_id
            && $user->isAdmin();
    }

    public function restore(User $user, Kelas $kelas): bool
    {
        return $this->delete($user, $kelas);
    }

    public function forceDelete(User $user, Kelas $kelas): bool
    {
        return $this->delete($user, $kelas);
    }
}
