<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Resolves notification recipients based on the existing role/permission
 * architecture.
 *
 * This service contains only recipient resolution logic. It does not send
 * notifications, build messages, or hold event-specific business rules.
 */
class NotificationRecipientService
{
    /**
     * Resolve all authorized Super Admin users.
     *
     * Super Admins are identified by the authoritative `is_super_admin` flag.
     * Normal tenant users always have this flag set to false, so this query
     * cannot accidentally return tenant accounts.
     *
     * @return Collection<int, User>
     */
    public function superAdmins(): Collection
    {
        return User::query()
            ->where('is_super_admin', true)
            ->where('is_active', true)
            ->get();
    }
}
