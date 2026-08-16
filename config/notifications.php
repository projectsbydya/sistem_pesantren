<?php

return [
    /*
    |--------------------------------------------------------------------------
    | General Notification Settings
    |--------------------------------------------------------------------------
    |
    | Controls which notification types are enabled.
    | All settings are configurable via .env so they can be toggled
    | per environment without code changes.
    |
    */

    'welcome_email_enabled' => env('NOTIFICATION_WELCOME_EMAIL_ENABLED', true),

    'password_reset_email_enabled' => env('NOTIFICATION_PASSWORD_RESET_EMAIL_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Bug Report Recipient
    |--------------------------------------------------------------------------
    |
    | Email address that receives new bug/error reports submitted by tenant
    | users. Leave empty/null to disable bug-report notifications.
    |
    */

    'bug_report_recipient' => env('NOTIFICATION_BUG_REPORT_RECIPIENT'),

    /*
    |--------------------------------------------------------------------------
    | Bug Report Sender Identity
    |--------------------------------------------------------------------------
    |
    | Dedicated From address/name used for bug-report notification emails.
    | These are intentionally separate from the default MAIL_FROM_* values
    | so bug reports can be sent with their own mailbox identity while still
    | using the existing SMTP transport.
    |
    | If left empty, the notification implementation can fall back to the
    | default mail from values.
    |
    */

    'bug_report_from_address' => env('NOTIFICATION_BUG_REPORT_FROM_ADDRESS'),
    'bug_report_from_name'    => env('NOTIFICATION_BUG_REPORT_FROM_NAME'),

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    |
    | Queue name for general (non-reminder) notifications.
    | Keep separate from 'reminders' queue so billing alerts
    | are not blocked by welcome/reset email bursts.
    |
    */

    'queue_name' => env('NOTIFICATION_QUEUE_NAME', 'notifications'),
];
