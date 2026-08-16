<?php

namespace App\Policies;

use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\Subject;
use App\Models\User;

class NilaiPolicy
{
    /**
     * Users with ustadz relation or tenant membership can view nilai.
     * Relation-based: USER -> RELATION -> Nilai
     */
    public function viewAny(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Santri, Parent, Ustadz, or Tenant members can view
        return $user->santri !== null
            || $user->parent !== null
            || $user->ustadz !== null
            || $user->tenant_id !== null;
    }

    public function view(User $user, Nilai $nilai): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $nilai->tenant_id) {
            return false;
        }

        // Santri: can only view their own nilai
        if ($user->santri) {
            return (int) $nilai->santri_id === (int) $user->santri->id;
        }

        // Parent: can only view their children's nilai (via ortu_santri pivot)
        if ($user->parent) {
            return $user->parent->santri()
                ->where('santri.id', $nilai->santri_id)
                ->exists();
        }

        // Ustadz: can only view nilai for their assigned ustadz_kelas
        if ($user->ustadz) {
            return $this->ustadzOwnsNilai($user, $nilai);
        }

        // Tenant members (admin/staff) can view all nilai in their tenant
        return $user->tenant_id !== null;
    }

    public function create(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Only admin or ustadz can create nilai
        return $user->isAdmin() || $user->isUstadz();
    }

    public function update(User $user, Nilai $nilai): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $nilai->tenant_id) {
            return false;
        }

        // Admin: can update any nilai in their tenant
        if ($user->isAdmin()) {
            return true;
        }

        // Ustadz: can only update nilai they created (via ustadz_kelas)
        if ($user->isUstadz()) {
            return $this->ustadzOwnsNilai($user, $nilai);
        }

        return false;
    }

    public function delete(User $user, Nilai $nilai): bool
    {
        return $this->update($user, $nilai);
    }

    /**
     * Can this ustadz record/edit nilai for a given kelas + subject combo?
     * Relation-based: USER -> Ustadz -> UstadzKelas -> (kelas_id, subject_id, program_id)
     */
    public function recordFor(User $user, Kelas $kelas, Subject $subject, int $programId): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $kelas->tenant_id) {
            return false;
        }

        // Admin: can record for any combo in their tenant
        if ($user->isAdmin()) {
            return true;
        }

        // Ustadz: can only record for their assigned ustadz_kelas
        if ($user->isUstadz()) {
            return $user->ustadz->ustadzKelas()
                ->where('kelas_id', $kelas->id)
                ->where('subject_id', $subject->id)
                ->where('program_id', $programId)
                ->exists();
        }

        return false;
    }

    // -------------------------------------------------------------------------

    private function ustadzOwnsNilai(User $user, Nilai $nilai): bool
    {
        if (! $nilai->ustadz_kelas_id) {
            return false;
        }

        return $user->ustadz->ustadzKelas()
            ->where('tenant_id', $user->tenant_id)
            ->where('id', $nilai->ustadz_kelas_id)
            ->exists();
    }
}
