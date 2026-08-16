<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tenant;
use App\Models\UsageLog;
use App\Models\User;

class UsageLogPolicy
{
    public function before(User $user): ?bool
    {
        // Super admin can do anything
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return false; // Only super admin (handled by before)
    }

    public function view(User $user, UsageLog $usageLog): bool
    {
        return false; // Only super admin (handled by before)
    }

    public function viewTenantUsage(User $user, Tenant $tenant): bool
    {
        // Tenant admin can view their own tenant
        return $user->tenant_id === $tenant->id && $user->isAdmin();
    }

    public function viewSuperAdminReport(User $user): bool
    {
        return false; // Only super admin (handled by before)
    }

    public function create(User $user): bool
    {
        return false; // Only super admin (handled by before)
    }

    public function delete(User $user, UsageLog $usageLog): bool
    {
        return false; // Only super admin (handled by before)
    }
}
