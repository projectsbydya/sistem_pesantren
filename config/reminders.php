<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bill Reminder Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic bill payment reminders.
    |
    */

    // Enable email reminders
    'email_enabled' => env('REMINDER_EMAIL_ENABLED', true),

    // Enable WhatsApp reminders (requires integration)
    'whatsapp_enabled' => env('REMINDER_WHATSAPP_ENABLED', false),

    // Days before due date to send first reminder
    'days_before_due' => env('REMINDER_DAYS_BEFORE', 3),

    // Additional reminder intervals (days after due date for overdue reminders)
    'overdue_reminder_intervals' => [1, 7, 14],

    // Queue connection for reminders
    'queue_connection' => env('REMINDER_QUEUE_CONNECTION', config('queue.default')),

    // Queue name for reminders
    'queue_name' => env('REMINDER_QUEUE_NAME', 'reminders'),

    // Max retries for failed notifications
    'max_retries' => 3,

    /*
    |--------------------------------------------------------------------------
    | Subscription Reminder Settings
    |--------------------------------------------------------------------------
    |
    | Controls expiration reminder notifications for paid subscriptions.
    |
    */

    // Enable subscription expiration email reminders
    'subscription_email_enabled' => env('REMINDER_SUBSCRIPTION_EMAIL_ENABLED', true),

    // Days before subscription expiry to send reminders (comma-separated in .env)
    'subscription_reminder_days' => env('REMINDER_SUBSCRIPTION_DAYS', '14,7,3,1'),

    // Also send a notification on the day the subscription expires
    'subscription_send_expired' => env('REMINDER_SUBSCRIPTION_SEND_EXPIRED', true),
];
