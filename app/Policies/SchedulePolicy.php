<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    /**
     * Relation-based: USER -> RELATION -> Schedule
     * Tenant Member: full access
     * Ustadz: only schedules from their ustadz_kelas
     * Santri / Parent: may view schedules (filtered by kelas in controller)
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

    public function view(User $user, Schedule $schedule): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if (!$this->belongsToSameTenant($user, $schedule)) {
            return false;
        }

        // Santri: can only view schedules for their own kelas
        if ($user->santri) {
            return $schedule->ustadzKelas?->kelas_id === $user->santri->kelas_id;
        }

        // Parent: can only view schedules for kelas of their children (via ortu_santri)
        if ($user->parent) {
            $childKelasIds = $user->parent->santri()->pluck('kelas_id')->filter()->all();
            return in_array($schedule->ustadzKelas?->kelas_id, $childKelasIds, true);
        }

        // Ustadz: can view all schedules in the tenant (edit scoped by update/delete)
        if ($user->ustadz) {
            return true;
        }

        // Tenant members (admin/staff) can view all schedules in their tenant
        return $user->tenant_id !== null;
    }

    public function create(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Only tenant admins or assigned ustadz can create schedules
        return $user->isAdmin() || $user->isUstadz();
    }

    public function update(User $user, Schedule $schedule): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if (! $this->belongsToSameTenant($user, $schedule)) {
            return false;
        }

        // Admin: full CRUD
        if ($user->isAdmin()) {
            return true;
        }

        // Ustadz: only schedules linked to their own ustadz_kelas
        if ($user->isUstadz()) {
            return $user->ustadz->ustadzKelas()
                ->where('id', $schedule->ustadz_kelas_id)
                ->exists();
        }

        return false;
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        return $this->update($user, $schedule);
    }

    /**
     * Can this user record/update absensi for a given jadwal?
     * Relation-based: USER -> Ustadz -> UstadzKelas -> Schedule
     */
    public function recordFor(User $user, Schedule $schedule): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if (! $this->belongsToSameTenant($user, $schedule)) {
            return false;
        }

        // Admin: can record for any schedule in their tenant
        if ($user->isAdmin()) {
            return true;
        }

        // Ustadz: can only record for their assigned ustadz_kelas
        if ($user->isUstadz()) {
            return $user->ustadz->ustadzKelas()
                ->where('id', $schedule->ustadz_kelas_id)
                ->exists();
        }

        return false;
    }

    private function belongsToSameTenant(User $user, Schedule $schedule): bool
    {
        return (int) $user->tenant_id === (int) $schedule->tenant_id;
    }
}
