<?php

declare(strict_types=1);

use Marko\Log\Contracts\LoggerInterface;
use Marko\Mail\Contracts\MailerInterface;
use Marko\Mail\Log\Config\MailLogConfig;
use Marko\Mail\Log\Exceptions\MailLogException;
use Marko\Mail\Log\LogMailer;
use Marko\Mail\Log\Tests\Support\MailLogFactory;

describe('module.php', function (): void {
    it('binds MailerInterface to LogMailer in module.php', function (): void {
        $modulePath = dirname(__DIR__, 2) . '/module.php';

        expect(file_exists($modulePath))->toBeTrue();

        $module = require $modulePath;

        expect($module)->toBeArray()
            ->and($module)->toHaveKey('bindings')
            ->and($module['bindings'])->toBeArray()
            ->and($module['bindings'])->toHaveKey(MailerInterface::class)
            ->and($module['bindings'][MailerInterface::class])->toBe(LogMailer::class);
    });

    it('resolves LogMailer with LoggerInterface and MailLogConfig injected', function (): void {
        $reflection = new ReflectionClass(LogMailer::class);
        $constructor = $reflection->getConstructor();

        expect($constructor)->not->toBeNull();

        $parameters = $constructor->getParameters();

        expect($parameters)->toHaveCount(2)
            ->and($parameters[0]->getType()->getName())->toBe(LoggerInterface::class)
            ->and($parameters[1]->getType()->getName())->toBe(MailLogConfig::class);
    });

    it('runs the production guard at boot', function (): void {
        $module = require dirname(__DIR__, 2) . '/module.php';

        expect($module)->toHaveKey('boot')
            ->and($module['boot'])->toBeInstanceOf(Closure::class);

        expect(fn () => $module['boot'](MailLogFactory::guard('development')))->not->toThrow(MailLogException::class)
            ->and(fn () => $module['boot'](MailLogFactory::guard('production')))->toThrow(MailLogException::class);
    });
});
