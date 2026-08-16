<?php

namespace App\Policies;

use App\Models\HafalanNilai;
use App\Models\Kelas;
use App\Models\Subject;
use App\Models\UstadzKelas;
use App\Models\User;

class HafalanNilaiPolicy
{
    /**
     * Users with relations (ustadz, santri, parent) or tenant membership can view hafalan nilai.
     * Relation-based: USER -> RELATION -> HafalanNilai
     */
    public function viewAny(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Relation-based access: santri (self), parent (children), ustadz (kelas), or tenant member
        return $user->ustadz !== null
            || $user->santri !== null
            || $user->parent !== null
            || $user->tenant_id !== null;
    }

    public function view(User $user, HafalanNilai $record): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $record->tenant_id) {
            return false;
        }

        // Santri: can only view their own records
        if ($user->santri) {
            return (int) $record->santri_id === (int) $user->santri->id;
        }

        // Parent: can only view their children's records (via ortu_santri pivot)
        if ($user->parent) {
            return $user->parent->santri()->where('santri.id', $record->santri_id)->exists();
        }

        // Ustadz: can view all records in their kelas (via ustadz_kelas)
        if ($user->ustadz) {
            return true; // Ustadz can view all hafalan nilai in their tenant (filtered by controller if needed)
        }

        // Tenant members (admin/staff) can view all records in their tenant
        return $user->tenant_id !== null;
    }

    /**
     * Can this user input hafalan/nilai for a given ustadz_kelas?
     * Relation-based: USER -> Ustadz -> UstadzKelas
     */
    public function inputFor(User $user, UstadzKelas $ustadzKelas): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $ustadzKelas->tenant_id) {
            return false;
        }

        // Ustadz: can only input for their own ustadz_kelas assignments
        if ($user->ustadz) {
            return $user->ustadz->ustadzKelas()
                ->where('id', $ustadzKelas->id)
                ->exists();
        }

        // Tenant members (admin/staff) can input for any ustadz_kelas in their tenant
        return $user->tenant_id !== null;
    }
}

