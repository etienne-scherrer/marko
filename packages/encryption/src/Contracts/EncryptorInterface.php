<?php

declare(strict_types=1);

namespace Marko\Encryption\Contracts;

use Marko\Encryption\Exceptions\DecryptionException;
use Marko\Encryption\Exceptions\EncryptionException;

interface EncryptorInterface
{
    /**
     * Encrypt a value, binding it to optional associated data.
     *
     * The associated data (AAD) is authenticated but neither encrypted nor stored:
     * the same AAD must be passed to decrypt(). Use it to bind ciphertext to where
     * it lives (for example "users.ssn"), so it cannot be moved to another field.
     *
     * @throws EncryptionException
     */
    public function encrypt(
        string $value,
        string $aad = '',
    ): string;

    /**
     * Decrypt a value that was encrypted with the same associated data.
     *
     * @throws DecryptionException
     */
    public function decrypt(
        string $encrypted,
        string $aad = '',
    ): string;
}
