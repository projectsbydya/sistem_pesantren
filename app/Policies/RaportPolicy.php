<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\Raport;
use App\Models\Role;
use App\Models\Santri;
use App\Models\User;

class RaportPolicy
{
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(Role::TENANT_ADMIN)
            || $user->isUstadz()
            || $user->isParent()
            || $user->isStudent();
    }

    public function view(User $user, Raport $raport): bool
    {
        if ((int) $user->tenant_id !== (int) $raport->tenant_id) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return true;
        }

        if ($user->isStudent() && $user->santri) {
            return (int) $user->santri->id === (int) $raport->santri_id;
        }

        if ($user->isParent() && $user->parent) {
            return $user->parent->hasSantri($raport->santri_id);
        }

        if ($user->isUstadz() && $user->ustadz) {
            return $this->ustadzHasKelas($user, $raport->program_id, $raport->kelas_id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::TENANT_ADMIN) || $user->isUstadz();
    }

    public function update(User $user, Raport $raport): bool
    {
        if ((int) $user->tenant_id !== (int) $raport->tenant_id) {
            return false;
        }

        if ($raport->status === 'published') {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return true;
        }

        if ($user->isUstadz() && $user->ustadz) {
            return $this->ustadzHasKelas($user, $raport->program_id, $raport->kelas_id);
        }

        return false;
    }

    public function delete(User $user, Raport $raport): bool
    {
        return $user->hasRole(Role::TENANT_ADMIN)
            && (int) $user->tenant_id === (int) $raport->tenant_id
            && $raport->status !== 'published';
    }

    public function publish(User $user, Raport $raport): bool
    {
        return $this->update($user, $raport);
    }

    public function unpublish(User $user, Raport $raport): bool
    {
        if ((int) $user->tenant_id !== (int) $raport->tenant_id) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return true;
        }

        if ($user->isUstadz() && $user->ustadz) {
            return $this->ustadzHasKelas($user, $raport->program_id, $raport->kelas_id);
        }

        return false;
    }

    public function regenerate(User $user, Raport $raport): bool
    {
        if ($raport->status !== 'draft') {
            return false;
        }

        return $this->update($user, $raport);
    }

    public function generateFor(User $user, Santri $santri, Program $program): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $santri->tenant_id) {
            return false;
        }

        if (! $program->tenants()->wherePivot('tenant_id', $user->tenant_id)->wherePivot('is_active', true)->exists()) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return true;
        }

        if ($user->isUstadz() && $user->ustadz) {
            $kelasIds = $santri->programs()
                ->where('program_id', $program->id)
                ->where('status', 'aktif')
                ->pluck('kelas_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (empty($kelasIds)) {
                return false;
            }

            return $user->ustadz->ustadzKelas()
                ->where('program_id', $program->id)
                ->whereIn('kelas_id', $kelasIds)
                ->exists();
        }

        return false;
    }

    /**
     * Return the santri IDs this user may see in raport lists for a given program.
     * null means unrestricted (tenant admin).
     */
    public static function accessibleSantriIds(User $user, int $programId): ?array
    {
        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return null;
        }

        if ($user->isStudent() && $user->santri) {
            return [$user->santri->id];
        }

        if ($user->isParent() && $user->parent) {
            return $user->parent->santri()->withoutGlobalScopes()->pluck('santri.id')->toArray();
        }

        if ($user->isUstadz() && $user->ustadz) {
            $kelasIds = $user->ustadz->ustadzKelas()
                ->where('program_id', $programId)
                ->pluck('kelas_id')
                ->unique()
                ->toArray();

            if (empty($kelasIds)) {
                return [];
            }

            return Santri::whereIn('kelas_id', $kelasIds)->pluck('id')->toArray();
        }

        return [];
    }

    private function ustadzHasKelas(User $user, int $programId, ?int $kelasId): bool
    {
        if (!$kelasId) {
            return false;
        }

        return $user->ustadz->ustadzKelas()
            ->where('program_id', $programId)
            ->where('kelas_id', $kelasId)
            ->exists();
    }
}
