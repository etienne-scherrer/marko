# Task 001: Server-aware supportsReturning() and shared MySqlServer binding

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`MySqlConnection` owns a lazily created `MySqlServer` built on itself and reports `supportsReturning()` from it: true on MariaDB 10.5+, false otherwise. The module binds `MySqlServer` to that same instance so the query builders, introspector and connection share one version check.

## Context
- Related files: packages/database-mysql/src/Connection/MySqlConnection.php, packages/database-mysql/src/Connection/MySqlServer.php (class docblock), packages/database-mysql/module.php, packages/database/src/Connection/ConnectionInterface.php (supportsReturning docblock), packages/database-mysql/tests/Connection/MySqlConnectionTest.php, packages/database-mysql/tests/Module/SharedConnectionTest.php
- Patterns to follow: `connectionReportingVersion()` in tests/Connection/Helpers.php (`createPdo()` override returning a `Pdo\Sqlite` handle with a `VERSION()` function)
- `ConnectionInterface::supportsReturning()` docblock currently says "Like driverName(), it must not require a live database connection." This change breaks that contract, so rewrite it: a driver may query the server once to answer (MySQL-family connections read the server version), so call it only when about to run SQL. Leave `driverName()` / `quoteIdentifier()` contracts untouched.
- Module binding: `MySqlServer::class` becomes a closure: resolve `ConnectionInterface`; return `$connection->server()` when it is a `MySqlConnection`, else `new MySqlServer($connection)` (ReadWriteConnection, a plugin proxy, a custom decorator). Keep `MySqlServer::class` in `singletons` (the container caches closure results for singleton ids).
- `server()` is public, created lazily with `new MySqlServer($this)`, and survives `disconnect()` and `reset()` (the server type does not change on reconnect). A failed version read (ConnectionException, QueryException, ServerVersionException) propagates from `supportsReturning()`; do not swallow it into `false`.
- Update `MySqlServer`'s class docblock: the instance is owned by the `MySqlConnection` and shared through the container.

## Requirements (Test Descriptions)
- [x] `it supports RETURNING on MariaDB 10.5 and later`
- [x] `it does not support RETURNING on MariaDB before 10.5`
- [x] `it does not support RETURNING on MySQL`
- [x] `it reads the server version once for supportsReturning and server`
- [x] `it does not connect until supportsReturning is asked`
- [x] `it shares the connection's own MySqlServer through the container`
- [x] `it builds a MySqlServer on the shared connection when it is not a MySqlConnection` (bind a non-MySqlConnection `ConnectionInterface` instance, then resolve `MySqlServer`)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Server detection reuses MySqlServer through MySqlConnection::server(); module binds MySqlServer to it (fallback new MySqlServer for decorators). ConnectionInterface and MySqlServer docblocks updated.
