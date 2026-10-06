# Task 005: Repository::insertBatch() generated keys via supportsReturning()

**Status**: completed
**Depends on**: 001, 002, 003, 004
**Retry count**: 0

## Description
Apply the same key rules to `insertBatch()` and switch its RETURNING path from `driverName() === 'pgsql'` to `supportsReturning()`.

## Context
- Related files: `packages/database/src/Repository/Repository.php`, `packages/database/tests/Repository/RepositoryBatchInsertTest.php`
- Reuse task 004's helper in `extractBatchRow()`.
- RETURNING is used when the key is auto-increment, or generated and omitted, and the connection supports it. Returned values go through `toPhpValue()`.
- An unset generated key on a connection without RETURNING throws before anything is executed.
- Existing pgsql-flavoured batch stubs must report `supportsReturning()` true.
- `upsert()` also goes through `extractBatchRows()` -> `extractBatchRow()`. Upsert never reads keys back, so for upsert: strip an unset/null generated key regardless of `supportsReturning()` (the database default fills it, MySQL included) and do NOT throw the no-RETURNING error; keep today's upsert behaviour for other keys (do not add the unset-key throw to upsert). Pass a flag/separate path from upsert so the insertBatch rules don't leak into it.
- A batch mixing set and unset generated keys currently surfaces as `BatchInsertException::columnSetMismatch`, which does not explain the cause. Detect it and throw a clear exception naming the entity and the key property (set all keys or none).

## Requirements (Test Descriptions)
- [x] `it reads generated keys back in insert order with RETURNING`
- [x] `it uses RETURNING for auto-increment keys when the connection supports it regardless of driver name`
- [x] `it throws RepositoryException for unset generated keys in a batch on a connection without RETURNING`
- [x] `it throws RepositoryException for unset keys that are neither generated nor auto-increment in a batch`
- [x] `it throws a clear exception for a batch that mixes set and unset generated keys`
- [x] `it omits unset generated keys from an upsert on a connection without RETURNING without throwing`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
