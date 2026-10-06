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

it('wraps a payload in a hex HMAC-SHA256 envelope', function (): void {
    $envelope = createCacheValueSigner()->wrap('s:5:"hello";');

    expect($envelope)->toBe(hash_hmac('sha256', 's:5:"hello";', 'cache-signer-test-key') . '.s:5:"hello";');
});

it('unwraps an envelope it signed', function (): void {
    $signer = createCacheValueSigner();

    expect($signer->unwrap($signer->wrap('payload')))->toBe('payload')
        ->and($signer->verifyAndUnwrap($signer->wrap('payload')))->toBe('payload');
});

it('returns null from unwrap for a tampered payload', function (): void {
    $signer = createCacheValueSigner();
    $envelope = $signer->wrap('payload');

    expect($signer->unwrap(substr($envelope, 0, 65) . 'planted'))->toBeNull();
});

it('returns null from unwrap for an unsigned or malformed envelope', function (): void {
    $signer = createCacheValueSigner();

    expect($signer->unwrap(serialize(['value' => 1])))->toBeNull()
        ->and($signer->unwrap(''))->toBeNull()
        ->and($signer->unwrap(str_repeat('a', 64) . 'xpayload'))->toBeNull();
});

it('returns null from unwrap for an envelope signed with another key', function (): void {
    $envelope = createCacheValueSigner('other-key')->wrap('payload');

    expect(createCacheValueSigner()->unwrap($envelope))->toBeNull();
});

it('throws signatureMismatch from verifyAndUnwrap for a tampered envelope', function (): void {
    $signer = createCacheValueSigner();

    expect(fn () => $signer->verifyAndUnwrap('not-an-envelope'))
        ->toThrow(TamperedCacheValueException::class, 'HMAC signature does not match');
});

it('throws emptySigningKey from every method when the key is empty', function (): void {
    $signer = createCacheValueSigner('');

    expect(fn () => $signer->wrap('payload'))
        ->toThrow(TamperedCacheValueException::class, 'the encryption key is empty')
        ->and(fn () => $signer->unwrap('not-an-envelope'))
        ->toThrow(TamperedCacheValueException::class, 'the encryption key is empty')
        ->and(fn () => $signer->verifyAndUnwrap('not-an-envelope'))
        ->toThrow(TamperedCacheValueException::class, 'the encryption key is empty');
});

it('throws a CacheException subtype so callers can catch cache failures uniformly', function (): void {
    expect(TamperedCacheValueException::signatureMismatch())->toBeInstanceOf(CacheException::class);
});
