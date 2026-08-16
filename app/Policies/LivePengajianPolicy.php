<?php

namespace App\Policies;

use App\Models\LivePengajian;
use App\Models\User;

/**
 * LivePengajianPolicy — relation-based access control.
 *
 * USER → RELATION → RESOURCE
 * - All tenant users : can view (index/show)
 * - Admin only       : can manage (create/update/delete/setStatus)
 */
class LivePengajianPolicy
{
    public function viewAny(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // All tenant members can view
        return $user->tenant_id !== null;
    }

    public function view(User $user, LivePengajian $livePengajian): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        return (int) $user->tenant_id === (int) $livePengajian->tenant_id;
    }

    public function create(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant members can create
        return $user->tenant_id !== null;
    }

    public function update(User $user, LivePengajian $livePengajian): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation + membership check
        return (int) $user->tenant_id === (int) $livePengajian->tenant_id
            && $user->tenant_id !== null;
    }

    public function delete(User $user, LivePengajian $livePengajian): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation + membership check
        return (int) $user->tenant_id === (int) $livePengajian->tenant_id
            && $user->tenant_id !== null;
    }

    public function setStatus(User $user, LivePengajian $livePengajian): bool
    {
        return $this->update($user, $livePengajian);
    }
}
