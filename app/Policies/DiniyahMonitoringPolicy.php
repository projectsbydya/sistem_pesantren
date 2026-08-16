<?php

namespace App\Policies;

use App\Models\DiniyahMonitoring;
use App\Models\UstadzKelas;
use App\Models\User;

class DiniyahMonitoringPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) return false;
        if ($user->parent !== null) return false;
        if ($user->santri !== null) return false;

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function view(User $user, DiniyahMonitoring $monitoring): bool
    {
        if ($user->isSuperAdmin()) return false;
        if ((int) $user->tenant_id !== (int) $monitoring->tenant_id) return false;

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) return false;
        if ($user->parent !== null) return false;
        if ($user->santri !== null) return false;

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function update(User $user, DiniyahMonitoring $monitoring): bool
    {
        if ($user->isSuperAdmin()) return false;
        if ((int) $user->tenant_id !== (int) $monitoring->tenant_id) return false;
        if ($user->parent !== null) return false;
        if ($user->santri !== null) return false;

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function delete(User $user, DiniyahMonitoring $monitoring): bool
    {
        if ($user->isSuperAdmin()) return false;
        if ((int) $user->tenant_id !== (int) $monitoring->tenant_id) return false;
        if ($user->parent !== null) return false;
        if ($user->santri !== null) return false;

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function recordFor(User $user, UstadzKelas $ustadzKelas): bool
    {
        if ($user->isSuperAdmin()) return false;
        if ((int) $user->tenant_id !== (int) $ustadzKelas->tenant_id) return false;
        if ($user->parent !== null) return false;
        if ($user->santri !== null) return false;

        if ($user->ustadz) {
            return $user->ustadz->ustadzKelas()->where('id', $ustadzKelas->id)->exists();
        }

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function rekap(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function riwayat(User $user, int $santriId): bool
    {
        return $this->viewAny($user);
    }
}
