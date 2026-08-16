<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SubscriptionReminderLog;
use App\Models\User;
use App\Notifications\SubscriptionExpiredNotification;
use App\Notifications\SubscriptionExpirationReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Send Subscription Expiration Reminders
 *
 * Sends reminder notifications to tenant admins before a paid subscription expires,
 * and a final notification on the day the subscription expires.
 *
 * Default reminder schedule: 14, 7, 3, 1 days before expiration.
 * An "expired" notification is sent on the expiration day itself (days_before=0).
 *
 * Run daily via scheduler. Idempotent — duplicate reminders are prevented by
 * subscription_reminder_logs with a unique constraint per (subscription, type, days, date).
 *
 * Super Admin users are NEVER included as recipients.
 */
class SendSubscriptionReminders extends Command
{
    protected $signature = 'subscriptions:send-reminders
                            {--days=14,7,3,1 : Comma-separated days before expiration to send reminders}
                            {--send-expired : Also send expired notifications for subscriptions that expired today}
                            {--dry-run : Preview without sending or logging}';

    protected $description = 'Send subscription expiration reminders to tenant admins';

    public function handle(): int
    {
        $daysString = $this->option('days');
        $sendExpired = $this->option('send-expired');
        $dryRun = $this->option('dry-run');
        $reminderDays = array_map('intval', explode(',', $daysString));

        $this->info('Sending subscription expiration reminders...');
        $this->info('Reminder schedule: ' . implode(', ', $reminderDays) . ' days before expiration');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No notifications will be sent');
        }

        $totalSent    = 0;
        $totalSkipped = 0;

        // ── Expiration Reminders (N days before) ──────────────────────────────
        foreach ($reminderDays as $days) {
            $targetDate = now()->addDays($days)->startOfDay();

            $this->info("\nChecking subscriptions expiring in {$days} days ({$targetDate->format('Y-m-d')})...");

            $subscriptions = Subscription::active()
                ->whereNotNull('ends_at')
                ->whereDate('ends_at', $targetDate)
                ->with('tenant')
                ->get();

            if ($subscriptions->isEmpty()) {
                $this->line("  No subscriptions found expiring in {$days} days.");
                continue;
            }

            $this->info("  Found {$subscriptions->count()} subscription(s)");

            foreach ($subscriptions as $subscription) {
                $sent = $this->sendExpirationReminder($subscription, $days, $dryRun);
                $sent ? $totalSent++ : $totalSkipped++;
            }
        }

        // ── Expired Notifications (day of expiry) ─────────────────────────────
        if ($sendExpired) {
            $this->info("\nChecking subscriptions that expired today...");

            $expiredToday = Subscription::whereIn('status', ['active', 'expired'])
                ->whereNotNull('ends_at')
                ->whereDate('ends_at', today())
                ->with('tenant')
                ->get();

            if ($expiredToday->isEmpty()) {
                $this->line("  No subscriptions expired today.");
            } else {
                $this->info("  Found {$expiredToday->count()} subscription(s) expired today");

                foreach ($expiredToday as $subscription) {
                    $sent = $this->sendExpiredNotification($subscription, $dryRun);
                    $sent ? $totalSent++ : $totalSkipped++;
                }
            }
        }

        $this->info("\nCompleted: {$totalSent} reminders sent, {$totalSkipped} skipped.");

        return self::SUCCESS;
    }

    // =========================================================================
    // Private Helpers
    // =========================================================================

    private function sendExpirationReminder(Subscription $subscription, int $days, bool $dryRun): bool
    {
        $adminUser = $this->getAdminUser($subscription);

        if (! $adminUser) {
            return false;
        }

        if ($dryRun) {
            $this->line("  [DRY RUN] Would send {$days}-day expiry reminder to {$adminUser->email}");
            return true;
        }

        try {
            if (SubscriptionReminderLog::alreadySent(
                $subscription->id,
                SubscriptionReminderLog::TYPE_SUB_EXPIRING,
                $days
            )) {
                $this->line("  {$days}-day reminder already sent today for subscription {$subscription->id}");
                return false;
            }

            Notification::send($adminUser, new SubscriptionExpirationReminder($subscription, $days));

            SubscriptionReminderLog::record(
                $subscription->id,
                $subscription->tenant_id,
                SubscriptionReminderLog::TYPE_SUB_EXPIRING,
                $days
            );

            $this->info("  ✓ Sent {$days}-day reminder to {$adminUser->email}");

            Log::info('Subscription expiration reminder sent', [
                'subscription_id' => $subscription->id,
                'tenant_id'       => $subscription->tenant_id,
                'admin_user_id'   => $adminUser->id,
                'days_before'     => $days,
            ]);

            return true;

        } catch (\Throwable $e) {
            $this->error("  Failed to send reminder: {$e->getMessage()}");

            Log::error('Failed to send subscription expiration reminder', [
                'subscription_id' => $subscription->id,
                'tenant_id'       => $subscription->tenant_id,
                'days_before'     => $days,
                'error'           => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function sendExpiredNotification(Subscription $subscription, bool $dryRun): bool
    {
        $adminUser = $this->getAdminUser($subscription);

        if (! $adminUser) {
            return false;
        }

        if ($dryRun) {
            $this->line("  [DRY RUN] Would send expired notification to {$adminUser->email}");
            return true;
        }

        try {
            if (SubscriptionReminderLog::alreadySent(
                $subscription->id,
                SubscriptionReminderLog::TYPE_SUB_EXPIRED,
                0
            )) {
                $this->line("  Expired notification already sent today for subscription {$subscription->id}");
                return false;
            }

            Notification::send($adminUser, new SubscriptionExpiredNotification($subscription));

            SubscriptionReminderLog::record(
                $subscription->id,
                $subscription->tenant_id,
                SubscriptionReminderLog::TYPE_SUB_EXPIRED,
                0
            );

            $this->info("  ✓ Sent expired notification to {$adminUser->email}");

            Log::info('Subscription expired notification sent', [
                'subscription_id' => $subscription->id,
                'tenant_id'       => $subscription->tenant_id,
                'admin_user_id'   => $adminUser->id,
            ]);

            return true;

        } catch (\Throwable $e) {
            $this->error("  Failed to send expired notification: {$e->getMessage()}");

            Log::error('Failed to send subscription expired notification', [
                'subscription_id' => $subscription->id,
                'tenant_id'       => $subscription->tenant_id,
                'error'           => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get the tenant admin user for a subscription.
     * Super Admin is explicitly excluded.
     */
    private function getAdminUser(Subscription $subscription): ?User
    {
        $tenant = $subscription->tenant;

        if (! $tenant) {
            $this->warn("  Subscription {$subscription->id}: No tenant found");
            return null;
        }

        // Tenant admin — explicitly exclude super_admin role
        $adminUser = $tenant->users()
            ->where('role', 'admin')
            ->first();

        if (! $adminUser) {
            $this->warn("  Tenant {$tenant->id}: No admin user found");
            return null;
        }

        if (empty($adminUser->email)) {
            $this->warn("  Admin user {$adminUser->id}: No email address");
            return null;
        }

        return $adminUser;
    }
}
