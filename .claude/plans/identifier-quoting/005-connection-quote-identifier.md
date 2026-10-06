# Task 005: ConnectionInterface::quoteIdentifier() in drivers and all stubs

**Status**: completed
**Depends on**: 001, 002, 003, 004
**Retry count**: 0

## Description
Add `quoteIdentifier(string $identifier): string` to `ConnectionInterface` (breaking for third-party drivers). `MySqlConnection` and `PgSqlConnection` delegate to their identifier class without needing a live connection, `ReadWriteConnection` delegates to its write connection, and every test stub implements it (ANSI double quotes, which SQLite-backed stubs also accept).

## Context
- Related files: `packages/database/src/Connection/ConnectionInterface.php`, the three driver connections, ~60 test files with stubs (same set as `supportsReturning()` in #321)

## Requirements (Test Descriptions)
- [x] `it quotes identifiers with backticks without connecting` (MySqlConnection)
- [x] `it quotes identifiers with double quotes without connecting` (PgSqlConnection)
- [x] `it delegates quoteIdentifier to the write connection` (ReadWriteConnection)
- [x] `it declares quoteIdentifier on ConnectionInterface`

## Acceptance Criteria
- All requirements have passing tests; every ConnectionInterface implementation compiles

## Implementation Notes
- Depends on 003/004 because this task edits stub files inside `packages/database-mysql/tests` and `packages/database-pgsql/tests` (`ProbeRecordingConnection`, `MySqlIntrospectorTest`, `Query/*Connection`, `SharedConnectionTest`) that those tasks work next to.
- Hand-written stubs (anonymous classes and fixture classes, including `packages/testing/tests/fixtures/database-app/app/fakedb/src/RecordingConnection.php`) get `quoteIdentifier()`. PHPUnit `createMock()`/`createStub()` connections need nothing here (nothing calls the method yet); tasks 006/007 configure them.
- Stub rule: `'"' . str_replace('"', '""', $part) . '"'` per `.`-separated part, matching the drivers.
- `pubsub-pgsql` stubs with a `quoteIdentifier(string $name)` method implement a different interface; leave them alone.
