<?php

declare(strict_types=1);

namespace Marko\Encryption\Config;

use Marko\Config\ConfigRepositoryInterface;
use Marko\Encryption\Exceptions\EncryptionException;

readonly class EncryptionConfig
{
    public function __construct(
        private ConfigRepositoryInterface $config,
    ) {}

    public function key(): string
    {
        return $this->config->getString('encryption.key');
    }

    public function cipher(): string
    {
        return $this->config->getString('encryption.cipher');
    }

    /**
     * Retired base64-encoded keys, tried for decryption only (newest first).
     *
     * @return list<string>
     *
     * @throws EncryptionException When an entry is not a string
     */
    public function previousKeys(): array
    {
        $keys = [];

        foreach ($this->config->getArray('encryption.previous_keys') as $key) {
            if (!is_string($key)) {
                throw EncryptionException::invalidPreviousKeys();
            }

            $keys[] = $key;
        }

        return $keys;
    }

    /**
     * Whether decryption with associated data falls back to empty associated data.
     */
    public function aadFallback(): bool
    {
        return $this->config->getBool('encryption.aad_fallback');
    }
}
