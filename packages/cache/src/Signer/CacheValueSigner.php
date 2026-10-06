<?php

declare(strict_types=1);

namespace Marko\Cache\Signer;

use Marko\Cache\Exceptions\TamperedCacheValueException;
use Marko\Encryption\Config\EncryptionConfig;

/**
 * Signs serialized cache payloads with HMAC-SHA256 so a driver never hands
 * unserialize() bytes it did not write itself.
 *
 * The envelope format is: {64-char-hex-hmac}.{serialized-payload}
 *
 * Every MAC goes through mac(), and the key comes from signingKey(), so the key
 * derivation and the MAC inputs can change in one place.
 */
readonly class CacheValueSigner
{
    private const int MAC_LENGTH = 64;

    private const string SEPARATOR = '.';

    public function __construct(
        private EncryptionConfig $encryptionConfig,
    ) {}

    /**
     * Wrap a serialized value in an HMAC-signed envelope.
     *
     * @throws TamperedCacheValueException when the signing key is empty
     */
    public function wrap(
        string $serialized,
    ): string {
        return $this->mac($serialized) . self::SEPARATOR . $serialized;
    }

    /**
     * Verify an HMAC-signed envelope and return the inner serialized bytes, or
     * null when the envelope is malformed, unsigned, or its HMAC does not match.
     *
     * A missing signing key is a configuration error rather than a bad entry,
     * so it still throws.
     *
     * @throws TamperedCacheValueException when the signing key is empty
     */
    public function unwrap(
        string $envelope,
    ): ?string {
        $hmac = substr($envelope, 0, self::MAC_LENGTH);
        $separator = substr($envelope, self::MAC_LENGTH, 1);
        $serialized = substr($envelope, self::MAC_LENGTH + 1);

        // The MAC is computed before the separator check so an empty key is
        // reported even when the envelope is malformed or unsigned.
        $expectedHmac = $this->mac($serialized);

        if ($separator !== self::SEPARATOR || !hash_equals($expectedHmac, $hmac)) {
            return null;
        }

        return $serialized;
    }

    /**
     * Verify an HMAC-signed envelope and return the inner serialized bytes.
     *
     * @throws TamperedCacheValueException when the envelope HMAC does not verify or the key is empty
     */
    public function verifyAndUnwrap(
        string $envelope,
    ): string {
        return $this->unwrap($envelope) ?? throw TamperedCacheValueException::signatureMismatch();
    }

    /**
     * @throws TamperedCacheValueException when the signing key is empty
     */
    private function mac(
        string $payload,
    ): string {
        return hash_hmac('sha256', $payload, $this->signingKey());
    }

    /**
     * @throws TamperedCacheValueException when the signing key is empty
     */
    private function signingKey(): string
    {
        $key = $this->encryptionConfig->key();

        if ($key === '') {
            throw TamperedCacheValueException::emptySigningKey();
        }

        return $key;
    }
}
