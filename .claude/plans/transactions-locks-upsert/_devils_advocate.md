# Devil's Advocate Review: transactions-locks-upsert

Note: during this review, tasks 001-004 were already being built in this worktree (TransactionState, the driver savepoint code and ReadWriteConnection delegation all exist). Findings were checked against that in-progress code where it applies.

## Critical (Must fix before building)

1. **Task 006 breaks every QueryBuilderInterface implementer until 007 and 008 land.** Adding `lockForUpdate()`/`sharedLock()`/`skipLocked()`/`noWait()`/`upsert()` to the interface means `PgSqlQueryBuilder`, `MySqlQueryBuilder` and `packages/database-pgsql/tests/Fixtures/Variant/VariantQueryBuilder.php` no longer satisfy it. Every test that loads them fatals, and PHPStan fails, while 007 and 008 run in parallel. Fix: 006 adds the methods to both driver builders and the Variant fixture: the lock setters store state, and `upsert()` throws a temporary `LogicException`. 007 and 008 replace these with real compilation. 006 must also list every double to update (about 20 anonymous classes across `packages/database/tests/{Query,Entity,Repository}`).
2. **No shared upsert/lock contract for parallel workers (006/007/008/009).** The plan never gives a signature, return type, `null` vs `[]` semantics for `$update`, or the order of lock clauses. Fix: the contract is now in `_plan.md` Architecture Notes.

## Important (Should fix before building)

3. **`ReadWriteConnection::reset()` with nesting (004).** It calls `write->rollback()` once. With savepoints that rolls back only the innermost level, which leaves the outer transaction open in a worker. It also runs after-rollback callbacks, which contradicts "reset() clears state without running callbacks". Fix: delegate to `write->reset()` when the write connection is `ResettableInterface`, and otherwise roll back until the level is 0.
4. **`disconnect()` leaves TransactionState stale (002/003).** `inTransaction()` now reads `transactionState->level()`. After `disconnect()` mid-transaction, the next `beginTransaction()` sends `SAVEPOINT` on a fresh PDO with no `BEGIN`, which is a server error. Fix: `disconnect()` clears the state.
5. **Lock edge cases unspecified (007/008):**
   - The lock clause must go after LIMIT/OFFSET.
   - `compileSubquery()` on a locked builder (the right side of a union) must throw.
   - Modifier checks must run when the SQL is compiled, not when the methods are called, so call order does not matter.
   - `skipLocked()` together with `noWait()` must throw.
   - `runAggregate()` does not go through `buildSelectSql()`, so an aggregate on a locked builder would silently drop the lock. It must throw.
6. **MySQL/MariaDB shared-lock modifiers (008).** MySQL 8 accepts modifiers only on `FOR SHARE`. MariaDB has no `FOR SHARE` (verify) and accepts `LOCK IN SHARE MODE [NOWAIT|SKIP LOCKED]`. `SKIP LOCKED` needs MariaDB 10.6+, but the plan says 10.5+. So `sharedLock()` with a modifier has no form that works on both. The plan emits the MySQL 8 form, and the gap must be documented.
7. **MySQL upsert ignores `uniqueBy` (008).** `ON DUPLICATE KEY` fires on any unique key, including the PK. `uniqueBy` only shapes the default update list. This must be documented and tested so nobody assumes PostgreSQL behaviour. An empty update list needs a defined no-op form (`first_unique = first_unique`, never `INSERT IGNORE`, which swallows other errors).
8. **PostgreSQL rejects duplicate conflict keys in one upsert batch (007/010).** `ON CONFLICT DO UPDATE command cannot affect row a second time`. `ON CONFLICT (cols)` also needs a matching unique index or constraint. Integration fixtures must define one, and the docs must mention both.
9. **Affected-row counts differ by driver (010).** MySQL returns 2 per updated row and 0 for an unchanged row. Integration tests must assert table contents, not the same return value on both drivers.
10. **Repository::upsert stale `updated_at` (009).** `applyInsertTimestamps()` fills only unset values, so an upsert of a loaded entity writes its old `updated_at`. It must refresh `updated_at`. Also, a batch that mixes null and set auto-increment PKs produces different column sets and must throw.
11. **Integration test helper collisions (010).** Pest loads every test file into one process. The existing `pgsqlIntegrationConfig()`/`pgsqlRowCount()` functions and the `PGSQL_SKIP_REASON` const must be reused or uniquely named, or the run fatals with "Cannot redeclare". MySQL DDL issues an implicit commit, so tables must be created outside the transactions under test.
12. **insertBatch tests (005).** "Keep every existing test green" is wrong when a test asserted the old skip-when-active behaviour, or when a double's `transaction()` does not route through begin/commit. Those tests must be updated.
13. **End-to-end lock wiring has no app-level test (011).** Nothing proves that `Repository::query()->lockForUpdate()` inside `TransactionInterface::transaction()` sees the same connection in the booted app. Added a test, so 011 now also depends on 006.

## Minor (Nice to address)

- `admin-auth` `RoleRepository` (line ~172) uses the same "begin only if not in transaction" pattern and could use `transaction()`.
- A failed `ROLLBACK TO SAVEPOINT` discards the level, but the server-side savepoint may remain. Consider `RELEASE` after `ROLLBACK TO`.
- PostgreSQL rejects `FOR UPDATE` with DISTINCT/GROUP BY/HAVING and on the nullable side of outer joins. The server error is loud enough, but it should be documented.
- Locks apply only to the main query, not to eager-loaded relations (`with()`). Document this.
- Upsert batches above about 65k bindings fail on PostgreSQL. Document this or chunk.
- `DatabaseTestHelper` wraps tests in a transaction, so after-commit callbacks never fire under it. Document this in task 012.

## Questions for the Team

- Should `afterRollback()` at level 0 throw, to follow the loud-errors principle, instead of silently dropping the callback?
- `sharedLock()->skipLocked()` on MariaDB: detect the server flavour from `PDO::ATTR_SERVER_VERSION`, or document it as unsupported?
- Should a throwing after-commit callback stop the remaining callbacks (current behaviour), or should all of them run and the first error be rethrown?
