# Task 002: DatabaseQueue reserves through the query builder lock

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Build the reservation SELECT with the query builder (`lockForUpdate()->skipLocked()->first()`), inject `QueryBuilderFactoryInterface`, and delete `supportsSkipLocked()`. Keep the guarded UPDATE as the second line of defence.

## Context
- Related files: packages/queue-database/src/DatabaseQueue.php, packages/queue-database/module.php, packages/queue-database/tests/* (DatabaseQueueTest, DatabaseQueueAttemptsTest, Feature/DatabaseQueueWorkerTest, ModuleTest, Fixtures/SqliteConnection, Integration/PgSqlRoundTripTest)
- Tests build the queue with `PgSqlQueryBuilderFactory` (already a dev dependency); the SQLite fixture drops and records row-lock clauses

## Requirements (Test Descriptions)
- [x] `it selects the next job with FOR UPDATE SKIP LOCKED`
- [x] `it fails loudly when popping on a connection without transactions`
- [x] `it does not reclaim a job whose reservation is within the retry_after window`
- [x] `it reclaims a job whose reservation is older than queue.retry_after`
- [x] `it binds the query builder factory in the module`
- [x] Existing pop/attempts/worker tests pass unchanged in behaviour

## Acceptance Criteria
- No `FOR UPDATE`/`SKIP LOCKED` string left in queue-database/src
- Public QueueInterface behaviour unchanged

## Implementation Notes
- Constructor contract (task 003 depends on it): add `QueryBuilderFactoryInterface $queryBuilderFactory` as a REQUIRED parameter immediately after `failedJobRepository` (before the defaulted params). All callers use named args.
- Every `DatabaseQueueTest` pop test built on `$this->createMock(ConnectionInterface::class)` (~lines 117, 166, 475, 530, 559, 585, 708, 762, 800) will throw `LockException`, because the builder asserts `TransactionInterface` + `inTransaction()`. Those tests also assert named bindings (`:queue`, `:reclaim_cutoff`) and unquoted SQL, but the builder emits positional `?` and `"jobs"`. Migrate them to `SqliteConnection` (preferred) or to a dual-interface stub with `inTransaction()` true. Rewrite any SELECT-shape assertions against the builder output. Keep the behaviour each test asserts.
- Update `createTestQueue()` (DatabaseQueueTest), the AttemptsTest/worker-test helpers and `pgsqlQueue()` (PgSqlRoundTripTest) to pass `new PgSqlQueryBuilderFactory($connection)`.
- ModuleTest: `queueDatabaseModuleContainer()` must bind `QueryBuilderFactoryInterface` to `new PgSqlQueryBuilderFactory($connection)` (same SQLite connection), or every module test fails to resolve. Confirm `tests/Integration/App/BootTest.php` still resolves `QueueInterface`.
- SqliteConnection fixture: strip only a trailing lock clause (`/\s+FOR (UPDATE|SHARE)(\s+(SKIP LOCKED|NOWAIT))?\s*$/`) and record it for assertions.
