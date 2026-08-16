<?php

namespace App\Policies;

use App\Models\BugReport;
use App\Models\User;

/**
 * BugReportPolicy — support/ops access control.
 *
 * - Tenant users (admin, ustadz, santri, parent, bendahara) may only CREATE
 *   bug reports. They cannot list or inspect reports.
 * - Super Admin may view/manage reports across all tenants.
 * - Updates/deletes are reserved for future ops workflows and are currently
 *   denied to everyone through policy.
 *
 * Tenant and reporter identity must always come from the authenticated
 * context (TenantService / auth()->user()), never from request input.
 */
class BugReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, BugReport $bugReport): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        // Super admins manage the platform; they do not submit tenant reports.
        if ($user->isSuperAdmin()) {
            return false;
        }

        // Any authenticated user bound to a tenant may report an issue.
        return $user->isTenantUser();
    }

    public function update(User $user, BugReport $bugReport): bool
    {
        return false;
    }

    public function delete(User $user, BugReport $bugReport): bool
    {
        return false;
    }
}
