<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Bill Reminders Scheduler
// Runs daily at 9 AM to send payment reminders
Schedule::command('reminders:bill-due')
    ->dailyAt('09:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/reminders.log'));

// Additional scheduler for overdue bills (weekly on Monday)
Schedule::command('reminders:bill-due --days-before=0')
    ->weeklyOn(1, '10:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/reminders.log'));

// =========================================================================
// TRIAL & SUBSCRIPTION EXPIRATION SCHEDULERS
// =========================================================================

// Send trial expiration reminders (7, 3, 1 days before)
// Runs daily at 8 AM
Schedule::command('subscriptions:send-trial-reminders')
    ->dailyAt('08:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/subscriptions.log'));

// Process expired trials
// Runs daily at 12 AM (midnight)
Schedule::command('subscriptions:process-expired-trials')
    ->dailyAt('00:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/subscriptions.log'));

// Process expired paid subscriptions
// Runs daily at 12:30 AM (after trial processing)
Schedule::command('subscriptions:process-expired --grace-period-check')
    ->dailyAt('00:30')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/subscriptions.log'));

// Send paid subscription expiration reminders (14, 7, 3, 1 days before)
// Runs daily at 8:30 AM (30 minutes after trial reminders)
Schedule::command('subscriptions:send-reminders --send-expired')
    ->dailyAt('08:30')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/subscriptions.log'));
