<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function before(User $user): ?bool
    {
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Invoice $invoice): bool
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->isSuperAdmin() && $invoice->status === 'unpaid';
    }

    public function cancel(User $user, Invoice $invoice): bool
    {
        return $user->isSuperAdmin() && in_array($invoice->status, ['unpaid', 'failed']);
    }

    public function markPaid(User $user, Invoice $invoice): bool
    {
        return $user->isSuperAdmin() && $invoice->status !== 'paid';
    }

    /**
     * Tenant admin/owner can view their own invoice.
     */
    public function viewTenant(User $user, Invoice $invoice): bool
    {
        return ! $user->isSuperAdmin()
            && $user->tenant_id !== null
            && (int) $user->tenant_id === (int) $invoice->tenant_id;
    }

    /**
     * Tenant admin/owner can initiate payment on an unpaid invoice.
     */
    public function pay(User $user, Invoice $invoice): bool
    {
        return ! $user->isSuperAdmin()
            && $user->tenant_id !== null
            && (int) $user->tenant_id === (int) $invoice->tenant_id
            && $invoice->status === 'unpaid';
    }
}
