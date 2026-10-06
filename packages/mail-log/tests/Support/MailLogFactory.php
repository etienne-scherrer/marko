<?php

declare(strict_types=1);

namespace Marko\Mail\Log\Tests\Support;

use Marko\Core\Environment\AppEnvironment;
use Marko\Log\Contracts\LoggerInterface;
use Marko\Mail\Log\Config\MailLogConfig;
use Marko\Mail\Log\LogMailer;
use Marko\Mail\Log\ProductionGuard;
use Marko\Testing\Fake\FakeConfigRepository;

class MailLogFactory
{
    public static function config(
        ?bool $includeBody = null,
        bool $allowProduction = false,
        string $environment = 'development',
    ): MailLogConfig {
        return new MailLogConfig(
            new FakeConfigRepository([
                'mail-log.include_body' => $includeBody,
                'mail-log.allow_production' => $allowProduction,
            ]),
            new AppEnvironment(['MARKO_ENV' => $environment]),
        );
    }

    /**
     * A LogMailer that logs bodies unless told otherwise.
     */
    public static function mailer(
        LoggerInterface $logger,
        ?bool $includeBody = true,
        string $environment = 'development',
    ): LogMailer {
        return new LogMailer($logger, self::config($includeBody, environment: $environment));
    }

    public static function guard(
        string $environment,
        bool $allowProduction = false,
    ): ProductionGuard {
        return new ProductionGuard(
            new AppEnvironment(['MARKO_ENV' => $environment]),
            self::config(allowProduction: $allowProduction, environment: $environment),
        );
    }
}
