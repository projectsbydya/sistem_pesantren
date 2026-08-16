<?php

namespace App\Policies;

use App\Models\HafalanQuran;
use App\Models\UstadzKelas;
use App\Models\User;

/**
 * HafalanQuranPolicy — relation-based access control for hafalan quran records.
 *
 * USER → RELATION → HafalanQuran
 * - Tenant Member : full access (tenant-scoped)
 * - Ustadz : create/delete only for their ustadz_kelas
 * - Super Admin : blocked from tenant data
 */
class HafalanQuranPolicy
{
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

        // Tenant members or ustadz can view
        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function create(User $user): bool
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

        // Tenant members or ustadz can create
        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    /**
     * Can user record hafalan for a specific UstadzKelas assignment?
     * Relation-based: USER -> Ustadz -> UstadzKelas
     */
    public function recordFor(User $user, UstadzKelas $ustadzKelas): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $ustadzKelas->tenant_id) {
            return false;
        }

        // Ustadz: can only record for their own ustadz_kelas assignments
        if ($user->ustadz) {
            return $user->ustadz->ustadzKelas()
                ->where('id', $ustadzKelas->id)
                ->exists();
        }

        // Tenant members (admin/staff) can record for any ustadz_kelas in their tenant
        return $user->tenant_id !== null;
    }

    public function delete(User $user, HafalanQuran $record): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant isolation
        if ((int) $user->tenant_id !== (int) $record->tenant_id) {
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

        // Ustadz: can only delete their own records
        if ($user->ustadz) {
            return $record->ustadzKelas()->where('ustadz_id', $user->ustadz->id)->exists();
        }

        // Tenant members (admin/staff) can delete any record in their tenant
        return $user->tenant_id !== null;
    }
}
