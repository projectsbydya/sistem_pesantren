<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Perizinan;
use App\Models\Role;
use App\Models\User;

final class PerizinanPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Santri: can view (own records only — enforced in view())
        if ($user->santri !== null) {
            return true;
        }

        // Parent: can view (children's records only — enforced in view())
        if ($user->parent !== null) {
            return true;
        }

        // Ustadz, Admin: full list within tenant
        return $user->ustadz !== null || $user->hasRole(Role::TENANT_ADMIN);
    }

    public function view(User $user, Perizinan $perizinan): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $perizinan->tenant_id) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null) {
            return true;
        }

        if ($user->santri !== null && (int) $user->santri->id === (int) $perizinan->santri_id) {
            return true;
        }

        if ($user->parent !== null) {
            return $user->parent->santri()
                ->where('santri.id', $perizinan->santri_id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return $user->tenant_id !== null
            && ($user->santri !== null || $user->parent !== null);
    }

    public function update(User $user, Perizinan $perizinan): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $perizinan->tenant_id) {
            return false;
        }

        if ($perizinan->status !== Perizinan::STATUS_PENDING) {
            return false;
        }

        if ($user->santri !== null && (int) $user->santri->id === (int) $perizinan->santri_id) {
            return true;
        }

        if ($user->parent !== null) {
            return $user->parent->santri()
                ->where('santri.id', $perizinan->santri_id)
                ->exists();
        }

        return false;
    }

    public function delete(User $user, Perizinan $perizinan): bool
    {
        return $this->update($user, $perizinan);
    }

    public function approve(User $user, Perizinan $perizinan): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $perizinan->tenant_id) {
            return false;
        }

        if ($perizinan->status !== Perizinan::STATUS_PENDING) {
            return false;
        }

        return $user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null;
    }

    public function reject(User $user, Perizinan $perizinan): bool
    {
        return $this->approve($user, $perizinan);
    }

    public function recordReturn(User $user, Perizinan $perizinan): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $perizinan->tenant_id) {
            return false;
        }

        if ($perizinan->status !== Perizinan::STATUS_DISETUJUI) {
            return false;
        }

        return $user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null;
    }

    /**
     * Authorize viewing the perizinan history for a specific santri (bySantri endpoint).
     */
    public function viewForSantri(User $user, int $santriId): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ($user->tenant_id === null) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null) {
            return true;
        }

        if ($user->santri !== null) {
            return (int) $user->santri->id === $santriId;
        }

        if ($user->parent !== null) {
            return $user->parent->santri()->where('santri.id', $santriId)->exists();
        }

        return false;
    }
}
