<?php

declare(strict_types=1);

namespace Marko\Hashing\Exceptions;

class InvalidValueException extends HasherException
{
    public static function tooLong(
        string $algorithm,
        int $maxBytes,
        int $actualBytes,
    ): self {
        return new self(
            message: "Value is longer than $maxBytes bytes",
            context: "The $algorithm hasher only uses the first $maxBytes bytes of a value; got $actualBytes bytes. Hashing it would silently ignore the rest",
            suggestion: "Validate that passwords are at most $maxBytes bytes (strlen(), not mb_strlen()) before hashing, or set hashing.default to 'argon2id', which has no length limit",
        );
    }

    public static function containsNulByte(
        string $algorithm,
    ): self {
        return new self(
            message: 'Value contains a NUL byte',
            context: "The $algorithm hasher cannot hash a value containing a NUL (\\0) byte",
            suggestion: 'Reject input containing NUL bytes during validation, before it reaches the hasher',
        );
    }
}
