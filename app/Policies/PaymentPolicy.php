<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Parent: can view own children's payments (relation-based)
        if ($user->parent !== null) {
            return true;
        }

        // Santri: can view own payments (relation-based)
        if ($user->santri !== null) {
            return true;
        }

        // Ustadz: blocked from finance module
        if ($user->ustadz !== null) {
            return false;
        }

        // Tenant Admin and Bendahara can view all payments
        return $user->isAdmin() || $user->isBendahara();
    }

    public function view(User $user, Payment $payment): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $payment->tenant_id) {
            return false;
        }

        // Parent: can only view own child's payments
        if ($user->parent !== null) {
            $parentSantriIds = $user->parent->santri->pluck('id')->toArray();
            return in_array($payment->santri_id, $parentSantriIds, true);
        }

        // Santri: can only view own payments
        if ($user->santri !== null) {
            return (int) $payment->santri_id === (int) $user->santri->id;
        }

        // Ustadz: blocked from finance module
        if ($user->ustadz !== null) {
            return false;
        }

        // Tenant Admin and Bendahara can view any payment in their tenant
        return $user->isAdmin() || $user->isBendahara();
    }

    public function create(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Parent: no access to create payments
        if ($user->parent !== null) {
            return false;
        }

        // Santri: no access to create payments
        if ($user->santri !== null) {
            return false;
        }

        // Only Admin and Bendahara can create payments
        return $user->tenant_id !== null && ($user->isAdmin() || $user->isBendahara());
    }

    public function update(User $user, Payment $payment): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $payment->tenant_id) {
            return false;
        }

        // Parent: no access to update payments
        if ($user->parent !== null) {
            return false;
        }

        // Santri: no access to update payments
        if ($user->santri !== null) {
            return false;
        }

        // Only Admin and Bendahara can update payments
        return $user->tenant_id !== null && ($user->isAdmin() || $user->isBendahara());
    }

    public function delete(User $user, Payment $payment): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $payment->tenant_id) {
            return false;
        }

        // Parent: no access to delete payments
        if ($user->parent !== null) {
            return false;
        }

        // Santri: no access to delete payments
        if ($user->santri !== null) {
            return false;
        }

        // Only Admin and Bendahara can delete payments
        return $user->tenant_id !== null && ($user->isAdmin() || $user->isBendahara());
    }

    public function verify(User $user, Payment $payment): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $payment->tenant_id) {
            return false;
        }

        // Parent: no access to verify payments
        if ($user->parent !== null) {
            return false;
        }

        // Santri: no access to verify payments
        if ($user->santri !== null) {
            return false;
        }

        // Only Admin and Bendahara can verify payments
        return $user->tenant_id !== null && ($user->isAdmin() || $user->isBendahara());
    }
}
