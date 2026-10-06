# Task 002: ConnectionInterface::supportsReturning() and implementations

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `supportsReturning(): bool` to `ConnectionInterface` so the repository can ask whether `INSERT ... RETURNING` is available instead of branching on `driverName()`. Implement it in the three drivers and every test stub.

## Context
- Related files: `packages/database/src/Connection/ConnectionInterface.php`, `packages/database-pgsql/src/Connection/PgSqlConnection.php` (true), `packages/database-mysql/src/Connection/MySqlConnection.php` (false; MariaDB 10.5+ supports RETURNING but stays on the MySQL behaviour until #297), `packages/database-readwrite/src/Connection/ReadWriteConnection.php` (delegates to `$this->write`)
- Every class implementing `ConnectionInterface` in tests (grep `implements .*ConnectionInterface` under `packages/`) and `packages/testing/tests/fixtures/database-app/app/fakedb/src/RecordingConnection.php` needs the method (false, except stubs reporting driverName 'pgsql', which return true).
- Like `driverName()`, it must not require a live connection.
- ALSO grep the repo-root `tests/` directory: `tests/Integration/QueryBuilderRawConsistencyTest.php` has an anonymous `ConnectionInterface` class (`makeRecordingConnection()`, driverName at ~line 83) that must get the method or the whole suite fatals.
- Some of this may already exist in the worktree (interface method, driver implementations, many stubs): check before re-adding.
- `ReadWriteConnection::query()` routes INSERT/UPDATE/DELETE to the write connection but does NOT set `$stickyWrite` (only `execute()` does). Task 004 moves the generated-key insert from `execute()` to `query("... RETURNING ...")`, which would lose read-your-writes (a following `find($id)` could hit a lagging replica). Set `$this->stickyWrite = true` in `query()` when `isWriteStatement($sql)` is true.

## Requirements (Test Descriptions)
- [x] `it reports that PostgreSQL connections support RETURNING`
- [x] `it reports that MySQL connections do not support RETURNING`
- [x] `it delegates supportsReturning to the write connection`
- [x] `it declares supportsReturning on ConnectionInterface`
- [x] `it makes the connection sticky to the write connection after a write statement runs through query`

## Acceptance Criteria
- All requirements have passing tests
- Full test suite still passes with all stubs updated

## Implementation Notes
