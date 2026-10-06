# Task 008: MySqlGenerator orders a foreign key column's replacement index around its unique index

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
When an FK column stops being unique, task 001's diff drops its unique index and adds a plain replacement index on the
same column. InnoDB refuses to drop the only index a foreign key uses (error 1553), and MySqlGenerator currently drops
indexes (step 2) before it adds them (step 6) in up, and drops added indexes before re-adding dropped ones in down. Both
directions fail on a real server.

## Context
- Related files: packages/database-mysql/src/Sql/MySqlGenerator.php (`generateTableAlterations()`,
  `generateReverseTableAlterations()`), packages/database-mysql/tests/Sql/MySqlGeneratorTest.php
- PgSqlGenerator already adds indexes before it drops them in up; no PostgreSQL change is needed.

## Requirements (Test Descriptions)
- [x] `it adds the replacement index before dropping the unique index it replaces`
- [x] `it restores the unique index before dropping the replacement index in down`
- [x] `it keeps dropping indexes before dropping columns and adding indexes after adding columns`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- A "replacement" is an index in `indexesToAdd` whose columns equal those of an index in `indexesToDrop` and none of
  whose columns are in `columnsToAdd`. Only those move; all other index adds/drops keep their current position.
