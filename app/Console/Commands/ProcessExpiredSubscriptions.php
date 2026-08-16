<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Process Expired Subscriptions Command
 *
 * Automatically converts expired active/paid subscriptions to 'expired' status
 * after their end date passes and grace period ends.
 *
 * Should be run daily via scheduler.
 */
class ProcessExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:process-expired
                            {--dry-run : Preview changes without applying}
                            {--grace-period-check : Only process if grace period has also ended}';

    protected $description = 'Process expired active/paid subscriptions';

    public function __construct(
        private SubscriptionService $subscriptionService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $checkGracePeriod = $this->option('grace-period-check');

        $this->info('Processing expired subscriptions...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be applied');
        }

        // Find active/suspended subscriptions that have passed their end date
        $query = Subscription::whereIn('status', ['active', 'suspended'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', now())
            ->with('tenant');

        // If grace period check is enabled, also verify grace period has ended
        if ($checkGracePeriod) {
            $query->where(function ($q) {
                $q->whereNull('grace_period_ends_at')
                    ->orWhere('grace_period_ends_at', '<=', now());
            });
        }

        $expiredSubscriptions = $query->get();

        if ($expiredSubscriptions->isEmpty()) {
            $this->info('No expired subscriptions found.');
            return self::SUCCESS;
        }

        $this->info("Found {$expiredSubscriptions->count()} expired subscription(s) to process.");

        $processed = 0;
        $errors = 0;

        foreach ($expiredSubscriptions as $subscription) {
            try {
                $this->processExpiredSubscription($subscription, $dryRun);
                $processed++;
            } catch (\Throwable $e) {
                $errors++;
                $this->error("Failed to process subscription {$subscription->id}: {$e->getMessage()}");
                Log::error('Failed to process expired subscription', [
                    'subscription_id' => $subscription->id,
                    'tenant_id' => $subscription->tenant_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Completed: {$processed} processed, {$errors} errors.");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function processExpiredSubscription(Subscription $subscription, bool $dryRun): void
    {
        $tenant = $subscription->tenant;

        $this->info("Processing subscription #{$subscription->id} for tenant: {$tenant?->name}");

        if ($dryRun) {
            $this->line("  - Would mark subscription as 'expired'");
            $this->line("  - Subscription ended at: {$subscription->ends_at}");
            if ($subscription->grace_period_ends_at) {
                $this->line("  - Grace period ended at: {$subscription->grace_period_ends_at}");
            }
            return;
        }

        // Mark subscription as expired
        $subscription->expire();

        Log::info('Subscription auto-expired', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'tenant_name' => $tenant?->name,
            'ends_at' => $subscription->ends_at?->toDateTimeString(),
        ]);

        $this->info("  ✓ Subscription marked as expired");
    }
}
