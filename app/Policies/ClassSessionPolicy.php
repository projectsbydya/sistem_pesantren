<?php

namespace App\Policies;

use App\Models\ClassSession;
use App\Models\User;

/**
 * ClassSessionPolicy — relation-based access control for ClassSession resources.
 *
 * USER → ROLE/RELATION → ClassSession
 * - Admin  : full CRUD within tenant
 * - Ustadz : view/create/update/delete sessions linked to their ustadz_kelas
 * - Santri : view sessions for their own kelas
 * - Parent : view sessions for kelas of their children
 */
class ClassSessionPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return $user->tenant_id !== null
            || $user->ustadz !== null
            || $user->santri !== null
            || $user->parent !== null;
    }

    public function view(User $user, ClassSession $classSession): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if (! $this->belongsToSameTenant($user, $classSession)) {
            return false;
        }

        if ($user->santri) {
            return $classSession->schedule?->kelas_id === $user->santri->kelas_id;
        }

        if ($user->parent) {
            $childKelasIds = $user->parent->santri()->pluck('kelas_id')->filter()->all();
            return in_array($classSession->schedule?->kelas_id, $childKelasIds, true);
        }

        // Ustadz: can view all class sessions in the tenant (edit/delete scoped separately)
        if ($user->ustadz) {
            return true;
        }

        return $user->tenant_id !== null;
    }

    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Only admin or ustadz can create sessions
        return $user->isAdmin() || $user->isUstadz();
    }

    public function update(User $user, ClassSession $classSession): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if (! $this->belongsToSameTenant($user, $classSession)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isUstadz()) {
            return $this->sessionBelongsToUstadz($user, $classSession);
        }

        return false;
    }

    public function delete(User $user, ClassSession $classSession): bool
    {
        return $this->update($user, $classSession);
    }

    public function restore(User $user, ClassSession $classSession): bool
    {
        return $this->delete($user, $classSession);
    }

    public function forceDelete(User $user, ClassSession $classSession): bool
    {
        return $this->delete($user, $classSession);
    }

    private function belongsToSameTenant(User $user, ClassSession $classSession): bool
    {
        return (int) $user->tenant_id === (int) $classSession->tenant_id;
    }

    private function sessionBelongsToUstadz(User $user, ClassSession $classSession): bool
    {
        if (! $classSession->schedule) {
            return false;
        }

        return $user->ustadz->ustadzKelas()
            ->where('id', $classSession->schedule->ustadz_kelas_id)
            ->exists();
    }
}
