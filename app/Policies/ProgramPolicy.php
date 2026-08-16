<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Program;
use App\Models\Role;
use App\Models\User;

/**
 * Program is a global model (no tenant_id).
 * Super Admin manages programs globally.
 * Tenant users can only view active programs.
 */
final class ProgramPolicy
{
    /**
     * Determine whether the user can view any programs.
     * All authenticated users can view active programs.
     */
    public function viewAny(User $user): bool
    {
        // Super admin can view all including inactive
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Tenant users can view programs (filtered to active via scope)
        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    /**
     * Determine whether the user can view the program.
     * All authenticated users can view active programs.
     */
    public function view(User $user, Program $program): bool
    {
        // Super admin can view all
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Tenant users can only view active programs
        if (!$program->is_active && $user->tenant_id !== null) {
            return false;
        }

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    /**
     * Determine whether the user can create programs.
     * Only Super Admin can create programs.
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update the program.
     * Only Super Admin can update programs.
     */
    public function update(User $user, Program $program): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can delete the program.
     * Only Super Admin can delete programs.
     */
    public function delete(User $user, Program $program): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Program $program): bool
    {
        return $this->delete($user, $program);
    }

    public function forceDelete(User $user, Program $program): bool
    {
        return $this->delete($user, $program);
    }
}
