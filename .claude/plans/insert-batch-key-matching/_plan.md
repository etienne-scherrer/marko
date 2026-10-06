# Plan: insertBatch() Key Matching

## Created
2026-10-06

## Status
completed

## Objective
Implement the recommendation of issue #342 (option A for the `RETURNING` path, option D for the MySQL path): keep positional `RETURNING` matching but document and test the reliance, and make the MySQL `LAST_INSERT_ID()` arithmetic honour `auto_increment_increment` so ids are never silently wrong.

## Related Issues
Closes #342

## Discovery Notes
- `Repository::insertBatch()` (`packages/database/src/Repository/Repository.php`) has two key strategies: `INSERT ... RETURNING <pk>` matched by row position (PostgreSQL, MariaDB 10.5+), and `LAST_INSERT_ID() + $offset` (MySQL, any connection without `RETURNING`).
- The MySQL arithmetic assumes `auto_increment_increment = 1`. With step N every id after the first is wrong.
- The arithmetic also runs when every entity already carries an explicit auto-increment id (the pk column is then in the `INSERT`); `LAST_INSERT_ID()` is not updated by explicit ids, so the explicit ids are overwritten with stale values. Same code path, same silent wrong-id class: fixed here too.
- `RepositoryInterface::insertBatch()` docblock says "only reliable when innodb_autoinc_lock_mode is 0 or 1" and "PostgreSQL uses RETURNING" (stale since #339 added MariaDB).
- `MySqlConnection::driverName()` is `'mysql'` for MySQL and MariaDB; `@@auto_increment_increment` is a MySQL/MariaDB session variable, so it is read only when `driverName() === 'mysql'`, through `ConnectionInterface::query()` (works behind `marko/database-readwrite`, inside the batch transaction, i.e. on the write connection).
- Integration tests: `packages/database-mysql/tests/Integration/GeneratedPrimaryKeysTest.php` (MySQL 8.4 / MariaDB 11.8 / 10.11), `packages/database-pgsql/tests/Integration/GeneratedPrimaryKeysTest.php`.
- Docs: `packages/docs-markdown/docs/packages/database.md` (generated keys section and "ID assignment after batch insert"), `database-mysql.md`.

## Scope

### In Scope
- MySQL path reads `@@auto_increment_increment` per batch (session setting can change at any time) and assigns `firstId + offset * increment`; throws `BatchInsertException` naming the setting when it can't be read as a positive integer.
- Skip key arithmetic when the batch carries explicit keys.
- PostgreSQL integration test with an identity sequence `INCREMENT BY 5`.
- MySQL 8.4 integration test with `SET SESSION auto_increment_increment = 5`.
- Docblock and docs: positional `RETURNING` reliance, precise `innodb_autoinc_lock_mode` condition, `save()` inside `transaction()` for a per-row guarantee.

### Out of Scope
- Options B and C from the issue (explicit row matching, per-row inserts).
- Changing `ConnectionInterface` (no new methods).

## Success Criteria
- [ ] MySQL `insertBatch()` assigns correct ids with `auto_increment_increment = 5`
- [ ] PostgreSQL `insertBatch()` assigns correct ids with sequence `INCREMENT BY 5`
- [ ] Docblock and docs updated per the issue's exit criteria
- [ ] All tests passing
- [ ] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | MySQL key arithmetic honours auto_increment_increment | - | completed |
| 002 | MySQL 8.4 integration test with auto_increment_increment = 5 | 001 | completed |
| 003 | PostgreSQL integration test with sequence INCREMENT BY 5 | - | completed |
| 004 | Docblock and docs | 001 | completed |

## Architecture Notes
- No `ConnectionInterface` change: the setting is read with `query('SELECT @@auto_increment_increment AS ...')` only on `driverName() === 'mysql'`; other drivers without `RETURNING` keep step 1.
- Order: read the step BEFORE the INSERT. `lastInsertId()` (`mysql_insert_id()`) reflects the last statement, so a `SELECT` between the INSERT and `lastInsertId()` would reset it to 0.
- "Explicit keys" means the pk column is in the INSERT column list (`withoutDatabaseFilledKey()` drops unset auto-increment keys). `$readsGeneratedKeys` only covers `generated` keys and is not the right signal.
- Explicit-id tests use non-consecutive ids. With consecutive ids, the old arithmetic passes by accident.
- Positional `RETURNING` matching stays (one statement); the CI integration tests on every server version are the tripwire.

## Risks & Mitigations
- Extra round trip per MySQL batch: one tiny `SELECT`, only on MySQL without `RETURNING`; acceptable for correctness.
- Concurrent bulk inserts under `innodb_autoinc_lock_mode = 2` can still interleave: documented precisely; `save()` inside `transaction()` is the per-row guarantee.
