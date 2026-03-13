<?php
/*
 * Local configuration file for Scalingo deployment.
 * All sensitive values are read from environment variables.
 * Set these in the Scalingo dashboard under Settings > Environment.
 */
return [
    'debug' => filter_var(env('DEBUG', false), FILTER_VALIDATE_BOOLEAN),

    'Security' => [
        'salt' => env('SECURITY_SALT', '__SET_SECURITY_SALT_IN_ENV__'),
    ],

    /*
     * On Scalingo the app is served at the domain root, not a subdirectory.
     * Remove the /GENTA base path that was used on Cloudways.
     */
    'App' => [
        'base' => false,
        'fullBaseUrl' => env('FULL_BASE_URL', false),
        'callbackSecret' => env('CALLBACK_SECRET', '__SET_CALLBACK_SECRET_IN_ENV__'),
    ],

    /*
     * Ngrok Report API Configuration
     */
    'Ngrok' => [
        'baseUrl' => env('NGROK_BASE_URL', 'https://nonbasic-bob-inimical.ngrok-free.dev'),
        'apiKey' => env('NGROK_API_KEY', 'NkOiP2w43x6abJ9s0zQgyDnYKSdXGmAo'),
        'analysisEndpoint' => '/analysis_report',
        'tailoredEndpoint' => '/tailored_module',
        'timeout' => 30,
    ],

    /*
     * Database connection.
     * Scalingo provides a DATABASE_URL env var for MySQL add-ons.
     * Format: mysql://user:password@host:port/database
     */
    'Datasources' => [
        'default' => [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'username' => env('DB_USERNAME', ''),
            'password' => env('DB_PASSWORD', ''),
            'database' => env('DB_DATABASE', ''),
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
     * Email / SMTP configuration.
     */
    'EmailTransport' => [
        'default' => [
            'className' => 'Smtp',
            'host' => env('SMTP_HOST', 'smtp.gmail.com'),
            'port' => env('SMTP_PORT', 587),
            'username' => env('SMTP_USERNAME', ''),
            'password' => env('SMTP_PASSWORD', ''),
            'tls' => true,
            'client' => null,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
    ],

    'Email' => [
        'default' => [
            'transport' => 'default',
            'from' => [env('EMAIL_FROM_ADDRESS', 'noreply@deped.gov.ph') => 'GENTA System'],
            'charset' => 'utf-8',
            'headerCharset' => 'utf-8',
        ],
    ],
];
