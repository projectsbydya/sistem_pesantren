<?php

namespace App\Policies;

use App\Models\ProgramAssessmentConfig;
use App\Models\User;

class ProgramAssessmentConfigPolicy
{
    public function viewAny(User $user): bool
    {
        return ! $user->isSuperAdmin() && $user->isAdmin();
    }

    public function update(User $user, ProgramAssessmentConfig $config): bool
    {
        return ! $user->isSuperAdmin()
            && $user->isAdmin()
            && (int) $user->tenant_id === (int) $config->tenant_id;
    }
}
