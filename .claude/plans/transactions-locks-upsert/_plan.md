# Plan: Savepoints, After-Commit Callbacks, Row Locks and Upsert

## Created
2026-10-05

## Status
completed

## Objective
Give the database layer the four transactional primitives apps need: nested transactions through savepoints, after-commit/after-rollback callbacks, row locks on the query builder, and upsert on the query builder and repository. Implement them in both drivers and in `ReadWriteConnection`.

## Related Issues
Closes #176

## Discovery Notes
- `PgSqlConnection` / `MySqlConnection` throw `TransactionException::nestedTransactionNotSupported()` on a nested `beginTransaction()`. Their transaction code is identical, so depth and callback bookkeeping goes in one driver-agnostic class (`Marko\Database\Connection\TransactionState`) that both drivers compose. The drivers keep the SQL (`BEGIN` / `SAVEPOINT` / `RELEASE` / `ROLLBACK TO`). No traits, no shared base class.
- `ReadWriteConnection::transaction()` resets `stickyWrite` to false in `finally`, so a nested call would send the rest of the outer transaction's reads to a replica. Restore the previous value instead.
- `Repository::insertBatch()` opens its own transaction only when none is active. With savepoints it can always call `transaction()`, guarded only by `instanceof TransactionInterface`.
- `QueryBuilderInterface` has no locking. Build the SELECT through `buildSelectSql()` in each driver. The query builder holds a plain `ConnectionInterface`, so the lock-outside-transaction check is `connection instanceof TransactionInterface && inTransaction()`.
- MySQL support: 8.0+ and MariaDB 10.5+. Upsert uses `ON DUPLICATE KEY UPDATE col = VALUES(col)`, which works on both. The 8.0.19 row alias does not work on MariaDB. Shared lock: `LOCK IN SHARE MODE`, or `FOR SHARE` plus the modifier when `skipLocked()` / `noWait()` is set, since MySQL's `LOCK IN SHARE MODE` takes no modifiers. Caveats to document: MariaDB has no `FOR SHARE`, so `sharedLock()` with a modifier is MySQL-8-only, and `SKIP LOCKED` needs MariaDB 10.6+.
- About 17 test doubles across packages implement `TransactionInterface`. They must gain the new methods. Same for the `QueryBuilderInterface` doubles in the database tests.
- Integration: per-driver `integration` group tests exist in `packages/database-{pgsql,mysql}/tests/Integration` and are gated on `MARKO_TEST_*` env vars. The #187 app suite (`tests/Integration/App`, Postgres) has two `#176` todos in `KnownGapsTest.php` to flip.
- The `DatabaseQueue` refactor to `lockForUpdate()` is left out. The queue uses a raw connection and has no query builder factory, and the ticket allows leaving it out.

## Scope

### In Scope
- `TransactionInterface::transactionLevel()`, `afterCommit()`, `afterRollback()`; savepoint nesting in both drivers. `nestedTransactionNotSupported()` is removed.
- `commit()` / `rollback()` with no open transaction throw `TransactionException::notInTransaction()`.
- `ReadWriteConnection` delegates the new methods and keeps sticky writes through nesting.
- `lockForUpdate()`, `sharedLock()`, `skipLocked()`, `noWait()` on `QueryBuilderInterface` and `RepositoryQueryBuilder`, compiled per driver. A locked read outside a transaction throws.
- `upsert(rows, uniqueBy, update)` on `QueryBuilderInterface`, compiled per driver. `Repository::upsert(entities, uniqueBy, update)`.
- `insertBatch()` uses `transaction()`.
- Unit tests, driver integration tests (pgsql + mysql), and app-suite tests that flip the #176 todos.
- Docs: database.md, database-pgsql.md, database-mysql.md, database-readwrite.md.

### Out of Scope
- `DatabaseQueue` refactor (see above)
- Hydrating generated ids or firing lifecycle events from `upsert()`
- Locks on aggregates or unions (these throw instead)

## Success Criteria
- [x] Nested `transaction()` calls commit together. A caught inner failure rolls back only the inner work. An outer failure rolls back everything (pgsql + mysql integration).
- [x] `transactionLevel()` is correct through nesting and rollback
- [x] After-commit and after-rollback semantics are tested at unit and integration level
- [x] `ReadWriteConnection` delegates every new method
- [x] Lock SQL for both drivers, plus an integration test showing a second connection skips or fails on the locked row
- [x] Upsert SQL for both drivers. Integration covers insert-new, update-existing and mixed batches.
- [x] `insertBatch()` uses `transaction()` and its tests stay green
- [x] Docs updated
- [x] All tests passing; `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | TransactionState and TransactionInterface additions | - | completed |
| 002 | PgSqlConnection savepoints and callbacks | 001 | completed |
| 003 | MySqlConnection savepoints and callbacks | 001 | completed |
| 004 | ReadWriteConnection delegation and nested sticky writes | 001 | completed |
| 005 | Update TransactionInterface test doubles; insertBatch via transaction() | 001 | completed |
| 006 | Lock and upsert API on QueryBuilderInterface, RepositoryQueryBuilder, exceptions, all implementers kept compiling | - | completed |
| 007 | PgSqlQueryBuilder locks and upsert | 006 | completed |
| 008 | MySqlQueryBuilder locks and upsert | 006 | completed |
| 009 | Repository::upsert | 005, 006 | completed |
| 010 | Driver integration tests (pgsql + mysql) | 002, 003, 007, 008, 009 | completed |
| 011 | App integration suite: flip #176 todos, plus an end-to-end lock check | 002, 006, 007 | completed |
| 012 | Documentation | 001-011 | completed |

## Architecture Notes
- `TransactionState` is a plain mutable class (not readonly) and pure. It holds a stack of levels, each with after-commit and after-rollback callback lists. When an inner level commits, its lists merge into the parent. After the outermost commit, the after-commit callbacks run once the state is back at level 0. A rollback discards that level's after-commit list and runs its after-rollback list. `afterCommit()` at level 0 runs immediately. `afterRollback()` at level 0 does nothing, since nothing can roll back.
- Savepoint names: `marko_sp_{depth}`, where depth is the level before the savepoint opens (1 for the first nested level).
- `transaction()` commits outside the `try`, so a failed COMMIT never triggers a second rollback. A failed outermost COMMIT rolls the PDO transaction back if it's still open, runs the after-rollback callbacks, then rethrows.
- `reset()` rolls back and clears the state without running callbacks.
- Lock and upsert errors are loud: `LockException`, `UpsertException`, `TransactionException::notInTransaction`.
- `disconnect()` clears TransactionState (the PDO transaction is gone). `ReadWriteConnection::reset()` delegates to `write->reset()` when the write connection is `ResettableInterface`. Otherwise it rolls back until `transactionLevel()` is 0. Never a single `rollback()`.

### Shared contract for tasks 006/007/008/009 (built in parallel)
- `lockForUpdate(): static`, `sharedLock(): static`, `skipLocked(): static`, `noWait(): static`
- Lock validation happens when the SQL is compiled, not when the method is called, so call order does not matter. The compiler throws `LockException` in these cases:
  - a modifier without a lock
  - both modifiers
  - no transaction open (`!($connection instanceof TransactionInterface) || !$connection->inTransaction()`)
  - a lock combined with an aggregate (count/min/max/sum/avg), with a union, or with `compileSubquery()`
- The lock clause is appended after LIMIT/OFFSET.
- `upsert(array $rows, array $uniqueBy, ?array $update = null): int` returns the driver's affected-row count. MySQL counts 2 for an updated row and 0 for an unchanged one, so callers must not compare counts across drivers.
  - `$update === null`: update every row column not in `$uniqueBy`.
  - `$update === []`, or no columns left to update: insert-or-ignore. PostgreSQL uses `DO NOTHING`; MySQL uses `ON DUPLICATE KEY UPDATE {first uniqueBy col} = {same col}`. Never `INSERT IGNORE`.
  - Every row must have the same key set as the first row. Values are bound in the first row's key order.
  - `$uniqueBy` must be non-empty. Both `$uniqueBy` and `$update` must be subsets of the row columns.
  - Every identifier goes through `IdentifierValidator`. Executes via `$connection->execute()`.
  - Throws `UpsertException` for empty rows, empty `uniqueBy`, mismatched keys, and unknown `uniqueBy`/`update` columns.
- MySQL `ON DUPLICATE KEY` fires on any unique key, including the PK. `uniqueBy` only shapes the default update list there. PostgreSQL needs a unique index or constraint that matches `uniqueBy` exactly, and rejects a batch that hits the same conflict key twice. Both are documented, not worked around.

## Risks & Mitigations
- Breaking change for third-party drivers and test doubles: called out in the PR and labelled `breaking`.
- Hotspot conflicts with #170 and #177 on the connection classes: changes are confined to the transaction methods.
- MySQL `VALUES()` is deprecated on 8.0.20+, but it's the only syntax shared with MariaDB. This is documented.
