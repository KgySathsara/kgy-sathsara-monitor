<?php

return [
    /*
    |--------------------------------------------------------------------------
    | KGY Sathsara Monitor Configuration
    |--------------------------------------------------------------------------
    |
    | This is KGY Sathsara's custom system monitoring package configuration.
    | Set your thresholds and notification preferences here.
    |
    */

    'author' => 'KGY Sathsara',
    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Alert Thresholds
    |--------------------------------------------------------------------------
    */
    'thresholds' => [
        'cpu' => env('KGY_CPU_THRESHOLD', 80),
        'memory' => env('KGY_MEMORY_THRESHOLD', 80),
        'disk' => env('KGY_DISK_THRESHOLD', 90),
        'load' => env('KGY_LOAD_THRESHOLD', 4.0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Disk Path to Monitor
    |--------------------------------------------------------------------------
    */
    'disk_path' => env('KGY_DISK_PATH', '/'),

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'email' => [
            'enabled' => env('KGY_EMAIL_ALERTS', false),
            'to' => env('KGY_ALERT_EMAIL', 'admin@example.com'),
            'from' => env('KGY_FROM_EMAIL', 'monitor@example.com'),
            'subject' => '⚠️ KGY Sathsara Monitor Alert',
        ],
        
        'slack' => [
            'enabled' => env('KGY_SLACK_ALERTS', false),
            'webhook' => env('KGY_SLACK_WEBHOOK'),
            'channel' => env('KGY_SLACK_CHANNEL', '#monitoring'),
        ],
        
        'telegram' => [
            'enabled' => env('KGY_TELEGRAM_ALERTS', false),
            'bot_token' => env('KGY_TELEGRAM_BOT_TOKEN'),
            'chat_id' => env('KGY_TELEGRAM_CHAT_ID'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Settings
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'enabled' => true,
        'route_prefix' => 'kgy-sathsara',
        'middleware' => ['web', 'kgy-sathsara-auth'],
        'secret_key' => env('KGY_DASHBOARD_SECRET'),
        'allowed_ips' => explode(',', env('KGY_ALLOWED_IPS', '127.0.0.1')),
        'refresh_interval' => env('KGY_DASHBOARD_REFRESH', 60), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Settings
    |--------------------------------------------------------------------------
    */
    'log' => [
        'enabled' => true,
        'retention_days' => env('KGY_LOG_RETENTION', 30),
        'table' => 'kgy_sathsara_logs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    */
    'commands' => [
        'monitor' => [
            'enabled' => true,
            'signature' => 'kgy-sathsara:monitor',
            'description' => 'KGY Sathsara System Monitor',
        ],
        'clean' => [
            'enabled' => true,
            'signature' => 'kgy-sathsara:clean',
            'description' => 'Clean old monitor logs',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Metrics
    |--------------------------------------------------------------------------
    */
    'custom_metrics' => [
        // Add custom metrics here
        // 'mysql_status' => 'SELECT 1',
        // 'redis_status' => 'PING',
    ],
];