<?php

declare(strict_types=1);

namespace Marko\Encryption\Exceptions;

class DecryptionException extends EncryptionException
{
    public static function invalidPayload(): self
    {
        return new self(
            message: 'The encrypted payload is invalid',
            context: 'Decrypting data that may be corrupted or tampered with',
            suggestion: 'Verify the data has not been modified after encryption',
        );
    }

    public static function invalidKey(): self
    {
        return new self(
            message: 'The encryption key is invalid or does not match',
            context: 'Decrypting data with a different key than was used for encryption',
            suggestion: 'Ensure the same ENCRYPTION_KEY is used for both encryption and decryption',
        );
    }

    public static function invalidTagLength(
        int $actual,
        int $expected,
    ): self {
        return new self(
            message: "Authentication tag must be $expected bytes, got $actual",
            context: 'Decrypting data that may be corrupted or tampered with',
            suggestion: 'Verify the data has not been modified after encryption; truncated tags are rejected to prevent forgery',
        );
    }

    public static function invalidIvLength(
        int $actual,
        int $expected,
    ): self {
        return new self(
            message: "Initialization vector must be $expected bytes, got $actual",
            context: 'Decrypting data that may be corrupted or tampered with',
            suggestion: 'Verify the data has not been modified after encryption and was encrypted with the same cipher',
        );
    }
}
