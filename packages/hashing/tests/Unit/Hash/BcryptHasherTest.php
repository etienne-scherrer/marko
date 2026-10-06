<?php

declare(strict_types=1);

use Marko\Hashing\Contracts\HasherInterface;
use Marko\Hashing\Exceptions\InvalidHasherConfigException;
use Marko\Hashing\Exceptions\InvalidValueException;
use Marko\Hashing\Hash\BcryptHasher;

it('implements HasherInterface', function () {
    $hasher = new BcryptHasher(cost: 4);

    expect($hasher)->toBeInstanceOf(HasherInterface::class);
});

it('uses default cost of 12 when no cost provided', function () {
    expect(BcryptHasher::DEFAULT_COST)->toBe(12);
});

it('accepts custom cost parameter', function () {
    $hasher = new BcryptHasher(cost: 5);

    $hash = $hasher->hash('password');

    expect($hash)->toStartWith('$2y$05$');
});

it('hashes a password with bcrypt algorithm', function () {
    $hasher = new BcryptHasher(cost: 4);

    $hash = $hasher->hash('password');

    expect($hash)->toStartWith('$2y$')
        ->and(strlen($hash))->toBe(60);
});

it('produces different hashes for the same password due to salt', function () {
    $hasher = new BcryptHasher(cost: 4);

    $hash1 = $hasher->hash('password');
    $hash2 = $hasher->hash('password');

    expect($hash1)->not->toBe($hash2);
});

it('verifies correct password returns true', function () {
    $hasher = new BcryptHasher(cost: 4);

    $hash = $hasher->hash('password');

    expect($hasher->verify('password', $hash))->toBeTrue();
});

it('verifies incorrect password returns false', function () {
    $hasher = new BcryptHasher(cost: 4);

    $hash = $hasher->hash('password');

    expect($hasher->verify('wrong-password', $hash))->toBeFalse();
});

it('returns bcrypt as algorithm name', function () {
    $hasher = new BcryptHasher(cost: 4);

    expect($hasher->algorithm())->toBe('bcrypt');
});

it('indicates rehash needed when cost increases', function () {
    $hasher = new BcryptHasher(cost: 4);
    $hash = $hasher->hash('password');

    $newHasher = new BcryptHasher(cost: 6);

    expect($newHasher->needsRehash($hash))->toBeTrue();
});

it('indicates no rehash needed when cost is the same', function () {
    $hasher = new BcryptHasher(cost: 4);
    $hash = $hasher->hash('password');

    expect($hasher->needsRehash($hash))->toBeFalse();
});

it('throws InvalidHasherConfigException when cost is below 4', function () {
    expect(fn () => new BcryptHasher(cost: 3))
        ->toThrow(InvalidHasherConfigException::class, 'Invalid bcrypt cost parameter');
});

it('throws InvalidHasherConfigException when cost is above 31', function () {
    expect(fn () => new BcryptHasher(cost: 32))
        ->toThrow(InvalidHasherConfigException::class, 'Invalid bcrypt cost parameter');
});

it('provides helpful context in cost validation exception', function () {
    try {
        new BcryptHasher(cost: 2);
    } catch (InvalidHasherConfigException $e) {
        expect($e->getContext())->toContain('Cost must be between 4 and 31')
            ->and($e->getSuggestion())->toContain('Update config/hashing.php');
    }
});

it('accepts minimum valid cost of 4', function () {
    $hasher = new BcryptHasher(cost: 4);

    expect($hasher->hash('password'))->toStartWith('$2y$04$');
});

it('accepts maximum valid cost of 31', function () {
    // Note: cost of 31 would take too long, so just verify it's constructed
    // We trust PHP's password_hash to handle the actual limit
    // For testing, we just verify the constructor accepts 31 without throwing
    expect(fn () => new BcryptHasher(cost: 31))->not->toThrow(InvalidHasherConfigException::class);
});

it('rejects a value longer than 72 bytes instead of silently truncating it', function () {
    $hasher = new BcryptHasher(cost: 4);

    expect(fn () => $hasher->hash(str_repeat('a', 73)))
        ->toThrow(InvalidValueException::class, 'Value is longer than 72 bytes');
});

it('counts bytes rather than characters when enforcing the 72-byte limit', function () {
    $hasher = new BcryptHasher(cost: 4);

    // 37 two-byte characters = 74 bytes
    expect(fn () => $hasher->hash(str_repeat('é', 37)))
        ->toThrow(InvalidValueException::class);
});

it('rejects a value containing a NUL byte with a specific exception', function () {
    $hasher = new BcryptHasher(cost: 4);

    expect(fn () => $hasher->hash("secret\0suffix"))
        ->toThrow(InvalidValueException::class, 'Value contains a NUL byte');
});

it('accepts a value of exactly 72 bytes', function () {
    $hasher = new BcryptHasher(cost: 4);
    $value = str_repeat('a', 72);

    $hash = $hasher->hash($value);

    expect($hasher->verify($value, $hash))->toBeTrue();
});

it('returns false when verifying a value longer than 72 bytes', function () {
    $hasher = new BcryptHasher(cost: 4);
    $hash = $hasher->hash(str_repeat('a', 72));

    // bcrypt would otherwise ignore the 73rd byte and report a match
    expect($hasher->verify(str_repeat('a', 72) . 'Y', $hash))->toBeFalse();
});

it('returns false when verifying a value containing a NUL byte', function () {
    $hasher = new BcryptHasher(cost: 4);
    $hash = $hasher->hash('secret');

    expect($hasher->verify("secret\0", $hash))->toBeFalse();
});

/**
 * A BcryptHasher that records every dummy verification it runs before running it for real.
 */
function createDummyRecordingBcryptHasher(
    ArrayObject $dummyChecks,
): BcryptHasher {
    return new readonly class ($dummyChecks) extends BcryptHasher
    {
        public function __construct(
            private ArrayObject $dummyChecks,
        ) {
            parent::__construct(cost: 4);
        }

        protected function verifyDummy(
            string $value,
        ): void {
            $this->dummyChecks->append($value);

            parent::verifyDummy($value);
        }
    };
}

it('runs a dummy verification before rejecting a value longer than 72 bytes', function () {
    $dummyChecks = new ArrayObject();
    $hasher = createDummyRecordingBcryptHasher($dummyChecks);
    $value = str_repeat('a', 73);

    expect($hasher->verify($value, $hasher->hash('secret')))->toBeFalse()
        ->and($dummyChecks->getArrayCopy())->toBe([$value]);
});

it('runs a dummy verification before rejecting a value containing a NUL byte', function () {
    $dummyChecks = new ArrayObject();
    $hasher = createDummyRecordingBcryptHasher($dummyChecks);

    expect($hasher->verify("secret\0", $hasher->hash('secret')))->toBeFalse()
        ->and($dummyChecks->getArrayCopy())->toBe(["secret\0"]);
});

it('does not run a dummy verification for a value bcrypt can check', function () {
    $dummyChecks = new ArrayObject();
    $hasher = createDummyRecordingBcryptHasher($dummyChecks);
    $hash = $hasher->hash('secret');

    expect($hasher->verify('secret', $hash))->toBeTrue()
        ->and($hasher->verify('wrong', $hash))->toBeFalse()
        ->and($dummyChecks->count())->toBe(0);
});

it('verifies dummy values against a well-formed bcrypt hash of the configured cost', function () {
    $hasher = new BcryptHasher(cost: 5);
    $dummyHash = new ReflectionProperty(BcryptHasher::class, 'dummyHash')->getValue($hasher);

    $info = password_get_info($dummyHash);

    // A malformed hash would make password_verify() fail instantly instead of doing bcrypt work
    expect($info['algo'])->toBe(PASSWORD_BCRYPT)
        ->and($info['options']['cost'])->toBe(5)
        ->and(strlen($dummyHash))->toBe(60)
        ->and(strlen(crypt('attacker-guess', $dummyHash)))->toBe(60)
        ->and($hasher->needsRehash($dummyHash))->toBeFalse();
});

it('uses the default cost for the dummy hash when none is configured', function () {
    $hasher = new BcryptHasher();
    $dummyHash = new ReflectionProperty(BcryptHasher::class, 'dummyHash')->getValue($hasher);

    expect(password_get_info($dummyHash)['options']['cost'])->toBe(BcryptHasher::DEFAULT_COST);
});
