<?php

declare(strict_types=1);

use Marko\Broadcasting\Exceptions\BroadcastException;
use Marko\Broadcasting\Exceptions\NoDriverException;

it('lists the amphp, mercure and pusher drivers in known-drivers.php', function (): void {
    $drivers = require __DIR__ . '/../known-drivers.php';

    expect($drivers)->toHaveKey('marko/broadcasting-amphp')
        ->toHaveKey('marko/broadcasting-mercure')
        ->toHaveKey('marko/broadcasting-pusher');
});

it('suggests composer require commands for every known driver in NoDriverException', function (): void {
    $exception = NoDriverException::noDriverInstalled();

    expect($exception)->toBeInstanceOf(BroadcastException::class)
        ->and($exception->getMessage())->toBe('No broadcasting driver installed.')
        ->and($exception->getSuggestion())->toContain('composer require marko/broadcasting-mercure')
        ->toContain('composer require marko/broadcasting-pusher')
        ->toContain('composer require marko/broadcasting-amphp')
        ->toContain('https://marko.build/docs/packages/broadcasting-mercure/');
});
