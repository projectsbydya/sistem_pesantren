<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\PenempatanKamar;
use App\Models\Role;
use App\Models\User;

final class PenempatanKamarPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return $user->tenant_id !== null;
    }

    public function view(User $user, PenempatanKamar $penempatan): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $penempatan->tenant_id) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null) {
            return true;
        }

        if ($user->santri !== null && (int) $user->santri->id === (int) $penempatan->santri_id) {
            return true;
        }

        if ($user->parent !== null) {
            return $user->parent->santri()
                ->where('santri.id', $penempatan->santri_id)
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

    public function update(User $user, PenempatanKamar $penempatan): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $penempatan->tenant_id) {
            return false;
        }

        return $user->hasRole(Role::TENANT_ADMIN);
    }

    public function delete(User $user, PenempatanKamar $penempatan): bool
    {
        return $this->update($user, $penempatan);
    }

    public function move(User $user): bool
    {
        return $this->create($user);
    }
}
