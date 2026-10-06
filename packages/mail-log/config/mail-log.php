<?php

declare(strict_types=1);

use Marko\Config\Env;

return [
    /*
    |--------------------------------------------------------------------------
    | Include Message Bodies
    |--------------------------------------------------------------------------
    |
    | Whether to write full text/HTML bodies and raw message content to the
    | log. Bodies routinely contain password-reset links and verification
    | tokens, so anyone who can read the log could take over accounts.
    |
    | null (the default) logs bodies only in development (development, dev,
    | local) and logs envelope metadata only everywhere else. Set true or
    | false to force a choice.
    |
    */
    'include_body' => null,

    /*
    |--------------------------------------------------------------------------
    | Allow Production
    |--------------------------------------------------------------------------
    |
    | The log driver delivers no mail, so booting it in production is almost
    | always a misconfiguration and the application refuses to boot. Set true
    | only if production must deliberately log mail instead of sending it.
    |
    */
    'allow_production' => Env::bool('MAIL_LOG_ALLOW_PRODUCTION', false),
];
