<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Bill;
use App\Models\Role;
use App\Models\User;

final class BillPolicy
{
    /**
     * Determine whether the user can view any bills.
     * - Tenant Admin & Bendahara: all bills in tenant
     * - Parent: bills for their children only
     * - Santri: their own bills only
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

        // Parent can view bills for their children
        if ($user->parent !== null) {
            return $user->tenant_id !== null;
        }

        // Santri can view their own bills (handled in view method)
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
     * Determine whether the user can view the bill.
     * - Tenant Admin & Bendahara: any bill in tenant
     * - Parent: only bills for their children
     * - Santri: only their own bills
     * - Super Admin: blocked
     */
    public function view(User $user, Bill $bill): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation check
        if ((int) $user->tenant_id !== (int) $bill->tenant_id) {
            return false;
        }

        // Tenant Admin and Bendahara can view any bill in their tenant
        if ($user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA])) {
            return true;
        }

        // Parent can only view bills for their children
        if ($user->parent !== null) {
            $childSantriIds = $user->parent->santri->pluck('id')->toArray();
            return in_array($bill->santri_id, $childSantriIds, false);
        }

        // Santri can only view their own bills
        if ($user->santri !== null) {
            return (int) $bill->santri_id === (int) $user->santri->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create bills.
     * Only Tenant Admin and Bendahara can create bills.
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
     * Determine whether the user can update the bill.
     * Only Tenant Admin and Bendahara can update bills.
     */
    public function update(User $user, Bill $bill): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $bill->tenant_id) {
            return false;
        }

        if ($user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA])) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the bill.
     * Only Tenant Admin can delete bills.
     */
    public function delete(User $user, Bill $bill): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $bill->tenant_id) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the bill.
     */
    public function restore(User $user, Bill $bill): bool
    {
        return $this->delete($user, $bill);
    }

    /**
     * Determine whether the user can permanently delete the bill.
     */
    public function forceDelete(User $user, Bill $bill): bool
    {
        return $this->delete($user, $bill);
    }
}
