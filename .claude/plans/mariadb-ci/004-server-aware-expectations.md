# Task 004: Server-aware expectations in existing integration tests

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Make the five tests that fail on MariaDB state the server difference explicitly instead of assuming MySQL.

## Context
- ConcurrencyErrorsTest / TransactionPrimitivesTest noWait: MariaDB reports NOWAIT as 1205 "Lock wait timeout exceeded" (still LockTimeoutException)
- TransactionPrimitivesTest sharedLock()->noWait(): MariaDB rejects FOR SHARE (documented limitation) → QueryException
- ModifyColumnMigrationTest: MariaDB keeps display width (`int(10) unsigned`)
- ExpressionDefaultsTest: json diff fixed by task 002

- Server detection: use `IntegrationDatabase::isMariaDb($connection)` (task 001) only. Each server-specific test skips on the other server with a reason naming the difference (e.g. "MariaDB rejects FOR SHARE; see the shared-lock follow-up"). Message assertions branch on the server: MySQL `NOWAIT is set`, MariaDB `Lock wait timeout exceeded`. Both still assert `LockTimeoutException`.

## Requirements (Test Descriptions)
- [ ] `it raises LockTimeoutException when noWait meets a locked row` passes on both servers
- [ ] `it makes a second connection fail fast with noWait on a locked row` passes on both servers
- [ ] `it lets a second connection share-lock a row that is share-locked` passes on MySQL; `it rejects a shared lock with a modifier on MariaDB` passes on MariaDB
- [ ] `it keeps the length, default and native definition when only nullability changes` passes on both servers
- [ ] `it creates expression defaults and diffs clean` passes on both servers

## Acceptance Criteria
- Whole `packages/database-mysql/tests/Integration` suite green on MySQL 8.4 and MariaDB 11.8

## Implementation Notes
ModifyColumnMigrationTest also needed a server-aware default: MariaDB has no DEFAULT_GENERATED, so CURRENT_TIMESTAMP is read as a plain string, not an Expression. The shared-lock modifier on MariaDB (compile LOCK IN SHARE MODE NOWAIT/SKIP LOCKED) is left for a follow-up; the test pins the documented limitation.
