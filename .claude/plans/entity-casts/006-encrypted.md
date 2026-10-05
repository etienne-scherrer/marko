# Task 006: #[Encrypted] Columns and Schema Types

**Status**: pending
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
- [ ] `it stores ciphertext rather than plaintext`
- [ ] `it hydrates the plaintext`
- [ ] `it throws a clear exception at parse time when no encryptor is bound`
- [ ] `it generates a text column for encrypted properties regardless of PHP type`
- [ ] `it generates the declared column type for cast properties`
- [ ] `it rejects combining Encrypted with a primary key`
- [ ] `it throws a clear exception at parse time when the metadata factory has no container`
- [ ] `it rejects combining Encrypted with a unique column or an Index`
- [ ] `it wraps decryption failures in an entity exception naming the entity, property and column`
- [ ] `it round-trips encrypted int, bool, array and DateTimeImmutable properties`
- [ ] `it does not mark an unchanged encrypted property dirty`
- [ ] `it throws when findBy, findOneBy or existsBy criteria target an encrypted property`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
