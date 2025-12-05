<?php
/*
 * Local configuration file to provide any overrides to your app.php configuration.
 * Copy and save this file as app_local.php and make changes as required.
 * Note: It is not recommended to commit files with credentials such as app_local.php
 * into source code version control.
 */
return [
    /*
     * Debug Level:
     *
     * Production Mode:
     * false: No error messages, errors, or warnings shown.
     *
     * Development Mode:
     * true: Errors and warnings shown.
     */
    'debug' => false,

    /*
     * Security and encryption configuration
     *
     * - salt - A random string used in security hashing methods.
     *   The salt value is also used as the encryption key.
     *   You should treat it as extremely sensitive data.
     */
    'Security' => [
        'salt' => env('SECURITY_SALT', 'ae3bb4d3a8afb15f01e584a449d8de1c77b5d3af3492425310f050c6fe160522'),
    ],

    /*
     * Application configuration for Cloudways production
     * Set base to false since app is at root, not in a subdirectory
     */
    'App' => [
        'base' => false,  // NO /GENTA prefix on Cloudways
        'fullBaseUrl' => env('FULL_BASE_URL', false),
        'callbackSecret' => env('CALLBACK_SECRET', 'production-callback-secret-change-this')
    ],

    /*
     * Connection information used by the ORM to connect
     * to your application's datastores.
     *
     * See app.php for more configuration options.
     */
    'Datasources' => [
        'default' => [
            'host' => 'localhost',
            /*
             * CakePHP will use the default DB port based on the driver selected
             * MySQL on MAMP uses port 8889, MAMP users will want to uncomment
             * the following line and set the port accordingly
             */
            //'port' => 'non_standard_port_number',

            'username' => 'zfepxctexd',
            'password' => 'VVyb8UNZ93',

            'database' => 'zfepxctexd',
            /*
             * If not using the default 'public' schema with the PostgreSQL driver
             * set it here.
             */
            //'schema' => 'myapp',

            /*
             * You can use a DSN string to set the entire configuration
             */
            'url' => env('DATABASE_URL', null),
        ],

        /*
         * The test connection is used during the test suite.
         */
        'test' => [
            'host' => 'localhost',
            //'port' => 'non_standard_port_number',
            'username' => 'my_app',
            'password' => 'secret',
            'database' => 'test_myapp',
            //'schema' => 'myapp',
            'url' => env('DATABASE_TEST_URL', 'sqlite://127.0.0.1/tmp/tests.sqlite'),
        ],
    ],

    /*
     * Email configuration.
     *
     * Host and credential configuration in case you are using SmtpTransport
     *
     * See app.php for more configuration options.
     */
    'EmailTransport' => [
        'default' => [
            'className' => 'Smtp',
            // For Gmail: smtp.gmail.com
            // For Outlook/Office365: smtp.office365.com
            // For SendGrid: smtp.sendgrid.net
            // For Mailgun: smtp.mailgun.org
            'host' => env('SMTP_HOST', 'smtp.gmail.com'),
            'port' => env('SMTP_PORT', 587),
            'username' => env('SMTP_USERNAME', 'jonathan.tiglao2002@gmail.com'),
            'password' => env('SMTP_PASSWORD', 'fedoukpiaahhlqwo'),
            'tls' => true,
            'client' => null,
            'url' => env('EMAIL_TRANSPORT_DEFAULT_URL', null),
        ],
    ],

    /*
     * Email delivery profiles
     */
    'Email' => [
        'default' => [
            'transport' => 'default',
            'from' => [env('EMAIL_FROM_ADDRESS', 'noreply@gmail.com') => 'GENTA System'],
            'charset' => 'utf-8',
            'headerCharset' => 'utf-8',
        ],
    ],
];
