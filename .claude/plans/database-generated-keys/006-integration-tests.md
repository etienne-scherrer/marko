# Task 006: PostgreSQL and MySQL integration tests

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005
**Retry count**: 0

## Description
Prove the behaviour against real servers: PostgreSQL generates and returns uuid keys through `save()` and `insertBatch()`; MySQL throws the loud error for an unset generated key and saves normally when the key is set.

## Context
- Related files: `packages/database-pgsql/tests/Integration/`, `packages/database-mysql/tests/Integration/`, `tests/Fixtures/IntegrationDatabase` in each package
- Pattern: `ColumnCastsAndExpressionDefaultsTest.php` (group `integration-services`, skips without a host)
- Build a Repository with `new EntityMetadataFactory()` and `new EntityHydrator()`.
- Create the tables with raw DDL, not the schema generator/migrations (those are owned by parallel #306/#307): PostgreSQL `id UUID PRIMARY KEY DEFAULT gen_random_uuid()`, MySQL `id CHAR(36) PRIMARY KEY DEFAULT (UUID())`. The entity still needs a `default:` on the `#[Column]` to pass task 001's parse-time validation.

## Requirements (Test Descriptions)
- [x] `it saves an entity with a database-generated uuid key and reads the key back`
- [x] `it finds the saved entity by its generated key`
- [x] `it gives each batch-inserted entity its own generated key in insert order`
- [x] `it throws RepositoryException when saving an unset generated key on MySQL`
- [x] `it saves an entity with a generated key column when the key is set in PHP on MySQL`

## Acceptance Criteria
- Tests pass when the servers are available; skip otherwise

## Implementation Notes
