# Task 001: MySQL key arithmetic honours auto_increment_increment

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
In the non-`RETURNING` branch of `Repository::insertBatch()`, read `@@auto_increment_increment` through the connection when the driver is `mysql` and assign `firstId + offset * increment`. Throw a `BatchInsertException` naming the setting when the value isn't a positive integer. Don't overwrite keys the batch set explicitly.

## Context
- Related files: `packages/database/src/Repository/Repository.php`, `packages/database/src/Exceptions/BatchInsertException.php`, `packages/database/tests/Repository/RepositoryBatchInsertTest.php`
- Patterns to follow: existing named constructors on `BatchInsertException`; spy connections in `RepositoryBatchInsertTest.php`

## Implementation Constraints (from devil's advocate review)
- **Statement order matters.** `lastInsertId()` is `PDO::lastInsertId()` / `mysql_insert_id()`, which reflects the LAST statement on the connection; a `SELECT` between the INSERT and `lastInsertId()` resets it to 0. Read the step with `query()` BEFORE `execute($sql, ...)` (inside the `$write` closure, so it runs inside the transaction / on the write connection behind `marko/database-readwrite`), then call `lastInsertId()` immediately after `execute()`.
- **Explicit-key detection:** do NOT reuse `$readsGeneratedKeys` (it only covers `isGenerated` keys and is always false for `autoIncrement`). The batch carries explicit keys when the pk column is in the INSERT column list: `in_array($pkProperty->columnName, $columns, true)` (`withoutDatabaseFilledKey()` drops unset/null auto-increment keys). Only read the step and do arithmetic when `$isAutoIncrement` and the pk column is absent.
- **Value parsing:** use `SELECT @@auto_increment_increment AS <alias>`. Depending on PDO emulation the value is `int` or a numeric `string`; accept either when it is an integer >= 1. Throw a new `BatchInsertException` named constructor (with message/context/suggestion naming `auto_increment_increment` and the bad value) on: no row, missing column, `0`, negative, non-numeric.
- Assign `firstId + offset * step`; update the stale inline comment ("returns the FIRST inserted id ... when innodb_autoinc_lock_mode is 0 or 1") to describe the step and the lock-mode condition.
- Existing spies return `driverName() === 'sqlite'`, so they are unaffected; add a mysql-flavoured spy whose `query()` returns the configured step and logs calls.

## Requirements (Test Descriptions)
- [x] `it steps MySQL batch ids by auto_increment_increment`
- [x] `it reads auto_increment_increment inside the batch transaction on the same connection`
- [x] `it reads auto_increment_increment before the INSERT so lastInsertId() reflects the INSERT` (spy's `lastInsertId()` returns 0 if any `query()` ran after the last `execute()`)
- [x] `it accepts auto_increment_increment returned as a numeric string`
- [x] `it throws BatchInsertException naming auto_increment_increment when the setting cannot be read` (cover: empty result, 0, non-numeric)
- [x] `it keeps explicit auto-increment keys set on every entity in the batch` (use non-consecutive, non-monotonic ids such as 10, 50, 30 and a spy `lastInsertId()` returning an unrelated value; assert no step query is issued)
- [x] `it does not read auto_increment_increment on a non-mysql connection without RETURNING`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
Added Repository::readAutoIncrementStep() (mysql only, read before execute inside the write closure) and BatchInsertException::unreadableAutoIncrementStep(). Key arithmetic skipped when the pk column is in the INSERT columns. phpcs/phpstan clean; database tests pass.
