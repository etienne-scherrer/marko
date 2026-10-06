<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Mail Driver
    |--------------------------------------------------------------------------
    |
    | The default mail driver used for sending emails. Drivers ship as
    | separate packages: "smtp" (marko/mail-smtp), "log" (marko/mail-log).
    |
    */
    'driver' => 'smtp',

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | The default "from" address and name used for all outgoing emails.
    |
    */
    'from' => [
        'address' => 'hello@example.com',
        'name' => 'Marko Application',
    ],

    /*
    |--------------------------------------------------------------------------
    | SMTP Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the SMTP mail driver.
    |
    */
    'smtp' => [
        'host' => 'localhost',
        'port' => 587,
        'encryption' => 'tls',
        'username' => null,
        'password' => null,
        'timeout' => 30,
        'auth_mode' => 'login',
    ],
];
