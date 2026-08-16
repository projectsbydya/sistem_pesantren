<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

/**
 * Subscription Policy — Super Admin Only
 *
 * Subscription management is strictly limited to super admin.
 * Tenants cannot access or modify their own subscription data directly.
 * All subscription changes must go through super admin.
 *
 * This is a fail-closed policy — any undefined action defaults to false.
 */
class SubscriptionPolicy
{
    /**
     * Before check — only super admin can proceed.
     * Returns false immediately for non-super-admin users.
     */
    public function before(User $user): ?bool
    {
        // Only super admin can manage subscriptions
        if (!$user->isSuperAdmin()) {
            return false;
        }

        return null; // Allow other checks to proceed for super admin
    }

    /**
     * Determine whether the user can view any subscriptions.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can view the subscription.
     */
    public function view(User $user, Subscription $subscription): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can create subscriptions.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the subscription.
     */
    public function update(User $user, Subscription $subscription): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete the subscription.
     */
    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can restore the subscription.
     */
    public function restore(User $user, Subscription $subscription): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can permanently delete the subscription.
     */
    public function forceDelete(User $user, Subscription $subscription): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can activate the subscription.
     */
    public function activate(User $user, Subscription $subscription): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can suspend the subscription.
     */
    public function suspend(User $user, Subscription $subscription): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can cancel the subscription.
     */
    public function cancel(User $user, Subscription $subscription): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can renew the subscription.
     */
    public function renew(User $user, Subscription $subscription): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can extend grace period.
     */
    public function extendGracePeriod(User $user, Subscription $subscription): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can convert trial to active.
     */
    public function convertTrial(User $user, Subscription $subscription): bool
    {
        return $user->isSuperAdmin();
    }

}
