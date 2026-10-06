<?php

declare(strict_types=1);

use Marko\Encryption\Exceptions\DecryptionException;
use Marko\Encryption\Exceptions\EncryptionException;

describe('DecryptionException', function (): void {
    it('extends EncryptionException', function (): void {
        $exception = DecryptionException::invalidPayload();

        expect($exception)->toBeInstanceOf(EncryptionException::class);
    });

    it('creates invalidPayload exception with correct message', function (): void {
        $exception = DecryptionException::invalidPayload();

        expect($exception->getMessage())->toBe('The encrypted payload is invalid')
            ->and($exception->getContext())->toBe('Decrypting data that may be corrupted or tampered with')
            ->and($exception->getSuggestion())->toBe('Verify the data has not been modified after encryption');
    });

    it('creates invalidKey exception with correct message', function (): void {
        $exception = DecryptionException::invalidKey();

        expect($exception->getMessage())->toBe('The encryption key is invalid or does not match')
            ->and($exception->getContext())->toBe(
                'Decrypting data with a different key, or different associated data, than was used for encryption',
            )
            ->and($exception->getSuggestion())->toBe(
                'Ensure the same ENCRYPTION_KEY (or a key listed in encryption.previous_keys) and the same associated data are used for both encryption and decryption',
            );
    });

    it('creates invalidTagLength exception naming the expected and actual lengths', function (): void {
        $exception = DecryptionException::invalidTagLength(1, 16);

        expect($exception->getMessage())->toBe('Authentication tag must be 16 bytes, got 1')
            ->and($exception->getContext())->toBe('Decrypting data that may be corrupted or tampered with')
            ->and($exception->getSuggestion())->toBe(
                'Verify the data has not been modified after encryption; truncated tags are rejected to prevent forgery',
            );
    });

    it('creates invalidIvLength exception naming the expected and actual lengths', function (): void {
        $exception = DecryptionException::invalidIvLength(8, 12);

        expect($exception->getMessage())->toBe('Initialization vector must be 12 bytes, got 8')
            ->and($exception->getContext())->toBe('Decrypting data that may be corrupted or tampered with')
            ->and($exception->getSuggestion())->toBe(
                'Verify the data has not been modified after encryption and was encrypted with the same cipher',
            );
    });
});
