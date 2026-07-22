<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Audit Trail Testing Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file is used for testing audit trail functionality.
    | It ensures that audit trail is enabled during tests.
    |
    */

    'enabled' => true,

    'log_authentication' => true,

    'log_data_changes' => true,

    'log_activities' => true,

    'retention_days' => 365,

    'excluded_models' => [
        'App\Models\ActivityLog',
        'App\Models\AuthLog',
        'App\Models\DataChangeLog',
    ],
];
