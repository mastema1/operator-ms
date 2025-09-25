<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | operator management system. These settings help protect against
    | common security vulnerabilities and attacks.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */
    'session' => [
        'timeout' => env('SESSION_TIMEOUT', 480), // 8 hours in minutes
        'regenerate_on_login' => true,
        'secure_cookies' => env('SESSION_SECURE_COOKIE', false),
        'same_site_cookies' => 'strict',
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Security
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => false,
        'max_age_days' => 90,
        'history_count' => 5, // Remember last 5 passwords
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'login_attempts' => [
            'max_attempts' => 5,
            'lockout_minutes' => 15,
        ],
        'api_requests' => [
            'per_minute' => 60,
            'burst_limit' => 100,
        ],
        'search_requests' => [
            'per_minute' => 30,
            'burst_limit' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_size' => 5 * 1024 * 1024, // 5MB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'allowed_mimes' => [
            'image/jpeg', 'image/png', 'image/gif',
            'application/pdf',
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ],
        'scan_for_viruses' => env('UPLOAD_VIRUS_SCAN', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Security Policy
    |--------------------------------------------------------------------------
    */
    'csp' => [
        'default_src' => "'self'",
        'script_src' => "'self' 'unsafe-inline' 'unsafe-eval'", // Required for Livewire
        'style_src' => "'self' 'unsafe-inline' https://fonts.googleapis.com",
        'font_src' => "'self' https://fonts.gstatic.com",
        'img_src' => "'self' data: blob:",
        'connect_src' => "'self'",
        'frame_ancestors' => "'none'",
        'base_uri' => "'self'",
        'form_action' => "'self'",
    ],

    /*
    |--------------------------------------------------------------------------
    | IP Whitelist/Blacklist
    |--------------------------------------------------------------------------
    */
    'ip_filtering' => [
        'enabled' => env('IP_FILTERING_ENABLED', false),
        'whitelist' => env('IP_WHITELIST', ''),
        'blacklist' => env('IP_BLACKLIST', ''),
        'allow_private_ips' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled' => env('AUDIT_LOGGING_ENABLED', true),
        'log_all_requests' => env('AUDIT_LOG_ALL_REQUESTS', false),
        'sensitive_routes' => [
            'operators.store', 'operators.update', 'operators.destroy',
            'postes.store', 'postes.update', 'postes.destroy',
            'backup.assign', 'backup.remove',
            'profile.update', 'profile.destroy'
        ],
        'retention_days' => 365,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Security
    |--------------------------------------------------------------------------
    */
    'database' => [
        'encrypt_connection' => env('DB_ENCRYPT', false),
        'verify_ssl' => env('DB_SSL_VERIFY', true),
        'query_timeout' => 30, // seconds
        'max_connections' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    */
    '2fa' => [
        'enabled' => env('TWO_FACTOR_ENABLED', false),
        'required_for_admins' => true,
        'backup_codes_count' => 8,
        'window' => 1, // Allow 1 window of variance
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'hsts_max_age' => 31536000, // 1 year
        'hsts_include_subdomains' => true,
        'hsts_preload' => true,
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Isolation
    |--------------------------------------------------------------------------
    */
    'tenant_isolation' => [
        'strict_mode' => true,
        'validate_all_queries' => env('VALIDATE_TENANT_QUERIES', true),
        'log_violations' => true,
        'auto_logout_on_violation' => true,
    ],

];
