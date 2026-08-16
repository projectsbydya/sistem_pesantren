<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\Tenant;
use App\Notifications\BillDueReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class SendBillReminders extends Command
{
    protected $signature = 'reminders:bill-due
                            {--days-before=3 : Days before due date to send reminder}
                            {--tenant= : Specific tenant ID to process}';

    protected $description = 'Send payment reminders for upcoming and overdue bills';

    public function handle(): int
    {
        $daysBefore = (int) $this->option('days-before');
        $specificTenant = $this->option('tenant');

        $this->info("Sending bill reminders ({$daysBefore} days before due date)...");

        // Get tenants to process
        $tenants = $specificTenant
            ? Tenant::where('id', $specificTenant)->get()
            : Tenant::where('is_active', true)->get();

        $totalSent = 0;
        $totalSkipped = 0;

        foreach ($tenants as $tenant) {
            $this->info("Processing tenant: {$tenant->name}");

            // Find bills that need reminders:
            // 1. Unpaid status
            // 2. Due date is within reminder window or overdue
            // 3. No reminder sent today
            $bills = Bill::where('tenant_id', $tenant->id)
                ->where('status', 'unpaid')
                ->where(function ($query) use ($daysBefore) {
                    $query->whereDate('due_date', '<=', now()->addDays($daysBefore))
                          ->whereDate('due_date', '>=', now()->subDays(30)); // Don't remind very old bills
                })
                ->whereDoesntHave('remindersSentToday')
                ->with(['santri.parents.user', 'tenant'])
                ->get();

            foreach ($bills as $bill) {
                $sent = $this->sendReminderForBill($bill);
                $sent ? $totalSent++ : $totalSkipped++;
            }
        }

        $this->info("Completed: {$totalSent} reminders sent, {$totalSkipped} skipped.");

        return self::SUCCESS;
    }

    private function sendReminderForBill(Bill $bill): bool
    {
        $santri = $bill->santri;

        if (!$santri) {
            $this->warn("Bill {$bill->id}: No santri found");
            return false;
        }

        // Get parents of this santri
        $parents = $santri->parents()->with('user')->get();

        if ($parents->isEmpty()) {
            $this->warn("Bill {$bill->id}: Santri has no parents");
            return false;
        }

        $sent = false;

        foreach ($parents as $parent) {
            $user = $parent->user;

            if (!$user) {
                $this->warn("Bill {$bill->id}: Parent {$parent->id} has no user");
                continue;
            }

            // Fail safely - skip if no email
            if (empty($user->email)) {
                $this->warn("Bill {$bill->id}: User {$user->id} has no email");
                continue;
            }

            try {
                Notification::send($user, new BillDueReminder($bill));
                $sent = true;
                $this->info("Bill {$bill->id}: Reminder sent to {$user->email}");
            } catch (\Throwable $e) {
                $this->error("Bill {$bill->id}: Failed to send - {$e->getMessage()}");
                Log::error('BillDueReminder dispatch failed', [
                    'bill_id'   => $bill->id,
                    'tenant_id' => $bill->tenant_id,
                    'user_id'   => $user->id,
                    'email'     => $user->email,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        // Log that reminder was sent today (to prevent duplicates)
        if ($sent) {
            DB::table('bill_reminder_logs')->insert([
                'bill_id' => $bill->id,
                'reminder_date' => now()->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $sent;
    }
}
