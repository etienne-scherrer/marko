# Task 004: Repository::insert() generated keys and loud unset-key errors

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
Make `save()` (via `insert()`) handle the primary key explicitly: an unset/null auto-increment key keeps today's behaviour; an unset/null generated key is omitted and read back with `RETURNING <pk>` when `supportsReturning()` is true, else a `RepositoryException`; any other unset/null key throws a `RepositoryException`.

## Context
- Related files: `packages/database/src/Repository/Repository.php`, `packages/database/src/Exceptions/RepositoryException.php`, `packages/database/tests/Repository/RepositoryTest.php`
- Put the key logic in one private helper reused by task 005 (strip/validate the key column of an extracted row).
- Read-back: `$this->connection->query("$sql RETURNING $pkColumn", $bindings)`, then set the property to `$this->hydrator->toPhpValue($row[$pkColumn], $pkMetadata)`. Throw a `RepositoryException` if the RETURNING result does not hold exactly one row.
- The no-RETURNING error names the entity and says to set the key in PHP before saving (e.g. a UUID from ramsey/uuid or symfony/uid).
- The unset-key error says to set the key or mark the column `generated: true` with a database default.
- Checks run inside insert() so EntityCreating observers can still assign the key.
- Design the helper with a flag (or separate entry point) so task 005's `upsert()` path can strip an unset/null generated key WITHOUT the no-RETURNING / unset-key throws (see task 005).
- Throwing for an unset/null non-generated, non-auto-increment key changes existing behaviour (it used to reach the database). Run the FULL `composer test` (admin-auth, notification, session-database, queue-database, `database/tests/Testing` EntityFactory, etc.), not just the database package, and fix any fixtures that relied on it.

## Requirements (Test Descriptions)
- [x] `it omits an unset generated key from the INSERT and reads it back with RETURNING`
- [x] `it omits a null generated key from the INSERT and reads it back with RETURNING`
- [x] `it inserts a generated key that is already set without RETURNING`
- [x] `it throws RepositoryException for an unset generated key on a connection without RETURNING`
- [x] `it throws RepositoryException for an unset key that is neither generated nor auto-increment`
- [x] `it throws RepositoryException for a null key that is neither generated nor auto-increment`
- [x] `it treats the entity as persisted after a generated key is read back`

## Acceptance Criteria
- All requirements have passing tests
- Existing auto-increment behaviour unchanged

## Implementation Notes
