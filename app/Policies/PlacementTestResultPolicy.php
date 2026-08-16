<?php

namespace App\Policies;

use App\Models\PlacementTestResult;
use App\Models\Program;
use App\Models\User;
use App\Policies\Concerns\HasReadonlyAccess;

class PlacementTestResultPolicy
{
    use HasReadonlyAccess;

    public function viewAny(User $user): bool
    {
        return $this->viewAnyAllowed($user);
    }

    public function view(User $user, PlacementTestResult $result, Program $program): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $result->tenant_id) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $program->tenant_id) {
            return false;
        }

        if ((int) $result->program_id !== (int) $program->id) {
            return false;
        }

        if ($user->santri) {
            return (int) $result->santri_id === (int) $user->santri->id;
        }

        if ($user->parent) {
            return $user->parent->hasSantri($result->santri_id);
        }

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function create(User $user, Program $program): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $program->tenant_id) {
            return false;
        }

        if ($user->parent !== null || $user->santri !== null) {
            return false;
        }

        return $user->tenant_id !== null || $user->ustadz !== null;
    }

    public function update(User $user, PlacementTestResult $result, Program $program): bool
    {
        return $this->modifyResultAllowed($user, $result, $program);
    }

    public function delete(User $user, PlacementTestResult $result, Program $program): bool
    {
        return $this->modifyResultAllowed($user, $result, $program);
    }

    private function modifyResultAllowed(User $user, PlacementTestResult $result, Program $program): bool
    {
        if ($user->isSuperAdmin()) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $result->tenant_id) {
            return false;
        }

        if ((int) $user->tenant_id !== (int) $program->tenant_id) {
            return false;
        }

        if ((int) $result->program_id !== (int) $program->id) {
            return false;
        }

        if ($user->parent !== null || $user->santri !== null) {
            return false;
        }

        return $user->tenant_id !== null || $user->ustadz !== null;
    }
}
