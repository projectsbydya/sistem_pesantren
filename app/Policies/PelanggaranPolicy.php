<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Pelanggaran;
use App\Models\Role;
use App\Models\User;

final class PelanggaranPolicy
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

    public function view(User $user, Pelanggaran $pelanggaran): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $pelanggaran->tenant_id) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null) {
            return true;
        }

        if ($user->santri !== null && (int) $user->santri->id === (int) $pelanggaran->santri_id) {
            return true;
        }

        if ($user->parent !== null) {
            return $user->parent->santri()
                ->where('santri.id', $pelanggaran->santri_id)
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

    public function update(User $user, Pelanggaran $pelanggaran): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $pelanggaran->tenant_id) {
            return false;
        }

        return $user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null;
    }

    public function delete(User $user, Pelanggaran $pelanggaran): bool
    {
        return $this->update($user, $pelanggaran);
    }

    public function process(User $user, Pelanggaran $pelanggaran): bool
    {
        return $this->update($user, $pelanggaran);
    }

    public function complete(User $user, Pelanggaran $pelanggaran): bool
    {
        return $this->update($user, $pelanggaran);
    }
}
