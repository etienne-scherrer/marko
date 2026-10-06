<?php

declare(strict_types=1);

use Marko\Core\Environment\AppEnvironment;
use Marko\Mail\Log\Exceptions\MailLogException;
use Marko\Mail\Log\ProductionGuard;
use Marko\Mail\Log\Tests\Support\MailLogFactory;

describe('ProductionGuard', function (): void {
    it('refuses to boot in production by default', function (string $environment): void {
        $guard = MailLogFactory::guard($environment);

        expect(fn () => $guard->check())->toThrow(
            MailLogException::class,
            "The log mail driver (marko/mail-log) cannot run in the '$environment' environment.",
        );
    })->with(['production', 'prod']);

    it('refuses to boot when no environment is set', function (): void {
        $guard = new ProductionGuard(new AppEnvironment([]), MailLogFactory::config(environment: 'production'));

        expect(fn () => $guard->check())->toThrow(MailLogException::class);
    });

    it('explains why and how to opt in', function (): void {
        $guard = MailLogFactory::guard('production');

        try {
            $guard->check();
            $this->fail('Expected MailLogException');
        } catch (MailLogException $exception) {
            expect($exception->getContext())->toContain('password-reset')
                ->and($exception->getSuggestion())->toContain('allow_production');
        }
    });

    it('boots in production when allow_production is true', function (): void {
        $guard = MailLogFactory::guard('production', allowProduction: true);

        expect(fn () => $guard->check())->not->toThrow(MailLogException::class);
    });

    it('boots outside production', function (string $environment): void {
        $guard = MailLogFactory::guard($environment);

        expect(fn () => $guard->check())->not->toThrow(MailLogException::class);
    })->with(['development', 'local', 'testing', 'staging']);
});
