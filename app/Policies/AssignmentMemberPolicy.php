<?php

namespace App\Policies;

use App\Models\AssignmentMember;
use App\Models\User;

class AssignmentMemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canAccessAny($user);
    }

    public function view(User $user, AssignmentMember $member): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $member->tenant_id) {
            return false;
        }

        if ($user->santri) {
            return (int) $member->santri_id === (int) $user->santri->id;
        }

        if ($user->parent) {
            return $user->parent->hasSantri($member->santri_id);
        }

        if ($user->ustadz) {
            return $this->canManageAssignment($user, $member);
        }

        return $user->tenant_id !== null;
    }

    public function update(User $user, AssignmentMember $member): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $member->tenant_id) {
            return false;
        }

        if ($user->parent !== null || $user->santri !== null) {
            return false;
        }

        if ($user->ustadz) {
            return $this->canManageAssignment($user, $member);
        }

        return $user->tenant_id !== null;
    }

    public function delete(User $user, AssignmentMember $member): bool
    {
        // Members are managed via assignment deletion or individual update.
        // Direct deletion is staff-only.
        return $this->update($user, $member);
    }

    private function canAccessAny(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        return $user->tenant_id !== null
            || $user->ustadz !== null
            || $user->parent !== null
            || $user->santri !== null;
    }

    private function canManageAssignment(User $user, AssignmentMember $member): bool
    {
        $assignment = $member->assignment;

        if ($assignment === null) {
            return false;
        }

        if ($assignment->kelas_id === null) {
            return false;
        }

        return $user->ustadz->ustadzKelas()
            ->where('kelas_id', $assignment->kelas_id)
            ->exists();
    }
}
