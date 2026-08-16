<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Usage Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the usage monitoring and limit enforcement system.
    |
    */

    // Default threshold percentage for "approaching limits" alerts
    'approaching_threshold' => env('USAGE_APPROACHING_THRESHOLD', 80.0),

    // Days to look back for recent usage logs
    'recent_days' => env('USAGE_RECENT_DAYS', 30),

    // Pagination per page for usage monitoring
    'per_page' => env('USAGE_PER_PAGE', 20),

    // Supported metrics and their plan column mapping
    'metrics' => [
        'user_count' => [
            'plan_column' => 'user_limit',
            'label' => 'User',
        ],
        'santri_count' => [
            'plan_column' => 'santri_limit',
            'label' => 'Santri',
        ],
        'branch_count' => [
            'plan_column' => 'branch_limit',
            'label' => 'Branch',
        ],
        'storage_usage_mb' => [
            'plan_column' => 'storage_limit_mb',
            'label' => 'Storage',
        ],
    ],

    // Status constants for santri (should match database values)
    'santri_status' => [
        'active' => 'aktif',
        'inactive' => 'nonaktif',
    ],

    // Labels for UI
    'labels' => [
        'no_plan' => 'No Plan',
        'unlimited' => 'Unlimited',
        'limited' => 'Limited',
    ],
];
