<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for WhatsApp notifications via multiple provider support.
    | Supported providers: twilio, fonnte, wablas
    |
    */

    // Enable/disable WhatsApp globally
    'enabled' => env('WHATSAPP_ENABLED', false),

    // Default provider (twilio, fonnte, wablas)
    'default_provider' => env('WHATSAPP_PROVIDER', 'twilio'),

    // Default sender/WhatsApp number (with country code, e.g., 6281234567890)
    'default_sender' => env('WHATSAPP_DEFAULT_SENDER', ''),

    // Queue settings for WhatsApp messages
    'queue' => [
        'enabled' => env('WHATSAPP_QUEUE_ENABLED', true),
        'connection' => env('WHATSAPP_QUEUE_CONNECTION', config('queue.default')),
        'name' => env('WHATSAPP_QUEUE_NAME', 'notifications'),
    ],

    // Retry settings
    'retry' => [
        'tries' => (int) env('WHATSAPP_RETRY_TRIES', 3),
        'backoff' => [60, 300, 600], // seconds
    ],

    // Provider-specific configurations
    'providers' => [
        'twilio' => [
            'enabled' => env('WHATSAPP_TWILIO_ENABLED', true),
            'sid' => env('WHATSAPP_TWILIO_SID', ''),
            'auth_token' => env('WHATSAPP_TWILIO_AUTH_TOKEN', ''),
            'from' => env('WHATSAPP_TWILIO_FROM', ''), // Twilio WhatsApp number
            'api_url' => env('WHATSAPP_TWILIO_API_URL', 'https://api.twilio.com/2010-04-01'),
        ],

        'fonnte' => [
            'enabled' => env('WHATSAPP_FONNTE_ENABLED', false),
            'token' => env('WHATSAPP_FONNTE_TOKEN', ''),
            'api_url' => env('WHATSAPP_FONNTE_API_URL', 'https://api.fonnte.com/send'),
            'country_code' => env('WHATSAPP_FONNTE_COUNTRY_CODE', '62'),
        ],

        'wablas' => [
            'enabled' => env('WHATSAPP_WABLAS_ENABLED', false),
            'token' => env('WHATSAPP_WABLAS_TOKEN', ''),
            'api_url' => env('WHATSAPP_WABLAS_API_URL', ''),
            'device_id' => env('WHATSAPP_WABLAS_DEVICE_ID', ''),
        ],
    ],

    // Tenant-aware settings
    'tenant' => [
        // Allow tenants to override provider settings
        'allow_override' => env('WHATSAPP_TENANT_OVERRIDE', false),
        
        // Table name for tenant-specific WhatsApp settings (if enabled)
        'settings_table' => 'tenant_whatsapp_settings',
    ],

    // Message templates configuration
    'templates' => [
        // Default footer for all messages
        'footer' => env('WHATSAPP_TEMPLATE_FOOTER', ''),
        
        // Default language for templates
        'language' => env('WHATSAPP_TEMPLATE_LANGUAGE', 'id'),
    ],

    // Rate limiting (messages per minute per provider)
    'rate_limit' => [
        'enabled' => env('WHATSAPP_RATE_LIMIT_ENABLED', true),
        'per_minute' => (int) env('WHATSAPP_RATE_LIMIT_PER_MINUTE', 30),
    ],
];
