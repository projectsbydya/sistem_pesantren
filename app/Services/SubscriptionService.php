<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Plan;
use App\Models\SaasPayment;
use App\Models\Subscription;
use App\Models\SubscriptionLog;
use App\Models\Tenant;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionService
{
    /**
     * Create a new subscription for a tenant.
     *
     * @param array{
     *   tenant_id: int,
     *   package_name: string,
     *   billing_cycle: string,
     *   amount: float,
     *   starts_at?: \Carbon\Carbon|null,
     *   ends_at?: \Carbon\Carbon|null,
     *   status?: string,
     *   trial_ends_at?: \Carbon\Carbon|null,
     *   grace_period_ends_at?: \Carbon\Carbon|null,
     * } $data
     */
    public function create(array $data): Subscription
    {
        return DB::transaction(function () use ($data) {
            // Cancel any existing active subscription for this tenant
            $this->cancelExistingSubscriptions($data['tenant_id']);

            $subscription = Subscription::create([
                'tenant_id' => $data['tenant_id'],
                'package_name' => $data['package_name'],
                'billing_cycle' => $data['billing_cycle'],
                'amount' => $data['amount'],
                'starts_at' => $data['starts_at'] ?? now(),
                'ends_at' => $data['ends_at'] ?? null,
                'status' => $data['status'] ?? 'trial',
                'trial_ends_at' => $data['trial_ends_at'] ?? null,
                'grace_period_ends_at' => $data['grace_period_ends_at'] ?? null,
            ]);

            SubscriptionLog::create([
                'tenant_id'       => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'action'          => SubscriptionLog::ACTION_CREATE,
                'old_status'      => null,
                'new_status'      => $subscription->status,
                'notes'           => $data['notes'] ?? null,
                'performed_by'    => Auth::id(),
            ]);

            Log::info('Subscription created', [
                'subscription_id' => $subscription->id,
                'tenant_id'       => $subscription->tenant_id,
                'package'         => $subscription->package_name,
                'status'          => $subscription->status,
            ]);

            return $subscription;
        });
    }

    /**
     * Create a trial subscription for a new tenant from a plan.
     */
    public function createTrialFromPlan(Tenant $tenant, Plan $plan): Subscription
    {
        return DB::transaction(function () use ($tenant, $plan) {
            $this->cancelExistingSubscriptions($tenant->id);

            $hasTrial = $plan->trial_days > 0;
            $status   = $hasTrial ? 'trial' : 'active';
            $endsAt   = $hasTrial ? null : ($plan->billing_cycle === 'yearly'
                ? now()->addYear()
                : now()->addMonth());

            $subscription = Subscription::create([
                'tenant_id'     => $tenant->id,
                'plan_id'       => $plan->id,
                'package_name'  => $plan->name,
                'billing_cycle' => $plan->billing_cycle,
                'amount'        => $plan->price,
                'status'        => $status,
                'starts_at'     => now(),
                'ends_at'       => $endsAt,
                'trial_ends_at' => $hasTrial ? now()->addDays($plan->trial_days) : null,
            ]);

            SubscriptionLog::create([
                'tenant_id'       => $tenant->id,
                'subscription_id' => $subscription->id,
                'action'          => SubscriptionLog::ACTION_CREATE,
                'old_status'      => null,
                'new_status'      => $status,
                'notes'           => "Auto subscription from plan: {$plan->name}",
                'performed_by'    => Auth::id(),
            ]);

            Log::info('Subscription created from plan', [
                'subscription_id' => $subscription->id,
                'tenant_id'       => $tenant->id,
                'plan_id'         => $plan->id,
                'status'          => $status,
            ]);

            return $subscription;
        });
    }

    /**
     * Update an existing subscription.
     *
     * @param array<string, mixed> $data
     */
    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->update($data);

        Log::info('Subscription updated', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'changes' => $data,
        ]);

        return $subscription->fresh();
    }

    /**
     * Delete a subscription.
     */
    public function delete(Subscription $subscription): bool
    {
        $tenantId = $subscription->tenant_id;
        $subscriptionId = $subscription->id;

        $result = $subscription->delete();

        if ($result) {
            Log::info('Subscription deleted', [
                'subscription_id' => $subscriptionId,
                'tenant_id' => $tenantId,
            ]);
        }

        return $result;
    }

    /**
     * Activate a subscription.
     */
    public function activate(Subscription $subscription): Subscription
    {
        if ($subscription->isActive()) {
            return $subscription;
        }

        $subscription->activate();

        Log::info('Subscription activated', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
        ]);

        return $subscription->fresh();
    }

    /**
     * Suspend a subscription.
     */
    public function suspend(Subscription $subscription, ?string $reason = null): Subscription
    {
        if ($subscription->isSuspended()) {
            return $subscription;
        }

        $subscription->suspend();

        Log::warning('Subscription suspended', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'reason' => $reason,
        ]);

        return $subscription->fresh();
    }

    /**
     * Cancel a subscription.
     */
    public function cancel(Subscription $subscription, ?string $reason = null): Subscription
    {
        if ($subscription->isCancelled()) {
            return $subscription;
        }

        $subscription->cancel();

        Log::info('Subscription cancelled', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'reason' => $reason,
        ]);

        return $subscription->fresh();
    }

    /**
     * Renew a subscription.
     */
    public function renew(Subscription $subscription, ?array $overrideAttributes = null): Subscription
    {
        $attributes = $overrideAttributes ?? [];
        $subscription->renew($attributes);

        Log::info('Subscription renewed', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'new_ends_at' => $subscription->fresh()->ends_at?->toDateTimeString(),
        ]);

        return $subscription->fresh();
    }

    /**
     * Convert trial subscription to active.
     */
    public function convertTrial(Subscription $subscription, ?array $attributes = null): Subscription
    {
        if (!$subscription->isTrial()) {
            throw new \InvalidArgumentException('Only trial subscriptions can be converted.');
        }

        $data = array_merge([
            'package_name' => $subscription->package_name,
            'billing_cycle' => $subscription->billing_cycle,
            'amount' => $subscription->amount,
        ], $attributes ?? []);

        $subscription->convertFromTrial();
        $subscription->update($data);

        Log::info('Trial subscription converted to active', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
        ]);

        return $subscription->fresh();
    }

    /**
     * Extend grace period for a subscription.
     */
    public function extendGracePeriod(Subscription $subscription, int $days): Subscription
    {
        $subscription->extendGracePeriod($days);

        Log::info('Subscription grace period extended', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'days' => $days,
            'new_grace_period_ends_at' => $subscription->fresh()->grace_period_ends_at?->toDateTimeString(),
        ]);

        return $subscription->fresh();
    }

    /**
     * Get active subscription for a tenant.
     */
    public function getActiveSubscription(int $tenantId): ?Subscription
    {
        return Subscription::forTenant($tenantId)
            ->active()
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->latest()
            ->first();
    }

    /**
     * Get current subscription for a tenant (active or trial).
     */
    public function getCurrentSubscription(int $tenantId): ?Subscription
    {
        return Subscription::forTenant($tenantId)
            ->whereIn('status', ['active', 'trial'])
            ->latest()
            ->first();
    }

    /**
     * Get all subscriptions for a tenant.
     *
     * @return Collection<int, Subscription>
     */
    public function getTenantSubscriptions(int $tenantId, ?array $filters = null): Collection
    {
        $query = Subscription::forTenant($tenantId);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['billing_cycle'])) {
            $query->where('billing_cycle', $filters['billing_cycle']);
        }

        return $query->latest()->get();
    }

    /**
     * Check if tenant has an active subscription.
     */
    public function hasActiveSubscription(int $tenantId): bool
    {
        return $this->getActiveSubscription($tenantId) !== null;
    }

    /**
     * Check if tenant is in trial period.
     */
    public function isInTrial(int $tenantId): bool
    {
        $subscription = Subscription::forTenant($tenantId)
            ->trial()
            ->latest()
            ->first();

        return $subscription !== null && !$subscription->isTrialExpired();
    }

    /**
     * Get subscriptions expiring soon.
     *
     * @return Collection<int, Subscription>
     */
    public function getExpiringSoon(int $days = 7): Collection
    {
        return Subscription::active()
            ->endingSoon($days)
            ->with('tenant')
            ->get();
    }

    /**
     * Get subscriptions in grace period.
     *
     * @return Collection<int, Subscription>
     */
    public function getInGracePeriod(): Collection
    {
        return Subscription::inGracePeriod()
            ->with('tenant')
            ->get();
    }

    /**
     * Process expired trials — mark them as expired.
     *
     * @return int Number of subscriptions processed
     */
    public function processExpiredTrials(): int
    {
        $expiredTrials = Subscription::trial()
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->get();

        $count = 0;
        foreach ($expiredTrials as $subscription) {
            $subscription->expire();
            $count++;

            Log::info('Trial subscription auto-expired', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
            ]);
        }

        return $count;
    }

    /**
     * Process expired subscriptions — mark them as expired.
     *
     * @return int Number of subscriptions processed
     */
    public function processExpiredSubscriptions(): int
    {
        $expired = Subscription::whereIn('status', ['active', 'suspended'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->where(function ($query) {
                $query->whereNull('grace_period_ends_at')
                    ->orWhere('grace_period_ends_at', '<=', now());
            })
            ->get();

        $count = 0;
        foreach ($expired as $subscription) {
            $subscription->expire();
            $count++;

            Log::info('Subscription auto-expired', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
            ]);
        }

        return $count;
    }

    /**
     * Manually suspend a tenant and its active subscription.
     * Idempotent: no-op if already suspended.
     */
    public function suspendTenant(Tenant $tenant, ?string $notes = null): Tenant
    {
        return DB::transaction(function () use ($tenant, $notes) {
            $tenantOldStatus = $tenant->status ?? ($tenant->is_active ? 'active' : 'inactive');

            // Idempotent guard
            if ($tenant->status === 'suspended' && !$tenant->is_active) {
                return $tenant;
            }

            $tenant->update([
                'status'    => 'suspended',
                'is_active' => false,
            ]);

            $subscription = Subscription::forTenant($tenant->id)
                ->whereIn('status', ['active', 'trial'])
                ->latest()
                ->first();

            $subscriptionOldStatus = $subscription?->status;

            if ($subscription && !$subscription->isSuspended()) {
                $subscription->suspend();
            }

            SubscriptionLog::create([
                'tenant_id'       => $tenant->id,
                'subscription_id' => $subscription?->id,
                'action'          => SubscriptionLog::ACTION_SUSPEND_TENANT,
                'old_status'      => $subscriptionOldStatus ?? $tenantOldStatus,
                'new_status'      => 'suspended',
                'notes'           => $notes,
                'performed_by'    => Auth::id(),
            ]);

            Log::warning('Tenant suspended manually', [
                'tenant_id'       => $tenant->id,
                'subscription_id' => $subscription?->id,
                'performed_by'    => Auth::id(),
                'notes'           => $notes,
            ]);

            return $tenant->fresh();
        });
    }

    /**
     * Manually activate a tenant and its most recent subscription.
     * Idempotent: no-op if already active.
     */
    public function activateTenant(Tenant $tenant, ?string $notes = null): Tenant
    {
        return DB::transaction(function () use ($tenant, $notes) {
            // Idempotent guard
            if ($tenant->status === 'active' && $tenant->is_active) {
                return $tenant;
            }

            $tenant->update([
                'status'    => 'active',
                'is_active' => true,
            ]);

            $subscription = Subscription::forTenant($tenant->id)
                ->where('status', 'suspended')
                ->latest()
                ->first();

            $subscriptionOldStatus = $subscription?->status;

            if ($subscription) {
                $subscription->activate();
            }

            SubscriptionLog::create([
                'tenant_id'       => $tenant->id,
                'subscription_id' => $subscription?->id,
                'action'          => SubscriptionLog::ACTION_ACTIVATE_TENANT,
                'old_status'      => $subscriptionOldStatus ?? 'suspended',
                'new_status'      => 'active',
                'notes'           => $notes,
                'performed_by'    => Auth::id(),
            ]);

            Log::info('Tenant activated manually', [
                'tenant_id'       => $tenant->id,
                'subscription_id' => $subscription?->id,
                'performed_by'    => Auth::id(),
                'notes'           => $notes,
            ]);

            return $tenant->fresh();
        });
    }

    /**
     * Generate an invoice for a subscription.
     */
    public function generateInvoice(Subscription $subscription, ?array $overrides = []): Invoice
    {
        return DB::transaction(function () use ($subscription, $overrides) {
            $invoice = Invoice::create([
                'tenant_id'       => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'invoice_number'  => Invoice::generateNumber(),
                'amount'          => $overrides['amount'] ?? $subscription->amount,
                'status'          => 'unpaid',
                'due_date'        => $overrides['due_date'] ?? now()->addDays(7)->toDateString(),
                'period_label'    => $overrides['period_label'] ?? now()->format('F Y'),
                'notes'           => $overrides['notes'] ?? null,
            ]);

            SubscriptionLog::create([
                'tenant_id'       => $subscription->tenant_id,
                'subscription_id' => $subscription->id,
                'action'          => SubscriptionLog::ACTION_INVOICE_CREATED,
                'old_status'      => null,
                'new_status'      => 'unpaid',
                'notes'           => "Invoice {$invoice->invoice_number} generated",
                'performed_by'    => Auth::id(),
            ]);

            Log::info('Invoice generated', [
                'invoice_id'      => $invoice->id,
                'invoice_number'  => $invoice->invoice_number,
                'subscription_id' => $subscription->id,
                'tenant_id'       => $subscription->tenant_id,
            ]);

            return $invoice;
        });
    }

    /**
     * Record a payment submission for an invoice.
     */
    public function recordPayment(Invoice $invoice, array $data): SaasPayment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $payment = SaasPayment::create([
                'invoice_id'     => $invoice->id,
                'amount'         => $data['amount'] ?? $invoice->amount,
                'payment_method' => $data['payment_method'],
                'transfer_proof' => $data['transfer_proof'] ?? null,
                'reference_id'   => $data['reference_id'] ?? null,
                'status'         => 'pending',
                'notes'          => $data['notes'] ?? null,
                'paid_at'        => $data['paid_at'] ?? now(),
            ]);

            Log::info('Payment recorded', [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
            ]);

            return $payment;
        });
    }

    /**
     * Confirm a payment — mark invoice as paid, activate subscription if needed.
     *
     * @param  int|null $performedBy  User ID performing the action. Pass null for automated/webhook calls.
     */
    public function confirmPayment(SaasPayment $payment, ?string $notes = null, ?int $performedBy = null): SaasPayment
    {
        $actor = $performedBy ?? Auth::id();

        return DB::transaction(function () use ($payment, $notes, $actor) {
            if (!$payment->isPending()) {
                return $payment;
            }

            $payment->update([
                'status'       => 'confirmed',
                'confirmed_by' => $actor,
                'confirmed_at' => now(),
                'notes'        => $notes ?? $payment->notes,
            ]);

            $invoice = $payment->invoice;
            $invoice->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $subscription = $invoice->subscription;
            if ($subscription && in_array($subscription->status, ['trial', 'suspended', 'expired'])) {
                $oldStatus = $subscription->status;
                $subscription->convertFromTrial();

                SubscriptionLog::create([
                    'tenant_id'       => $subscription->tenant_id,
                    'subscription_id' => $subscription->id,
                    'action'          => SubscriptionLog::ACTION_PAYMENT_CONFIRMED,
                    'old_status'      => $oldStatus,
                    'new_status'      => 'active',
                    'notes'           => "Payment confirmed: {$invoice->invoice_number}",
                    'performed_by'    => $actor,
                ]);
            }

            Log::info('Payment confirmed', [
                'payment_id'   => $payment->id,
                'invoice_id'   => $invoice->id,
                'performed_by' => $actor,
            ]);

            return $payment->fresh();
        });
    }

    /**
     * Reject a payment.
     *
     * @param  int|null $performedBy  User ID performing the action. Pass null for automated/webhook calls.
     */
    public function rejectPayment(SaasPayment $payment, ?string $notes = null, ?int $performedBy = null): SaasPayment
    {
        $actor = $performedBy ?? Auth::id();

        return DB::transaction(function () use ($payment, $notes, $actor) {
            if (!$payment->isPending()) {
                return $payment;
            }

            $payment->update([
                'status'       => 'rejected',
                'confirmed_by' => $actor,
                'confirmed_at' => now(),
                'notes'        => $notes,
            ]);

            $invoice = $payment->invoice;
            $invoice->update(['status' => 'failed']);

            Log::warning('Payment rejected', [
                'payment_id'   => $payment->id,
                'invoice_id'   => $invoice->id,
                'performed_by' => $actor,
                'notes'        => $notes,
            ]);

            return $payment->fresh();
        });
    }

    /**
     * Process an incoming payment gateway webhook end-to-end.
     *
     * Flow:
     *   1. Look up the SaasPayment by external_id (gateway transaction ID).
     *   2. Fetch the live status from the gateway.
     *   3a. paid    → confirmPayment()  → invoice paid, subscription activated.
     *   3b. failed/expired → rejectPayment() → invoice marked failed.
     *   3c. pending → no-op (payment still in progress).
     *
     * Returns the action taken: 'confirmed' | 'rejected' | 'skipped' | 'not_found'.
     *
     * Never throws — all exceptions are caught and logged so the HTTP handler
     * can always return 200 to the gateway (prevents flood of retries).
     */
    public function handleWebhookPayment(string $externalId, string $gatewayName): string
    {
        try {
            $payment = SaasPayment::where('external_id', $externalId)
                ->where('payment_method', $gatewayName)
                ->latest()
                ->first();

            if ($payment === null) {
                Log::warning('Webhook: SaasPayment not found', [
                    'external_id' => $externalId,
                    'gateway'     => $gatewayName,
                ]);
                return 'not_found';
            }

            if (! $payment->isPending()) {
                Log::info('Webhook: payment already processed, skipping', [
                    'payment_id'  => $payment->id,
                    'status'      => $payment->status,
                    'external_id' => $externalId,
                ]);
                return 'skipped';
            }

            $gateway    = PaymentGatewayFactory::make($gatewayName);
            $statusData = $gateway->getPaymentStatus($externalId);
            $status     = $statusData['status'] ?? 'pending';

            Log::info('Webhook: gateway status fetched', [
                'external_id' => $externalId,
                'gateway'     => $gatewayName,
                'status'      => $status,
            ]);

            if ($status === 'paid') {
                $this->confirmPayment($payment, "Confirmed via webhook ({$gatewayName})", performedBy: null);
                return 'confirmed';
            }

            if (in_array($status, ['failed', 'expired'], true)) {
                $this->rejectPayment($payment, "Rejected via webhook ({$gatewayName}): {$status}", performedBy: null);
                return 'rejected';
            }

            return 'skipped';

        } catch (\Throwable $e) {
            Log::error('Webhook: handleWebhookPayment threw an exception', [
                'external_id' => $externalId,
                'gateway'     => $gatewayName,
                'error'       => $e->getMessage(),
            ]);
            return 'error';
        }
    }

    /**
     * Get all active plans.
     *
     * @return Collection<int, Plan>
     */
    public function getActivePlans(): Collection
    {
        return Plan::active()->orderBy('price')->get();
    }

    /**
     * Generate (or retrieve) a payment link for an invoice via the configured gateway.
     *
     * Idempotent: if a pending SaasPayment with a payment_url already exists for
     * the invoice, the existing URL is returned without hitting the gateway again.
     *
     * On success a SaasPayment record is created (status=pending) carrying:
     *   - external_id  : gateway's transaction/invoice ID
     *   - payment_url  : URL the customer opens to complete payment
     *   - payment_method: gateway name (e.g. 'xendit', 'midtrans')
     *
     * @throws \App\Services\Payments\Exceptions\PaymentGatewayException  On gateway failure.
     * @throws \RuntimeException  If the invoice is already paid or cancelled.
     */
    public function generatePaymentLink(Invoice $invoice): string
    {
        if (in_array($invoice->status, ['paid', 'cancelled'], true)) {
            throw new \RuntimeException(
                "Cannot generate payment link for invoice {$invoice->invoice_number}: status is {$invoice->status}."
            );
        }

        $existing = SaasPayment::where('invoice_id', $invoice->id)
            ->where('status', 'pending')
            ->whereNotNull('payment_url')
            ->latest()
            ->first();

        if ($existing !== null) {
            Log::info('generatePaymentLink: returning existing payment link', [
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'payment_id'     => $existing->id,
                'payment_url'    => $existing->payment_url,
            ]);

            return $existing->payment_url;
        }

        $gateway = PaymentGatewayFactory::make();
        $result  = $gateway->createPaymentLink($invoice);

        $payment = DB::transaction(function () use ($invoice, $gateway, $result) {
            return SaasPayment::create([
                'invoice_id'     => $invoice->id,
                'amount'         => $invoice->amount,
                'payment_method' => $gateway->getGatewayName(),
                'external_id'    => $result['external_id'],
                'payment_url'    => $result['payment_url'],
                'status'         => 'pending',
            ]);
        });

        Log::info('generatePaymentLink: payment link created', [
            'invoice_id'     => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'payment_id'     => $payment->id,
            'gateway'        => $gateway->getGatewayName(),
            'external_id'    => $result['external_id'],
            'payment_url'    => $result['payment_url'],
        ]);

        return $payment->payment_url;
    }

    /**
     * Cancel existing active/trial subscriptions for a tenant.
     */
    private function cancelExistingSubscriptions(int $tenantId): void
    {
        Subscription::forTenant($tenantId)
            ->whereIn('status', ['active', 'trial', 'suspended'])
            ->update(['status' => 'cancelled']);
    }
}
