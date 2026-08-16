<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AssignmentPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return $user->tenant_id !== null
            || $user->ustadz !== null
            || $user->parent !== null
            || $user->santri !== null;
    }

    public function view(User $user, Assignment $assignment): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $assignment->tenant_id) {
            return false;
        }

        if ($user->santri) {
            return $this->hasAccessibleMember($user, $assignment, [$user->santri->id]);
        }

        if ($user->parent) {
            $santriIds = $user->parent->santri()->pluck('santri.id')->toArray();
            return $this->hasAccessibleMember($user, $assignment, $santriIds);
        }

        if ($user->ustadz) {
            return $this->ownsKelas($user, $assignment->kelas_id);
        }

        return $user->tenant_id !== null;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ($user->parent !== null || $user->santri !== null) {
            return false;
        }

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function update(User $user, Assignment $assignment): bool
    {
        return $this->modify($user, $assignment);
    }

    public function delete(User $user, Assignment $assignment): bool
    {
        return $this->modify($user, $assignment);
    }

    private function modify(User $user, Assignment $assignment): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $assignment->tenant_id) {
            return false;
        }

        if ($user->parent !== null || $user->santri !== null) {
            return false;
        }

        if ($user->ustadz) {
            return $this->ownsKelas($user, $assignment->kelas_id);
        }

        return $user->tenant_id !== null;
    }

    private function hasAccessibleMember(User $user, Assignment $assignment, array $santriIds): bool
    {
        if (empty($santriIds)) {
            return false;
        }

        return $assignment->members()
            ->whereIn('santri_id', $santriIds)
            ->exists();
    }

    private function ownsKelas(User $user, ?int $kelasId): bool
    {
        if ($kelasId === null) {
            return false;
        }

        return $user->ustadz->ustadzKelas()
            ->where('kelas_id', $kelasId)
            ->exists();
    }
}
