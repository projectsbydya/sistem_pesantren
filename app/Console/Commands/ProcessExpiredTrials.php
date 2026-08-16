<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\SubscriptionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Process Expired Trials Command
 *
 * Automatically converts expired trial subscriptions to 'expired' status
 * and optionally suspends tenant access.
 *
 * Should be run daily via scheduler.
 */
class ProcessExpiredTrials extends Command
{
    protected $signature = 'subscriptions:process-expired-trials
                            {--dry-run : Preview changes without applying}
                            {--notify : Send notifications to affected tenants}
                            {--grace-period=0 : Additional grace period days after trial ends}';

    protected $description = 'Process expired trial subscriptions and update tenant status';

    public function __construct(
        private SubscriptionService $subscriptionService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $shouldNotify = $this->option('notify');
        $gracePeriodDays = (int) $this->option('grace-period');

        $this->info('Processing expired trial subscriptions...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be applied');
        }

        // Find trials that have expired (trial_ends_at in the past, beyond grace period)
        $expiredTrials = Subscription::trial()
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now()->subDays($gracePeriodDays))
            ->with('tenant')
            ->get();

        if ($expiredTrials->isEmpty()) {
            $this->info('No expired trials found.');
            return self::SUCCESS;
        }

        $this->info("Found {$expiredTrials->count()} expired trial(s) to process.");

        $processed = 0;
        $errors = 0;

        foreach ($expiredTrials as $subscription) {
            try {
                $this->processExpiredTrial($subscription, $dryRun, $shouldNotify);
                $processed++;
            } catch (\Throwable $e) {
                $errors++;
                $this->error("Failed to process subscription {$subscription->id}: {$e->getMessage()}");
                Log::error('Failed to process expired trial', [
                    'subscription_id' => $subscription->id,
                    'tenant_id' => $subscription->tenant_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Completed: {$processed} processed, {$errors} errors.");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function processExpiredTrial(Subscription $subscription, bool $dryRun, bool $shouldNotify): void
    {
        $tenant = $subscription->tenant;

        $this->info("Processing subscription #{$subscription->id} for tenant: {$tenant?->name}");

        if ($dryRun) {
            $this->line("  - Would mark subscription as 'expired'");
            $this->line("  - Trial ended at: {$subscription->trial_ends_at}");
            return;
        }

        // Mark subscription as expired
        $subscription->expire();

        // Update tenant trial status
        if ($tenant) {
            $tenant->update(['is_trial' => false]);
        }

        Log::info('Trial subscription auto-expired', [
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'tenant_name' => $tenant?->name,
        ]);

        // Send notification if enabled
        if ($shouldNotify && $tenant) {
            // TODO: Send trial expired notification to tenant admin
            // This would be implemented based on notification preferences
        }

        $this->info("  ✓ Subscription marked as expired");
    }
}
