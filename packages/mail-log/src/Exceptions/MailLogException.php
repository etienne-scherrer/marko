<?php

declare(strict_types=1);

namespace Marko\Mail\Log\Exceptions;

use Marko\Core\Exceptions\MarkoException;

class MailLogException extends MarkoException
{
    public static function productionNotAllowed(
        string $environment,
    ): self {
        return new self(
            message: "The log mail driver (marko/mail-log) cannot run in the '$environment' environment.",
            context: 'The log driver sends no mail and writes messages to the application log, where '
                . 'password-reset links and verification tokens can be read by anyone with log access.',
            suggestion: 'Install a real mail driver for production (such as marko/mail-smtp) and remove marko/mail-log, '
                . "or set 'allow_production' => true in config/mail-log.php (MAIL_LOG_ALLOW_PRODUCTION=true) "
                . 'if production must deliberately log mail instead of sending it.',
        );
    }
}
