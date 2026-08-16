<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Kamar;
use App\Models\Role;
use App\Models\User;

final class KamarPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return $user->tenant_id !== null;
        }

        if ($user->ustadz !== null) {
            return $user->tenant_id !== null;
        }

        if ($user->santri !== null || $user->parent !== null) {
            return $user->tenant_id !== null;
        }

        return false;
    }

    public function view(User $user, Kamar $kamar): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $kamar->tenant_id) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null) {
            return true;
        }

        if ($user->santri !== null && (int) $user->santri->kamar_id === (int) $kamar->id) {
            return true;
        }

        if ($user->parent !== null) {
            $childKamarIds = $user->parent->santri->pluck('kamar_id')->toArray();
            return in_array($kamar->id, $childKamarIds, false);
        }

        return false;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return $user->hasRole(Role::TENANT_ADMIN) && $user->tenant_id !== null;
    }

    public function update(User $user, Kamar $kamar): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $kamar->tenant_id) {
            return false;
        }

        return $user->hasRole(Role::TENANT_ADMIN);
    }

    public function delete(User $user, Kamar $kamar): bool
    {
        return $this->update($user, $kamar);
    }

    public function restore(User $user, Kamar $kamar): bool
    {
        return $this->delete($user, $kamar);
    }

    public function forceDelete(User $user, Kamar $kamar): bool
    {
        return $this->delete($user, $kamar);
    }
}
