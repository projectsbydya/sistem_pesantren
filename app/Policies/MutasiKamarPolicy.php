<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MutasiKamar;
use App\Models\Role;
use App\Models\User;

final class MutasiKamarPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return $user->tenant_id !== null;
    }

    public function view(User $user, MutasiKamar $mutasi): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $mutasi->tenant_id) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null) {
            return true;
        }

        if ($user->santri !== null && (int) $user->santri->id === (int) $mutasi->santri_id) {
            return true;
        }

        if ($user->parent !== null) {
            return $user->parent->santri()
                ->where('santri.id', $mutasi->santri_id)
                ->exists();
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

    public function update(User $user, MutasiKamar $mutasi): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $mutasi->tenant_id) {
            return false;
        }

        return $user->hasRole(Role::TENANT_ADMIN);
    }

    public function delete(User $user, MutasiKamar $mutasi): bool
    {
        return $this->update($user, $mutasi);
    }
}
