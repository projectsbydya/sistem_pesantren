<?php

namespace App\Policies;

use App\Models\PlacementTest;
use App\Models\UstadzKelas;
use App\Models\User;
use App\Policies\Concerns\HasReadonlyAccess;

class PlacementTestPolicy
{
    use HasReadonlyAccess;

    public function viewAny(User $user): bool
    {
        return $this->viewAnyAllowed($user);
    }

    public function view(User $user, PlacementTest $placementTest): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $placementTest->tenant_id) {
            return false;
        }

        if ($user->santri) {
            return $placementTest->results()->where('santri_id', $user->santri->id)->exists();
        }

        if ($user->parent) {
            return $placementTest->results()
                ->whereIn('santri_id', $user->parent->santri()->pluck('santri.id'))
                ->exists();
        }

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function create(User $user): bool
    {
        return $this->createAllowed($user);
    }

    public function update(User $user, PlacementTest $placementTest): bool
    {
        return $this->modifyAllowed($user, $placementTest);
    }

    public function delete(User $user, PlacementTest $placementTest): bool
    {
        return $this->modifyAllowed($user, $placementTest);
    }

    public function recordFor(User $user, UstadzKelas $ustadzKelas): bool
    {
        return $this->recordForAllowed($user, $ustadzKelas);
    }
}
