<?php

namespace App\Policies\Concerns;

use App\Models\UstadzKelas;
use App\Models\User;

trait HasReadonlyAccess
{
    /**
     * Allow tenant members, ustadz, parents and students to view the index.
     * Super admins are intentionally blocked from tenant operational data.
     */
    protected function viewAnyAllowed(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return $user->tenant_id !== null
            || $user->ustadz !== null
            || $user->parent !== null
            || $user->santri !== null;
    }

    /**
     * Allow viewing a record only when it belongs to the user's tenant and,
     * for students/parents, only when it belongs to themselves or their children.
     */
    protected function viewRecordAllowed(User $user, $record, ?int $santriId = null): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $record->tenant_id) {
            return false;
        }

        if ($user->santri) {
            return $santriId !== null
                && (int) $santriId === (int) $user->santri->id;
        }

        if ($user->parent) {
            return $santriId !== null
                && $user->parent->hasSantri($santriId);
        }

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    /**
     * Creation is restricted to staff/ustadz; students and parents are readonly.
     */
    protected function createAllowed(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ($user->parent !== null || $user->santri !== null) {
            return false;
        }

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    /**
     * Modification/deletion is restricted to staff/ustadz and the record's tenant;
     * students and parents are readonly.
     */
    protected function modifyAllowed(User $user, $record): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $record->tenant_id) {
            return false;
        }

        if ($user->parent !== null || $user->santri !== null) {
            return false;
        }

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    /**
     * Recording on behalf of a specific ustadz-kelas assignment.
     * Students and parents cannot record; ustadz may only record for their own classes.
     */
    protected function recordForAllowed(User $user, UstadzKelas $ustadzKelas): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $ustadzKelas->tenant_id) {
            return false;
        }

        if ($user->parent !== null || $user->santri !== null) {
            return false;
        }

        if ($user->ustadz) {
            return $user->ustadz->ustadzKelas()->where('id', $ustadzKelas->id)->exists();
        }

        return $user->tenant_id !== null || $user->ustadz !== null;
    }
}
