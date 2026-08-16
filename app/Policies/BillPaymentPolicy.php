<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Role;
use App\Models\User;

final class BillPaymentPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->ustadz !== null) {
            return false;
        }

        return $user->tenant_id !== null
            && ($user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA])
                || $user->parent !== null
                || $user->santri !== null);
    }

    public function view(User $user, BillPayment $payment): bool
    {
        if (! $this->belongsToTenant($user, $payment->tenant_id)) {
            return false;
        }

        if ($user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA])) {
            return true;
        }

        if ($user->parent !== null) {
            return $user->parent->santri()->whereKey($payment->santri_id)->exists();
        }

        return $user->santri !== null && (int) $user->santri->id === (int) $payment->santri_id;
    }

    public function create(User $user, Bill $bill): bool
    {
        if (! $this->belongsToTenant($user, $bill->tenant_id) || $bill->status === 'paid') {
            return false;
        }

        if ($user->parent !== null) {
            return $user->parent->santri()->whereKey($bill->santri_id)->exists();
        }

        if ($user->santri !== null) {
            return (int) $user->santri->id === (int) $bill->santri_id;
        }

        return false;
    }

    public function submitPayment(User $user): bool
    {
        return $user->tenant_id !== null
            && ! $user->isSuperAdmin()
            && ($user->parent !== null || $user->santri !== null);
    }

    public function update(User $user, BillPayment $payment): bool
    {
        return $this->canManagePending($user, $payment);
    }

    public function delete(User $user, BillPayment $payment): bool
    {
        return $this->canManagePending($user, $payment);
    }

    public function approve(User $user, BillPayment $payment): bool
    {
        return $this->belongsToTenant($user, $payment->tenant_id)
            && $payment->isPending()
            && $user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA]);
    }

    public function reject(User $user, BillPayment $payment): bool
    {
        return $this->approve($user, $payment);
    }

    public function uploadProof(User $user, BillPayment $payment): bool
    {
        return $this->belongsToTenant($user, $payment->tenant_id)
            && $payment->isManual()
            && $user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA]);
    }

    private function canManagePending(User $user, BillPayment $payment): bool
    {
        if (! $this->belongsToTenant($user, $payment->tenant_id) || ! $payment->isPending()) {
            return false;
        }

        if ($user->hasRole([Role::TENANT_ADMIN, Role::BENDAHARA])) {
            return true;
        }

        return $user->parent !== null
            && $user->parent->santri()->whereKey($payment->santri_id)->exists();
    }

    private function belongsToTenant(User $user, int $tenantId): bool
    {
        return ! $user->isSuperAdmin()
            && $user->tenant_id !== null
            && (int) $user->tenant_id === $tenantId;
    }
}
