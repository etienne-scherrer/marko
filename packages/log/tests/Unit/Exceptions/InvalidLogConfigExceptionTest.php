<?php

declare(strict_types=1);

use Marko\Log\Exceptions\InvalidLogConfigException;
use Marko\Log\Exceptions\LogException;

it('extends LogException', function () {
    expect(InvalidLogConfigException::invalidMode('log.file_mode', 0o1000))->toBeInstanceOf(LogException::class);
});

it('reports an invalid permission mode in octal with the config key', function () {
    $exception = InvalidLogConfigException::invalidMode('log.file_mode', 0o1000);

    expect($exception->getMessage())->toBe("Invalid permission mode 01000 for 'log.file_mode'")
        ->and($exception->getSuggestion())->toContain('0600');
});

it('reports the type of an invalid redact key', function () {
    $exception = InvalidLogConfigException::invalidRedactKey(['nested']);

    expect($exception->getMessage())->toContain('got array')
        ->and($exception->getSuggestion())->toContain("['password', 'token']");
});
