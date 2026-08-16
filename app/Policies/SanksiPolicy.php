<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Sanksi;
use App\Models\Role;
use App\Models\User;

final class SanksiPolicy
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

    public function view(User $user, Sanksi $sanksi): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $sanksi->tenant_id) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null) {
            return true;
        }

        if ($user->santri !== null && $sanksi->pelanggaran) {
            return (int) $user->santri->id === (int) $sanksi->pelanggaran->santri_id;
        }

        if ($user->parent !== null && $sanksi->pelanggaran) {
            return $user->parent->santri()
                ->where('santri.id', $sanksi->pelanggaran->santri_id)
                ->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return $user->tenant_id !== null && ($user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null);
    }

    public function update(User $user, Sanksi $sanksi): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $sanksi->tenant_id) {
            return false;
        }

        return $user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null;
    }

    public function delete(User $user, Sanksi $sanksi): bool
    {
        return $this->update($user, $sanksi);
    }

    public function complete(User $user, Sanksi $sanksi): bool
    {
        return $this->update($user, $sanksi);
    }

    public function cancel(User $user, Sanksi $sanksi): bool
    {
        return $this->update($user, $sanksi);
    }
}
