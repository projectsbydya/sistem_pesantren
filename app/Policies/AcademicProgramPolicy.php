<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AcademicProgramPolicy
{
    /**
     * Determine whether the user can view any subjects for a specific program type.
     * Relation-based: USER -> Ustadz -> UstadzKelas -> Subject
     */
    public function viewAny(User $user): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Admin can view all subjects in their tenant
        if ($user->isAdmin()) {
            return true;
        }

        // Ustadz can view subjects in their tenant
        if ($user->isUstadz()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the subject.
     */
    public function view(User $user, Subject $subject): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant ownership check
        if ((int) $subject->tenant_id !== (int) $user->tenant_id) {
            return false;
        }

        // Admin can view all subjects in their tenant
        if ($user->isAdmin()) {
            return true;
        }

        // Ustadz: can view all subjects in the tenant (management restricted to admin)
        if ($user->isUstadz()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create subjects for a specific program type.
     */
    public function create(User $user, string $programType): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Only tenant admins can create subjects
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the subject.
     */
    public function update(User $user, Subject $subject): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant ownership check
        if ((int) $subject->tenant_id !== (int) $user->tenant_id) {
            return false;
        }

        // Only tenant admins can update subjects
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the subject.
     */
    public function delete(User $user, Subject $subject): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant ownership check
        if ((int) $subject->tenant_id !== (int) $user->tenant_id) {
            return false;
        }

        // Only tenant admins can delete subjects
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can access program features.
     * Relation-based: USER -> Ustadz -> UstadzKelas (with program_type)
     */
    public function accessProgram(User $user, string $programType): bool
    {
        // Super Admin: no tenant data access
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Tenant members can access all programs in their tenant
        if ($user->tenant_id !== null) {
            return true;
        }

        // Ustadz can access programs they are assigned to
        if ($user->ustadz) {
            return $user->ustadz->ustadzKelas()
                ->where('program_type', $programType)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Subject $subject): bool
    {
        return $this->update($user, $subject);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Subject $subject): bool
    {
        return $this->delete($user, $subject);
    }
}
