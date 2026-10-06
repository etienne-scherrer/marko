# Task 003: Shorten fk_ Names and Validate Declared Index Names in SchemaBuilder

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Derive `fk_<table>_<column>` through `IdentifierName::derive()`, and throw `EntityException::indexNameTooLong()` from `SchemaBuilder::buildIndex()` when a declared `#[Index]` name is over 63 bytes. `buildIndex()` takes the entity class so the error names it; `SchemaRegistry` passes the extender class.

## Context
- Related files: packages/database/src/Entity/SchemaBuilder.php, packages/database/src/Exceptions/EntityException.php, packages/database/src/Schema/SchemaRegistry.php, packages/database/tests/Entity/SchemaBuilderTest.php
- Patterns to follow: EntityException static factories (message/context/suggestion)

## Interface Contract
- `SchemaBuilder::buildIndex(IndexMetadata $metadata, string $entityClass): Index`. `$entityClass` is required (`@param class-string`). Throw before constructing the `Index` when `!IdentifierName::fits($metadata->name)`.
- `SchemaBuilder::build()` passes `$metadata->entityClass`.
- `SchemaRegistry::registerEntities()` (line ~165) passes `$extenderClass`. These are the only two in-repo callers.
- `EntityException::indexNameTooLong(string $entityClass, string $indexName, int $byteLength): self`. The message names the entity, the index and `$byteLength` vs `IdentifierName::MAX_BYTES`. The suggestion is to shorten the `#[Index(name:)]` to 63 bytes or fewer.
- FK name: `IdentifierName::derive("{$tableName}_{$column->name}", prefix: 'fk_')`. Update the `// Generate FK name` comment.
- Extender test goes in `packages/database/tests/Schema/SchemaRegistryTest.php` (extender + parent registered together, extender declares the over-long `#[Index]`) and asserts the message names the extender class.

## Requirements (Test Descriptions)
- [x] `it keeps a foreign key name that fits unchanged`
- [x] `it shortens a foreign key name over 63 bytes`
- [x] `it throws EntityException when a declared index name is longer than 63 bytes`
- [x] `it names the entity, the index and its byte length in the over-long index name error`
- [x] `it measures a declared multibyte index name in bytes`
- [x] `it accepts a declared index name of exactly 63 bytes`
- [x] `it rejects an over-long index name declared by an extender`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
`buildIndex()` and `EntityException::indexNameTooLong()` also take the table name, so the error names the table as well as the entity. `SchemaRegistry` passes the parent table name for extender indexes. The extender case is covered by a `ProductLongIndexExtenderEntity` fixture.
