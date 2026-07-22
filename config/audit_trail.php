<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Audit Trail Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for audit trail functionality in the application.
    |
    */

    // Enabled/Disabled status
    'enabled' => env('AUDIT_TRAIL_ENABLED', true),

    // Log authentication events
    'log_auth' => env('AUDIT_TRAIL_LOG_AUTH', true),

    // Log user activities
    'log_activities' => env('AUDIT_TRAIL_LOG_ACTIVITIES', true),

    // Log data changes
    'log_data_changes' => env('AUDIT_TRAIL_LOG_DATA_CHANGES', true),

    // Routes to exclude from activity logging
    'excluded_routes' => [
        'livewire/*',
        'api/*',
        '_debugbar/*',
        'telescope/*',
        'horizon/*',
        'logs',
        'filament/assets/*',
        'css/*',
        'js/*',
        'images/*',
        'favicon.ico',
    ],

    // HTTP methods to log
    'log_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],

    // Models to exclude from data change logging
    'excluded_models' => [
        \App\Models\ActivityLog::class,
        \App\Models\AuthLog::class,
        \App\Models\DataChangeLog::class,
    ],

    // Fields to exclude from data change logging (sensitive data)
    'excluded_fields' => [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
    ],

    // Maximum age for log retention (in days)
    'retention_days' => env('AUDIT_TRAIL_RETENTION_DAYS', 365),

    // Pagination settings
    'pagination' => [
        'per_page' => 50,
        'max_per_page' => 200,
    ],

    // Date format for display
    'date_format' => 'Y-m-d H:i:s',

    // Enable/disable IP logging
    'log_ip_address' => true,

    // Enable/disable user agent logging
    'log_user_agent' => true,

    // Queue settings for async logging
    'queue' => [
        'enabled' => env('AUDIT_TRAIL_QUEUE_ENABLED', false),
        'connection' => env('AUDIT_TRAIL_QUEUE_CONNECTION', 'default'),
        'queue' => env('AUDIT_TRAIL_QUEUE_NAME', 'audit_trail'),
    ],

    // Cleanup settings
    'cleanup' => [
        'enabled' => env('AUDIT_TRAIL_CLEANUP_ENABLED', true),
        'schedule' => 'daily', // daily, weekly, monthly
        'batch_size' => 1000,
    ],

    // Export settings
    'export' => [
        'max_records' => 10000,
        'formats' => ['csv', 'excel', 'pdf'],
        'filename_format' => 'audit_trail_{type}_{date}',
    ],

    // Security settings
    'security' => [
        'encrypt_sensitive_data' => false,
        'hash_ip_addresses' => false,
        'anonymize_after_days' => null,
    ],

    // Dashboard settings
    'dashboard' => [
        'show_stats' => true,
        'show_recent_activities' => true,
        'recent_limit' => 10,
        'refresh_interval' => 30, // seconds
    ],
];
