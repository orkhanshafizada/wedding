<?php

return [
    'audit_all_models' => false,

    'heartbeat_throttle_seconds' => 60,

    'include_namespaces' => [
        'App\\Models\\',
        'Modules\\',
    ],

    'exclude_models' => [
        Modules\Log\Models\AdminSession::class,
        Modules\Log\Models\ActivityLog::class,
        Modules\Log\Models\ActivityLogChange::class,
        Spatie\Permission\Models\Role::class,
        Spatie\Permission\Models\Permission::class,
    ],

    'exclude_tables' => [
        'cache',
        'jobs',
        'job_batches',
        'sessions',
        'password_reset_tokens',
    ],

    'mask_fields' => [
        'password',
        'remember_token',
        'api_token',
        'token',
        'secret',
    ],

    'max_value_length' => 2000,

    'admin_guard' => 'web',
];
