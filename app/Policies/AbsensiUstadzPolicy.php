<?php

namespace App\Policies;

use App\Models\AbsensiUstadz;
use App\Models\Schedule;
use App\Models\User;

class AbsensiUstadzPolicy
{
    /**
     * Users with ustadz relation or tenant membership can view absensi ustadz.
     * Relation-based: USER -> RELATION -> AbsensiUstadz
     */
    public function viewAny(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Parent: no access to staff modules (relation-based)
        if ($user->parent !== null) {
            return false;
        }

        // Santri: no access to staff modules (relation-based)
        if ($user->santri !== null) {
            return false;
        }

        // Ustadz can view (self/related), tenant members can view all
        return $user->ustadz !== null || $user->tenant_id !== null;
    }

    public function view(User $user, AbsensiUstadz $absensi): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $absensi->tenant_id) {
            return false;
        }

        // Ustadz: can view their own absensi records only
        if ($user->ustadz) {
            return $absensi->ustadz()
                ->whereHas('user', fn ($q) => $q->where('id', $user->id))
                ->exists();
        }

        // Tenant members (admin/staff) can view all absensi in their tenant
        return $user->tenant_id !== null;
    }

    /**
     * Can user record attendance for a specific Ustadz record?
     * Relation-based: USER -> Ustadz -> AbsensiUstadz
     */
    public function createFor(User $user, Schedule $schedule): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $schedule->tenant_id) {
            return false;
        }

        if (! $schedule->program()->whereHas('tenants', function ($tenant) use ($user) {
            $tenant
                ->where('tenant_id', $user->tenant_id)
                ->where('tenant_programs.is_active', true);
        })->exists()) {
            return false;
        }

        if ($user->ustadz) {
            return (int) $schedule->ustadzKelas?->ustadz_id === (int) $user->ustadz->id;
        }

        return $user->tenant_id !== null;
    }

    public function update(User $user, AbsensiUstadz $absensi): bool
    {
        return $this->view($user, $absensi);
    }
}
