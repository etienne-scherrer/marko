# Task 008: MySqlQueryBuilder locks and upsert

**Status**: completed
**Depends on**: [006]
**Retry count**: 0

## Description
Compile FOR UPDATE and LOCK IN SHARE MODE (FOR SHARE when a modifier is set). Compile upsert as INSERT ... ON DUPLICATE KEY UPDATE col = VALUES(col).

## Context
- Related files: packages/database-mysql/src/Query/MySqlQueryBuilder.php, packages/database-mysql/tests/Query/

## Requirements (Test Descriptions)
- [x] `it appends FOR UPDATE for lockForUpdate`
- [x] `it appends LOCK IN SHARE MODE for sharedLock`
- [x] `it appends SKIP LOCKED and NOWAIT modifiers`
- [x] `it throws when a lock is used outside a transaction`
- [x] `it throws when a lock modifier is used without a lock`
- [x] `it throws when a lock is combined with an aggregate or union`
- [x] `it compiles upsert with ON DUPLICATE KEY UPDATE using every non-unique column by default`
- [x] `it compiles upsert with an explicit update column list`
- [x] `it compiles a no-op update when the update list is empty`
- [x] `it rejects empty rows, empty uniqueBy and mismatched row columns`
- [x] `it appends the lock clause after LIMIT and OFFSET`
- [x] `it validates lock modifiers at compile time regardless of call order`
- [x] `it throws when skipLocked and noWait are both set`
- [x] `it throws when compileSubquery is called on a locked builder`
- [x] `it emits FOR SHARE with the modifier when sharedLock is combined with skipLocked or noWait`
- [x] `it rejects uniqueBy or update columns that are not in the rows`
- [x] `it executes the upsert through connection execute and returns the affected-row count`

Notes: follow the "Shared contract" in `_plan.md` and replace the 006 placeholder `upsert()`. The no-op form is `ON DUPLICATE KEY UPDATE {first uniqueBy col} = {same col}`, never `INSERT IGNORE`. `uniqueBy` does not appear in the SQL; it only shapes the default update list, so add a docblock saying so. `FOR SHARE` plus a modifier is MySQL 8 only (MariaDB lacks `FOR SHARE`), and `SKIP LOCKED` needs MariaDB 10.6+. Document this and do not try to detect the server.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
