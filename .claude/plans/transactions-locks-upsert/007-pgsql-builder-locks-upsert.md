# Task 007: PgSqlQueryBuilder locks and upsert

**Status**: completed
**Depends on**: [006]
**Retry count**: 0

## Description
Compile FOR UPDATE and FOR SHARE, with SKIP LOCKED or NOWAIT. Compile upsert as INSERT ... ON CONFLICT (...) DO UPDATE SET col = EXCLUDED.col, or DO NOTHING when the update list is empty.

## Context
- Related files: packages/database-pgsql/src/Query/PgSqlQueryBuilder.php, packages/database-pgsql/tests/Query/

## Requirements (Test Descriptions)
- [x] `it appends FOR UPDATE for lockForUpdate`
- [x] `it appends FOR SHARE for sharedLock`
- [x] `it appends SKIP LOCKED and NOWAIT modifiers`
- [x] `it throws when a lock is used outside a transaction`
- [x] `it throws when a lock modifier is used without a lock`
- [x] `it throws when a lock is combined with an aggregate or union`
- [x] `it compiles upsert with ON CONFLICT DO UPDATE using every non-unique column by default`
- [x] `it compiles upsert with an explicit update column list`
- [x] `it compiles upsert with DO NOTHING when the update list is empty`
- [x] `it rejects empty rows, empty uniqueBy and mismatched row columns`
- [x] `it appends the lock clause after LIMIT and OFFSET`
- [x] `it validates lock modifiers at compile time regardless of call order`
- [x] `it throws when skipLocked and noWait are both set`
- [x] `it throws when compileSubquery is called on a locked builder`
- [x] `it rejects uniqueBy or update columns that are not in the rows`
- [x] `it executes the upsert through connection execute and returns the affected-row count`

Notes: follow the "Shared contract" in `_plan.md`. Replace the 006 placeholder `upsert()`. `runAggregate()` and `executeUnion()` do not go through `buildSelectSql()`, so the lock check must cover them explicitly. Do not dedupe conflict keys: PostgreSQL's "cannot affect row a second time" error is the loud error.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
