<?php

namespace App\Policies;

use App\Models\AbsensiSantri;
use App\Models\Schedule;
use App\Models\UstadzKelas;
use App\Models\User;

class AbsensiPolicy
{
    /**
     * Users with ustadz relation or tenant membership can view absensi.
     * Relation-based: USER -> RELATION -> AbsensiSantri
     */
    public function viewAny(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Attendance input is for admin/ustadz only; there is no read-only student view
        return $user->isAdmin() || $user->isUstadz();
    }

    public function view(User $user, AbsensiSantri $absensi): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $absensi->tenant_id) {
            return false;
        }

        // Admin: can view all absensi in their tenant
        if ($user->isAdmin()) {
            return true;
        }

        // Ustadz: can only view absensi for their assigned ustadz_kelas
        if ($user->isUstadz()) {
            $jadwal = $absensi->jadwal;

            if (! $jadwal || ! $jadwal->ustadz_kelas_id) {
                return false;
            }

            return $user->ustadz->ustadzKelas()
                ->where('id', $jadwal->ustadz_kelas_id)
                ->exists();
        }

        // Santri: can only view their own absensi
        if ($user->isStudent() && $user->santri) {
            return (int) $absensi->santri_id === (int) $user->santri->id;
        }

        // Parent: can only view their children's absensi
        if ($user->isParent() && $user->parent) {
            return $user->parent->santri()
                ->where('santri.id', $absensi->santri_id)
                ->exists();
        }

        return false;
    }

    /**
     * Can user record attendance for a specific Schedule?
     * Relation-based: USER -> Ustadz -> UstadzKelas -> Schedule
     */
    public function recordFor(User $user, Schedule $schedule): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $schedule->tenant_id) {
            return false;
        }

        // Admin: can record attendance for any schedule in their tenant
        if ($user->isAdmin()) {
            return true;
        }

        // Ustadz: may only record attendance for schedules assigned via ustadz_kelas
        if ($user->isUstadz()) {
            return $user->ustadz->ustadzKelas()
                ->where('id', $schedule->ustadz_kelas_id)
                ->exists();
        }

        return false;
    }

}
