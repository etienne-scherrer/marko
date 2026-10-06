<?php

declare(strict_types=1);

use Marko\Config\Exceptions\ConfigNotFoundException;
use Marko\Encryption\Config\EncryptionConfig;
use Marko\Encryption\Exceptions\EncryptionException;
use Marko\Testing\Fake\FakeConfigRepository;

describe('EncryptionConfig', function (): void {
    it('reads key from config without fallback', function (): void {
        $config = new EncryptionConfig(new FakeConfigRepository([
            'encryption.key' => 'test-key-value',
        ]));

        expect($config->key())->toBe('test-key-value');
    });

    it('reads cipher from config without fallback', function (): void {
        $config = new EncryptionConfig(new FakeConfigRepository([
            'encryption.cipher' => 'aes-256-cbc',
        ]));

        expect($config->cipher())->toBe('aes-256-cbc');
    });

    it('throws ConfigNotFoundException when key is missing', function (): void {
        $config = new EncryptionConfig(new FakeConfigRepository([]));

        $config->key();
    })->throws(ConfigNotFoundException::class);

    it('throws ConfigNotFoundException when cipher is missing', function (): void {
        $config = new EncryptionConfig(new FakeConfigRepository([]));

        $config->cipher();
    })->throws(ConfigNotFoundException::class);

    it('reads previous keys from config', function (): void {
        $config = new EncryptionConfig(new FakeConfigRepository([
            'encryption.previous_keys' => ['old-key-1', 'old-key-2'],
        ]));

        expect($config->previousKeys())->toBe(['old-key-1', 'old-key-2']);
    });

    it('throws EncryptionException when a previous key is not a string', function (): void {
        $config = new EncryptionConfig(new FakeConfigRepository([
            'encryption.previous_keys' => ['old-key', 42],
        ]));

        $config->previousKeys();
    })->throws(EncryptionException::class, 'Invalid encryption.previous_keys configuration');

    it('reads aad_fallback from config', function (): void {
        $config = new EncryptionConfig(new FakeConfigRepository([
            'encryption.aad_fallback' => false,
        ]));

        expect($config->aadFallback())->toBeFalse();
    });

    it('throws ConfigNotFoundException when previous_keys is missing', function (): void {
        $config = new EncryptionConfig(new FakeConfigRepository([]));

        $config->previousKeys();
    })->throws(ConfigNotFoundException::class);

    it('config file contains all required keys with defaults', function (): void {
        $configFile = require dirname(__DIR__, 3) . '/config/encryption.php';

        expect($configFile)->toHaveKey('key')
            ->and($configFile)->toHaveKey('cipher')
            ->and($configFile['previous_keys'])->toBe([])
            ->and($configFile['aad_fallback'])->toBeTrue();
    });
});
