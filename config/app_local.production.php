<?php
/*
 * Production configuration file for Cloudways deployment.
 * This file should be created on the server with actual credentials.
 * DO NOT commit this file to version control.
 */
return [
    /*
     * Debug Level:
     * MUST be false in production for security
     */
    'debug' => filter_var(env('DEBUG', false), FILTER_VALIDATE_BOOLEAN),

    /*
     * Security and encryption configuration
     * Generate a new random salt for production using:
     * bin/cake security generate_salt
     */
    'Security' => [
        'salt' => env('SECURITY_SALT', '__GENERATE_NEW_SALT_FOR_PRODUCTION__'),
    ],

    /*
     * Application base path configuration
     * On Cloudways, your app will typically be at the root, so set this to false
     * or leave it empty unless you're deploying to a subdirectory
     */
    'App' => [
        'base' => env('APP_BASE', false),
        'fullBaseUrl' => env('FULL_BASE_URL', false),
        'callbackSecret' => env('CALLBACK_SECRET', '__GENERATE_RANDOM_SECRET__'),
    ],

    /*
     * Database Connection
     * Use environment variables for security
     * Cloudways provides database credentials in their dashboard
     */
    'Datasources' => [
        'default' => [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'username' => env('DB_USERNAME', 'username'),
            'password' => env('DB_PASSWORD', 'password'),
            'database' => env('DB_DATABASE', 'database_name'),
            
            /*
             * You can also use a DATABASE_URL for easier configuration:
             * Format: mysql://username:password@localhost:3306/database_name
             */
            'url' => env('DATABASE_URL', null),
            
            'encoding' => 'utf8mb4',
            'timezone' => 'UTC',
            'flags' => [],
            'cacheMetadata' => true,
            'log' => false,
            'quoteIdentifiers' => false,
        ],
    ],

    /*
     * Email configuration
     * Configure SMTP settings if needed
     */
    'EmailTransport' => [
        'default' => [
            'className' => 'Smtp',
            'host' => env('EMAIL_HOST', 'localhost'),
            'port' => env('EMAIL_PORT', 587),
            'timeout' => 30,
            'username' => env('EMAIL_USERNAME', null),
            'password' => env('EMAIL_PASSWORD', null),
            'client' => null,
            'tls' => env('EMAIL_TLS', true),
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
    ],

    /*
     * Logging configuration
     * Errors should be logged, not displayed in production
     */
    'Log' => [
        'debug' => [
            'className' => 'Cake\Log\Engine\FileLog',
            'path' => LOGS,
            'file' => 'debug',
            'levels' => ['notice', 'info', 'debug'],
            'url' => env('LOG_DEBUG_URL', null),
        ],
        'error' => [
            'className' => 'Cake\Log\Engine\FileLog',
            'path' => LOGS,
            'file' => 'error',
            'levels' => ['warning', 'error', 'critical', 'alert', 'emergency'],
            'url' => env('LOG_ERROR_URL', null),
        ],
    ],
];
