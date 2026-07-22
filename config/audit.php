<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Trail Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the audit trail system.
    | You can customize various aspects of logging behavior here.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Enable/Disable Audit Trail
    |--------------------------------------------------------------------------
    |
    | Set to false to completely disable audit trail logging.
    | Useful for testing or maintenance.
    |
    */
    'enabled' => env('AUDIT_TRAIL_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Number of days to keep audit trail data before cleanup.
    | Minimum is 30 days for compliance.
    |
    */
    'retention_days' => env('AUDIT_TRAIL_RETENTION_DAYS', 90),

    /*
    |--------------------------------------------------------------------------
    | Activity Logging
    |--------------------------------------------------------------------------
    |
    | Configure which activities should be logged.
    |
    */
    'activity_logging' => [
        'page_access' => env('AUDIT_LOG_PAGE_ACCESS', true),
        'data_changes' => env('AUDIT_LOG_DATA_CHANGES', true),
        'authentication' => env('AUDIT_LOG_AUTHENTICATION', true),
        'file_operations' => env('AUDIT_LOG_FILE_OPERATIONS', true),
        'report_generation' => env('AUDIT_LOG_REPORT_GENERATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Models
    |--------------------------------------------------------------------------
    |
    | Models that should not be logged for data changes.
    | Add model class names to this array.
    |
    */
    'excluded_models' => [
        'App\Models\ActivityLog',
        'App\Models\AuthLog',
        'App\Models\DataChangeLog',
        'App\Models\StockMovementLog',
        // Add more models as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded Routes
    |--------------------------------------------------------------------------
    |
    | Routes that should not be logged for page access.
    | Supports wildcards using * character.
    |
    */
    'excluded_routes' => [
        'livewire.*',
        'filament.asset',
        'filament.app.css',
        'filament.app.js',
        '_debugbar.*',
        'telescope.*',
        // Add more routes as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Excluded User Agents
    |--------------------------------------------------------------------------
    |
    | User agents that should not be logged.
    | Useful for excluding bots and crawlers.
    |
    */
    'excluded_user_agents' => [
        'bot',
        'crawler',
        'spider',
        'scanner',
        // Add more user agents as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Sensitive Fields
    |--------------------------------------------------------------------------
    |
    | Fields that should be excluded from data change logging
    | for security and privacy reasons.
    |
    */
    'sensitive_fields' => [
        'password',
        'password_confirmation',
        'remember_token',
        'api_token',
        'email_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        // Add more sensitive fields as needed
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security-related features.
    |
    */
    'security' => [
        'failed_login_threshold' => env('AUDIT_FAILED_LOGIN_THRESHOLD', 5),
        'failed_login_window_minutes' => env('AUDIT_FAILED_LOGIN_WINDOW', 15),
        'suspicious_activity_notification' => env('AUDIT_SUSPICIOUS_NOTIFICATION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    |
    | Configure performance-related settings.
    |
    */
    'performance' => [
        'batch_size' => env('AUDIT_BATCH_SIZE', 1000),
        'queue_logging' => env('AUDIT_QUEUE_LOGGING', false),
        'queue_connection' => env('AUDIT_QUEUE_CONNECTION', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Address Settings
    |--------------------------------------------------------------------------
    |
    | Configure how IP addresses are handled.
    |
    */
    'ip_address' => [
        'anonymize' => env('AUDIT_ANONYMIZE_IP', false),
        'trusted_proxies' => env('AUDIT_TRUSTED_PROXIES', ''),
    ],

];
