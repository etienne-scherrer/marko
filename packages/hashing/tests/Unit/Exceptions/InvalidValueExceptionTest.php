<?php

declare(strict_types=1);

use Marko\Hashing\Exceptions\HasherException;
use Marko\Hashing\Exceptions\InvalidValueException;

it('extends HasherException', function () {
    expect(InvalidValueException::tooLong('bcrypt', 72, 80))->toBeInstanceOf(HasherException::class);
});

it('reports the byte length and limit for a value that is too long', function () {
    $exception = InvalidValueException::tooLong('bcrypt', 72, 80);

    expect($exception->getMessage())->toBe('Value is longer than 72 bytes')
        ->and($exception->getContext())->toContain('80 bytes')
        ->and($exception->getContext())->toContain('bcrypt')
        ->and($exception->getSuggestion())->not->toBeEmpty();
});

it('explains a NUL byte rejection', function () {
    $exception = InvalidValueException::containsNulByte('bcrypt');

    expect($exception->getMessage())->toBe('Value contains a NUL byte')
        ->and($exception->getContext())->toContain('bcrypt')
        ->and($exception->getSuggestion())->not->toBeEmpty();
});
