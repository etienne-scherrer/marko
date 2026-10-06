<?php

declare(strict_types=1);

use Marko\Cache\Exceptions\CacheException;
use Marko\Cache\Exceptions\TamperedCacheValueException;
use Marko\Cache\Signer\CacheValueSigner;
use Marko\Encryption\Config\EncryptionConfig;
use Marko\Testing\Fake\FakeConfigRepository;

function createCacheValueSigner(
    string $key = 'cache-signer-test-key',
): CacheValueSigner {
    return new CacheValueSigner(new EncryptionConfig(new FakeConfigRepository([
        'encryption.key' => $key,
    ])));
}

function cacheSignerSubkey(
    string $key = 'cache-signer-test-key',
): string {
    return hash_hkdf('sha256', $key, 32, 'marko-cache-signer');
}

it('wraps a payload in a hex HMAC-SHA256 envelope over the context and payload', function (): void {
    $envelope = createCacheValueSigner()->wrap('s:5:"hello";', 'app:greeting');

    expect($envelope)->toBe(
        hash_hmac('sha256', '12:app:greetings:5:"hello";', cacheSignerSubkey()) . '.s:5:"hello";',
    );
});

it('signs with an HKDF subkey rather than the raw app encryption key', function (): void {
    $signedWithRawKey = hash_hmac('sha256', '3:keypayload', 'cache-signer-test-key') . '.payload';

    expect(cacheSignerSubkey())->not->toBe('cache-signer-test-key')
        ->and(createCacheValueSigner()->unwrap($signedWithRawKey, 'key'))->toBeNull();
});

it('derives a subkey that differs from the queue envelope subkey for the same app key', function (): void {
    expect(cacheSignerSubkey())
        ->not->toBe(hash_hkdf('sha256', 'cache-signer-test-key', 32, 'marko-queue-envelope'))
        ->and(CacheValueSigner::KEY_PURPOSE)->toBe('marko-cache-signer');
});

it('unwraps an envelope it signed', function (): void {
    $signer = createCacheValueSigner();

    expect($signer->unwrap($signer->wrap('payload', 'key'), 'key'))->toBe('payload')
        ->and($signer->verifyAndUnwrap($signer->wrap('payload', 'key'), 'key'))->toBe('payload');
});

it('rejects an envelope moved from one cache key to another', function (): void {
    $signer = createCacheValueSigner();
    $envelope = $signer->wrap('s:5:"admin";', 'marko:cache:role:alice');

    expect($signer->unwrap($envelope, 'marko:cache:role:bob'))->toBeNull()
        ->and(fn () => $signer->verifyAndUnwrap($envelope, 'marko:cache:role:bob'))
        ->toThrow(TamperedCacheValueException::class, 'HMAC signature does not match');
});

it('keeps the boundary between context and payload unambiguous', function (): void {
    $signer = createCacheValueSigner();
    $envelope = $signer->wrap('bpayload', 'a');

    // Context "a" + payload "bpayload" and context "ab" + payload "payload" share the bytes "abpayload".
    expect($signer->unwrap(substr($envelope, 0, 65) . 'payload', 'ab'))->toBeNull();
});

it('returns null from unwrap for a tampered payload', function (): void {
    $signer = createCacheValueSigner();
    $envelope = $signer->wrap('payload', 'key');

    expect($signer->unwrap(substr($envelope, 0, 65) . 'planted', 'key'))->toBeNull();
});

it('returns null from unwrap for an unsigned or malformed envelope', function (): void {
    $signer = createCacheValueSigner();

    expect($signer->unwrap(serialize(['value' => 1]), 'key'))->toBeNull()
        ->and($signer->unwrap('', 'key'))->toBeNull()
        ->and($signer->unwrap(str_repeat('a', 64) . 'xpayload', 'key'))->toBeNull();
});

it('returns null from unwrap for an envelope signed with another key', function (): void {
    $envelope = createCacheValueSigner('other-key')->wrap('payload', 'key');

    expect(createCacheValueSigner()->unwrap($envelope, 'key'))->toBeNull();
});

it('throws signatureMismatch from verifyAndUnwrap for a tampered envelope', function (): void {
    $signer = createCacheValueSigner();

    expect(fn () => $signer->verifyAndUnwrap('not-an-envelope', 'key'))
        ->toThrow(TamperedCacheValueException::class, 'HMAC signature does not match');
});

it('throws emptySigningKey from every method when the key is empty', function (): void {
    $signer = createCacheValueSigner('');

    expect(fn () => $signer->wrap('payload', 'key'))
        ->toThrow(TamperedCacheValueException::class, 'the encryption key is empty')
        ->and(fn () => $signer->unwrap('not-an-envelope', 'key'))
        ->toThrow(TamperedCacheValueException::class, 'the encryption key is empty')
        ->and(fn () => $signer->verifyAndUnwrap('not-an-envelope', 'key'))
        ->toThrow(TamperedCacheValueException::class, 'the encryption key is empty');
});

it('throws a CacheException subtype so callers can catch cache failures uniformly', function (): void {
    expect(TamperedCacheValueException::signatureMismatch())->toBeInstanceOf(CacheException::class);
});
