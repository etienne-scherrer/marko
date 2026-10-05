# Task 006: #[Encrypted] Columns and Schema Types

**Status**: completed
**Depends on**: 004
**Retry count**: 0

## Description
`#[Encrypted]` stores ciphertext via `EncryptorInterface` (marko/encryption is suggest-only), forces a text column, and fails loudly at metadata-parse time when no encryptor is available.

## Context
- `PropertyMetadata` gets trailing `bool $encrypted = false`.
- `EntityMetadataFactory::__construct(?ContainerInterface $container = null)` (it is a container singleton; the container injects the registered `ContainerInterface` instance). Parse-time check = `$container !== null && $container->has(EncryptorInterface::class)`; with no container, `#[Encrypted]` throws the same clear exception.
- Gotcha: `Container::has()` only checks `bindings` + `class_exists()` — encryptors registered via `$container->instance()` or a Preference are NOT seen. Tests must register the fake encryptor with `bind()`. Document the limitation in the exception suggestion.
- The encryptor itself is resolved lazily via `ContainerInterface::get(EncryptorInterface::class)` on first encrypt/decrypt, never at parse time.
- Write: cast `toDatabase()` → `(string)` → `encrypt()` (strict_types: ints/floats/bools must be stringified). Read: `decrypt()` → cast `toPhp()`. Dirty checks use the pre-encryption snapshot from task 004 (ciphertext is non-deterministic).
- packages/database/composer.json: add `marko/encryption` to `suggest` and `require-dev` (moved here from task 007).

## Requirements (Test Descriptions)
- [x] `it stores ciphertext rather than plaintext`
- [x] `it hydrates the plaintext`
- [x] `it throws a clear exception at parse time when no encryptor is bound`
- [x] `it generates a text column for encrypted properties regardless of PHP type`
- [x] `it generates the declared column type for cast properties`
- [x] `it rejects combining Encrypted with a primary key`
- [x] `it throws a clear exception at parse time when the metadata factory has no container`
- [x] `it rejects combining Encrypted with a unique column or an Index`
- [x] `it wraps decryption failures in an entity exception naming the entity, property and column`
- [x] `it round-trips encrypted int, bool, array and DateTimeImmutable properties`
- [x] `it does not mark an unchanged encrypted property dirty`
- [x] `it throws when findBy, findOneBy or existsBy criteria target an encrypted property`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Added `Attributes/Encrypted`; `EntityMetadataFactory` takes optional container and validates #[Encrypted] at parse time (cast conflict, PK, unique/Index, non-text type, missing package, missing encryptor). Encryptor detected via `has()` or `resolvedInstances()` (so `instance()` works too); never instantiated at parse time.
- Encrypted columns are always `text`; PropertyMetadata keeps its columnType so the inner cast is still picked by PHP type. Explicit `type: 'json'`/`datetime` on an encrypted column is rejected (use plain #[Column]).
- EntityHydrator::hydrate wraps EncryptionException in `EntityException::decryptionFailed` (entity, property, column, previous).
- Repository::criteriaValue rejects encrypted properties (`EntityException::encryptedCriteria`) for findBy/findOneBy/existsBy.
- Schema: cast property without explicit type uses the inferred default (`varchar`; `json` for array); explicit `#[Column(type:, length:)]` is honored.
- composer.json: marko/encryption in suggest and require-dev.
- Tests: packages/database/tests/Entity/Cast/EncryptedColumnTest.php. Database suite green (2 pre-existing risky).
