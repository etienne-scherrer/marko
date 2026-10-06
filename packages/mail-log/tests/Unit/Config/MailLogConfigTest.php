<?php

declare(strict_types=1);

use Marko\Config\Exceptions\ConfigException;
use Marko\Core\Environment\AppEnvironment;
use Marko\Mail\Log\Config\MailLogConfig;
use Marko\Mail\Log\Tests\Support\MailLogFactory;
use Marko\Testing\Fake\FakeConfigRepository;

describe('MailLogConfig', function (): void {
    it('includes bodies by default only in development', function (string $environment, bool $expected): void {
        expect(MailLogFactory::config(environment: $environment)->includeBody())->toBe($expected);
    })->with([
        ['development', true],
        ['dev', true],
        ['local', true],
        ['testing', false],
        ['staging', false],
        ['production', false],
    ]);

    it('lets an explicit include_body override the environment default', function (): void {
        expect(MailLogFactory::config(includeBody: true, environment: 'production')->includeBody())->toBeTrue()
            ->and(MailLogFactory::config(includeBody: false, environment: 'development')->includeBody())->toBeFalse();
    });

    it('rejects a non-boolean include_body', function (): void {
        $config = new MailLogConfig(
            new FakeConfigRepository(['mail-log.include_body' => 'sometimes']),
            new AppEnvironment(['MARKO_ENV' => 'development']),
        );

        expect(fn () => $config->includeBody())->toThrow(ConfigException::class);
    });

    it('reads allow_production', function (): void {
        expect(MailLogFactory::config(allowProduction: true)->allowProduction())->toBeTrue()
            ->and(MailLogFactory::config()->allowProduction())->toBeFalse();
    });

    it('ships defaults that resolve include_body from the environment and refuse production', function (): void {
        $defaults = require dirname(__DIR__, 3) . '/config/mail-log.php';

        expect($defaults)->toHaveKey('include_body')
            ->and($defaults['include_body'])->toBeNull()
            ->and($defaults)->toHaveKey('allow_production')
            ->and($defaults['allow_production'])->toBeFalse();
    });
});
