# Task 003: Compile shared-lock modifiers per server

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
`MySqlQueryBuilder` takes an optional `MySqlServer` (the factory and the container pass the shared one); `buildLockClause()` compiles `LOCK IN SHARE MODE NOWAIT|SKIP LOCKED` on MariaDB and `FOR SHARE NOWAIT|SKIP LOCKED` on MySQL. Server detection is consulted only for a shared lock with a modifier.

## Context
- Related files: packages/database-mysql/src/Query/MySqlQueryBuilder.php, MySqlQueryBuilderFactory.php, tests/Query/MySqlQueryBuilderLockingUpsertTest.php, tests/Query/MySqlQueryBuilderFactoryTest.php

## Requirements (Test Descriptions)
- [x] `it compiles a shared lock with NOWAIT to LOCK IN SHARE MODE NOWAIT on MariaDB`
- [x] `it compiles a shared lock with SKIP LOCKED to LOCK IN SHARE MODE SKIP LOCKED on MariaDB`
- [x] `it keeps FOR SHARE with a modifier on MySQL`
- [x] `it keeps LOCK IN SHARE MODE and FOR UPDATE unchanged on MariaDB`
- [x] `it does not query the server version for queries without a shared lock modifier`
- [x] `it passes the shared MySqlServer to every query builder it creates`
- [x] `it detects the server from its own connection when no MySqlServer is given`

## Acceptance Criteria
- All requirements have passing tests
- No change to SQL MySQL receives

## Implementation Notes
- Signature: `__construct(private readonly ConnectionInterface $connection, ?MySqlServer $server = null)`. Store the server in a private non-readonly `?MySqlServer` property and resolve it lazily (`$this->server ??= new MySqlServer($this->connection)`) only inside the shared-lock-with-modifier branch of `buildLockClause()`. Many callers (other tests, queue-database integration tests) pass no server. The container autowires the parameter with the singleton.
- `MySqlQueryBuilderFactory`: add the same optional `?MySqlServer $server = null` and pass it to every builder it creates.
- Existing tests at `MySqlQueryBuilderLockingUpsertTest.php` lines 47-63 (`FOR SHARE SKIP LOCKED` / `FOR SHARE NOWAIT`) use a mock that returns `[]` for `SELECT VERSION()`, so they would now throw `ServerVersionException`. Update them to pass an explicit `MySqlServer` over a connection answering `VERSION()` with `8.4.3`, or teach the mock to answer `VERSION()`. Make sure `lastQuerySql` still captures the SELECT, not the version probe.
- Update the `buildLockClause()` docblock, which currently says FOR SHARE is used on both servers.
