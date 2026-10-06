# Task 003: Hydrator omits an uninitialized primary key on extract

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`EntityHydrator::extract()` raises a raw PHP `Error` when the primary key property is uninitialized. Omit the key column instead, so the repository can decide whether the database generates it or a `RepositoryException` is due.

## Context
- Related files: `packages/database/src/Entity/EntityHydrator.php`, `packages/database/tests/Entity/EntityHydratorTest.php`
- Only the primary key is skipped; other uninitialized properties keep today's behaviour.

## Requirements (Test Descriptions)
- [x] `it omits an uninitialized primary key from the extracted row`
- [x] `it extracts a null primary key as null`
- [x] `it extracts an initialized string primary key`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
