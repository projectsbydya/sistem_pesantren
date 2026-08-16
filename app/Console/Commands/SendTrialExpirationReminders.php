<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\TrialExpirationReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Send Trial Expiration Reminders Command
 *
 * Sends reminder notifications to tenant admins before trial expires.
 * Default reminder schedule: 7 days, 3 days, 1 day before expiration.
 *
 * Should be run daily via scheduler.
 */
class SendTrialExpirationReminders extends Command
{
    protected $signature = 'subscriptions:send-trial-reminders
                            {--days=7,3,1 : Comma-separated days before expiration to send reminders}
                            {--dry-run : Preview without sending}';

    protected $description = 'Send trial expiration reminders to tenant admins';

    public function handle(): int
    {
        $daysString = $this->option('days');
        $dryRun = $this->option('dry-run');
        $reminderDays = array_map('intval', explode(',', $daysString));

        $this->info('Sending trial expiration reminders...');
        $this->info('Reminder schedule: ' . implode(', ', $reminderDays) . ' days before expiration');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No notifications will be sent');
        }

        $totalSent = 0;
        $totalSkipped = 0;

        foreach ($reminderDays as $days) {
            $targetDate = now()->addDays($days)->startOfDay();
            
            $this->info("\nChecking trials expiring in {$days} days (on {$targetDate->format('Y-m-d')})...");

            // Find trials expiring on the target date
            $trials = Subscription::trial()
                ->whereNotNull('trial_ends_at')
                ->whereDate('trial_ends_at', $targetDate)
                ->with('tenant')
                ->get();

            if ($trials->isEmpty()) {
                $this->line("  No trials found expiring in {$days} days.");
                continue;
            }

            $this->info("  Found {$trials->count()} trial(s)");

            foreach ($trials as $subscription) {
                $sent = $this->sendReminder($subscription, $days, $dryRun);
                $sent ? $totalSent++ : $totalSkipped++;
            }
        }

        $this->info("\nCompleted: {$totalSent} reminders sent, {$totalSkipped} skipped.");

        return self::SUCCESS;
    }

    private function sendReminder(Subscription $subscription, int $daysUntilExpiration, bool $dryRun): bool
    {
        $tenant = $subscription->tenant;

        if (!$tenant) {
            $this->warn("  Subscription {$subscription->id}: No tenant found");
            return false;
        }

        // Get tenant admin user
        $adminUser = $tenant->users()
            ->where('role', 'admin')
            ->first();

        if (!$adminUser) {
            $this->warn("  Tenant {$tenant->id}: No admin user found");
            return false;
        }

        if (empty($adminUser->email)) {
            $this->warn("  Admin user {$adminUser->id}: No email address");
            return false;
        }

        if ($dryRun) {
            $this->line("  [DRY RUN] Would send {$daysUntilExpiration}-day reminder to {$adminUser->email}");
            return true;
        }

        try {
            // Check if reminder already sent today for this subscription and timeframe
            $alreadySent = \DB::table('subscription_reminder_logs')
                ->where('subscription_id', $subscription->id)
                ->where('days_before', $daysUntilExpiration)
                ->whereDate('reminder_date', today())
                ->exists();

            if ($alreadySent) {
                $this->line("  {$daysUntilExpiration}-day reminder already sent today for subscription {$subscription->id}");
                return false;
            }

            Notification::send($adminUser, new TrialExpirationReminder($subscription, $daysUntilExpiration));

            // Log reminder sent
            \DB::table('subscription_reminder_logs')->insert([
                'subscription_id' => $subscription->id,
                'tenant_id' => $tenant->id,
                'days_before' => $daysUntilExpiration,
                'reminder_date' => today(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info("  ✓ Sent {$daysUntilExpiration}-day reminder to {$adminUser->email}");

            Log::info('Trial expiration reminder sent', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $tenant->id,
                'admin_user_id' => $adminUser->id,
                'days_before' => $daysUntilExpiration,
            ]);

            return true;

        } catch (\Throwable $e) {
            $this->error("  Failed to send reminder: {$e->getMessage()}");
            Log::error('Failed to send trial expiration reminder', [
                'subscription_id' => $subscription->id,
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
