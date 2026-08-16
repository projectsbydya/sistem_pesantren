<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SaasPayment;
use App\Models\User;

class SaasPaymentPolicy
{
    public function before(User $user): ?bool
    {
        if (!$user->isSuperAdmin()) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, SaasPayment $payment): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function confirm(User $user, SaasPayment $payment): bool
    {
        return $user->isSuperAdmin() && $payment->isPending();
    }

    public function reject(User $user, SaasPayment $payment): bool
    {
        return $user->isSuperAdmin() && $payment->isPending();
    }

    public function delete(User $user, SaasPayment $payment): bool
    {
        return $user->isSuperAdmin() && $payment->isPending();
    }
}
