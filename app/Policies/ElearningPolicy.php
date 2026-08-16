<?php

namespace App\Policies;

use App\Models\Elearning;
use App\Models\User;

class ElearningPolicy
{
    /**
     * Users with relations (ustadz, santri, parent) or tenant membership can view elearning.
     * Relation-based: USER -> RELATION -> Elearning
     */
    public function viewAny(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Relation-based access: tenant member, ustadz, santri, or parent
        return $user->tenant_id !== null
            || $user->ustadz !== null
            || $user->santri !== null
            || $user->parent !== null;
    }

    public function view(User $user, Elearning $elearning): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $elearning->tenant_id) {
            return false;
        }

        // Santri: can view global materials or materials for their own kelas
        if ($user->santri) {
            if ($elearning->kelas_id === null) {
                return true;
            }
            return (int) $elearning->kelas_id === (int) $user->santri->kelas_id;
        }

        // Parent: can view global materials or materials for their children's kelas (via ortu_santri)
        if ($user->parent) {
            if ($elearning->kelas_id === null) {
                return true;
            }
            $childKelasIds = $user->parent->santri()->pluck('kelas_id')->filter()->all();
            return in_array((int) $elearning->kelas_id, $childKelasIds, true);
        }

        // Ustadz: can view their own materials or materials for their ustadz_kelas
        if ($user->ustadz) {
            return $this->ustadzOwnsElearning($user, $elearning);
        }

        // Tenant members (admin/staff) can view all materials in their tenant
        return $user->tenant_id !== null;
    }

    public function create(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Only admin or ustadz can create e-learning materials
        return $user->isAdmin() || $user->isUstadz();
    }

    public function update(User $user, Elearning $elearning): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $elearning->tenant_id) {
            return false;
        }

        // Admin: can update any materials in their tenant
        if ($user->isAdmin()) {
            return true;
        }

        // Ustadz: can only update their own materials
        if ($user->isUstadz()) {
            return $this->ustadzOwnsElearning($user, $elearning);
        }

        return false;
    }

    public function delete(User $user, Elearning $elearning): bool
    {
        return $this->update($user, $elearning);
    }

    // -------------------------------------------------------------------------

    private function ustadzOwnsElearning(User $user, Elearning $elearning): bool
    {
        if (! $elearning->ustadz_kelas_id) {
            return false;
        }

        return $user->ustadz->ustadzKelas()
            ->where('id', $elearning->ustadz_kelas_id)
            ->exists();
    }
}
