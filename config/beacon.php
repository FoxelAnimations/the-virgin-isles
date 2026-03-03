<?php

return [
    /*
    |--------------------------------------------------------------------------
    | IP Hashing Salt
    |--------------------------------------------------------------------------
    | Secret salt used for SHA-256 hashing of IP addresses in scan logs.
    | MUST be set in production. Never commit the real value.
    */
    'ip_salt' => env('BEACON_IP_SALT', 'change-me-in-production'),

    /*
    |--------------------------------------------------------------------------
    | Log Retention (days)
    |--------------------------------------------------------------------------
    | Scan logs older than this many days can be cleaned up.
    | Set to 0 to disable automatic retention cleanup.
    */
    'log_retention_days' => (int) env('BEACON_LOG_RETENTION_DAYS', 365),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    | Maximum requests per minute per IP on the /beacon/{guid} endpoint.
    */
    'rate_limit_per_minute' => (int) env('BEACON_RATE_LIMIT', 60),

    /*
    |--------------------------------------------------------------------------
    | Default Redirect URL
    |--------------------------------------------------------------------------
    | Fallback URL when a beacon has no redirect_url configured.
    */
    'default_redirect' => env('BEACON_DEFAULT_REDIRECT', '/'),
];
