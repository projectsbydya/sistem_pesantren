<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\SantriProgram;
use App\Models\User;

final class SantriProgramPolicy
{
    /**
     * Determine whether the user can view any santri programs.
     */
    public function viewAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN) || $user->ustadz !== null) {
            return $user->tenant_id !== null;
        }

        // Parent and Santri can view their own records
        if ($user->parent !== null || $user->santri !== null) {
            return $user->tenant_id !== null;
        }

        return false;
    }

    /**
     * Determine whether the user can view the santri program.
     */
    public function view(User $user, SantriProgram $santriProgram): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $santriProgram->tenant_id) {
            return false;
        }

        if ($user->hasRole(Role::TENANT_ADMIN)) {
            return true;
        }

        // Ustadz can view if they teach the related class
        if ($user->ustadz !== null) {
            $kelasIds = $user->ustadz->ustadzKelas->pluck('kelas_id')->toArray();
            return in_array($santriProgram->kelas_id, $kelasIds, false);
        }

        // Santri can only view their own program
        if ($user->santri !== null) {
            return (int) $santriProgram->santri_id === (int) $user->santri->id;
        }

        // Parent can view their children's programs
        if ($user->parent !== null) {
            $childIds = $user->parent->santri->pluck('id')->toArray();
            return in_array($santriProgram->santri_id, $childIds, false);
        }

        return false;
    }

    /**
     * Determine whether the user can create santri programs.
     */
    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return $user->hasRole(Role::TENANT_ADMIN) && $user->tenant_id !== null;
    }

    /**
     * Determine whether the user can update the santri program.
     */
    public function update(User $user, SantriProgram $santriProgram): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $santriProgram->tenant_id) {
            return false;
        }

        return $user->hasRole(Role::TENANT_ADMIN);
    }

    /**
     * Determine whether the user can delete the santri program.
     */
    public function delete(User $user, SantriProgram $santriProgram): bool
    {
        return $this->update($user, $santriProgram);
    }

    public function restore(User $user, SantriProgram $santriProgram): bool
    {
        return $this->delete($user, $santriProgram);
    }

    public function forceDelete(User $user, SantriProgram $santriProgram): bool
    {
        return $this->delete($user, $santriProgram);
    }
}
