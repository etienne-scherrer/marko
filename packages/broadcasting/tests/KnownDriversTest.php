<?php

declare(strict_types=1);

use Marko\Broadcasting\Exceptions\BroadcastException;
use Marko\Broadcasting\Exceptions\NoDriverException;

it('lists the mercure and pusher drivers in known-drivers.php', function (): void {
    $drivers = require __DIR__ . '/../known-drivers.php';

    expect($drivers)->toHaveKey('marko/broadcasting-mercure')
        ->and($drivers)->toHaveKey('marko/broadcasting-pusher');
});

it('suggests composer require commands for every known driver in NoDriverException', function (): void {
    $exception = NoDriverException::noDriverInstalled();

    expect($exception)->toBeInstanceOf(BroadcastException::class)
        ->and($exception->getMessage())->toBe('No broadcasting driver installed.')
        ->and($exception->getSuggestion())->toContain('composer require marko/broadcasting-mercure')
        ->toContain('composer require marko/broadcasting-pusher')
        ->toContain('https://marko.build/docs/packages/broadcasting-mercure/');
});
