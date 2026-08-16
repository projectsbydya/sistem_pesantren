<?php

namespace App\Policies;

use App\Models\UstadzKelas;
use App\Models\User;

/**
 * UstadzKelasPolicy — resource-based access control for teacher-class assignments.
 *
 * USER → RESOURCE (UstadzKelas via tenant_id ownership)
 * - Tenant members: can manage (CRUD) penugasan within their tenant
 * - Super Admin: blocked from tenant data
 */
class UstadzKelasPolicy
{
    public function viewAny(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Admin can manage; ustadz can view their own assignments
        return $user->isAdmin() || $user->isUstadz();
    }

    public function view(User $user, UstadzKelas $ustadzKelas): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $ustadzKelas->tenant_id) {
            return false;
        }

        // Admin: can view all assignments in their tenant
        if ($user->isAdmin()) {
            return true;
        }

        // Ustadz: can view their own assignments
        if ($user->isUstadz()) {
            return $user->ustadz->ustadzKelas()
                ->where('id', $ustadzKelas->id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Only tenant admins can create assignments
        return $user->isAdmin();
    }

    public function update(User $user, UstadzKelas $ustadzKelas): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation + admin check
        return (int) $user->tenant_id === (int) $ustadzKelas->tenant_id
            && $user->isAdmin();
    }

    public function delete(User $user, UstadzKelas $ustadzKelas): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation + admin check
        return (int) $user->tenant_id === (int) $ustadzKelas->tenant_id
            && $user->isAdmin();
    }
}
