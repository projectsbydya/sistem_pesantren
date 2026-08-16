<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\Tabungan;
use App\Models\User;

final class TabunganPolicy
{
    /**
     * Determine whether the user can view any tabungan.
     * - Tenant Admin & Bendahara: all tabungan in tenant
     * - Parent: tabungan for their children only
     * - Santri: their own tabungan only
     * - Ustadz: blocked from finance module
     * - Super Admin: blocked from tenant data
     */
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ($user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA])) {
            return $user->tenant_id !== null;
        }

        // Parent can view tabungan for their children
        if ($user->parent !== null) {
            return $user->tenant_id !== null;
        }

        // Santri can view their own tabungan (handled in view method)
        if ($user->santri !== null) {
            return $user->tenant_id !== null;
        }

        // Ustadz blocked from finance module
        if ($user->ustadz !== null) {
            return false;
        }

        return false;
    }

    /**
     * Determine whether the user can view the tabungan.
     */
    public function view(User $user, Tabungan $tabungan): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $tabungan->tenant_id) {
            return false;
        }

        // Tenant Admin and Bendahara can view any tabungan in their tenant
        if ($user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA])) {
            return true;
        }

        // Parent can only view tabungan for their children
        if ($user->parent !== null) {
            $childSantriIds = $user->parent->santri->pluck('id')->toArray();
            return in_array($tabungan->santri_id, $childSantriIds, false);
        }

        // Santri can only view their own tabungan
        if ($user->santri !== null) {
            return (int) $tabungan->santri_id === (int) $user->santri->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create tabungan.
     * Only Tenant Admin and Bendahara can create tabungan.
     */
    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ($user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA])) {
            return $user->tenant_id !== null;
        }

        return false;
    }

    /**
     * Determine whether the user can update the tabungan.
     * Only Tenant Admin and Bendahara can update tabungan.
     */
    public function update(User $user, Tabungan $tabungan): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $tabungan->tenant_id) {
            return false;
        }

        if ($user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA])) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the tabungan.
     * Only Tenant Admin can delete tabungan.
     */
    public function delete(User $user, Tabungan $tabungan): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $tabungan->tenant_id) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return true;
        }

        return false;
    }

    public function restore(User $user, Tabungan $tabungan): bool
    {
        return $this->delete($user, $tabungan);
    }

    public function forceDelete(User $user, Tabungan $tabungan): bool
    {
        return $this->delete($user, $tabungan);
    }
}
