# Devil's Advocate Review: use-builder-locks-and-savepoints

## Critical (Must fix before building)
None.

## Important (Should fix before building)
1. **Task 002: about 9 mock-based pop tests will throw LockException.** `DatabaseQueueTest` builds pop tests on `$this->createMock(ConnectionInterface::class)` (lines ~117, 166, 475, 530, 559, 585, 708, 762, 800). That mock is not a `TransactionInterface`, so `PgSqlQueryBuilder::assertLockInTransaction()` throws on `first()`. They also assert on named bindings (`:queue`, `:reclaim_cutoff`) and unquoted SQL, but the builder emits positional `?` and `"jobs"`. "Pass unchanged in behaviour" is not achievable without rewriting them. Fix: say explicitly that these tests move to `SqliteConnection` (or a dual-interface stub with `inTransaction()` true), and that SELECT-shape assertions get rewritten against the builder output.
2. **Task 002: ModuleTest's container has no `QueryBuilderFactoryInterface`.** The `queueDatabaseModuleContainer()` helper binds only `ConnectionInterface` and config. Once module.php resolves the factory, every module test fails. Fix: the helper must bind `QueryBuilderFactoryInterface` to `PgSqlQueryBuilderFactory` over the same SQLite connection. Also check that `tests/Integration/App/BootTest.php` still resolves `QueueInterface`.
3. **Task 002: the constructor contract is unspecified.** Tasks 002 and 003 both construct `DatabaseQueue`. Fix: pin `QueryBuilderFactoryInterface $queryBuilderFactory` as a required parameter immediately after `failedJobRepository`, before the defaulted ones.
4. **Task 003: `pgsqlQueueConnection()` drops and recreates the tables.** Calling it for worker B wipes worker A's data mid-test. Worker A's lock also needs care: `pop()` commits its own transaction, so A must `beginTransaction()` first (the pop nests as a savepoint) and roll back in `finally`. Fix: add a connection-only helper, give each worker its own `PgSqlQueryBuilderFactory`, and set `lock_timeout` on B so a regression fails fast instead of hanging CI.

## Minor (Nice to address)
- Task 001: the five listed tests (and `createRoleSavepointConnection`) already exist in RoleRepositoryTest. The worker should reuse them, not duplicate them.
- Task 002: the SQLite fixture should strip only a trailing `FOR UPDATE|FOR SHARE [SKIP LOCKED|NOWAIT]` clause (regex anchored at end).

## Questions for the Team
- None.
