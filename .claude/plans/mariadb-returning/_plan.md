# Plan: MariaDB INSERT ... RETURNING

## Created
2026-10-06

## Status
completed

## Objective
Read database-generated keys back on MariaDB 10.5+ with `INSERT ... RETURNING`: `MySqlConnection::supportsReturning()` reports the real capability of the connected server (true on MariaDB 10.5+, false on MySQL) using the shared `MySqlServer` check from #324.

## Related Issues
Closes #325

## Discovery Notes
- `MySqlServer` (one cached `SELECT VERSION()` per instance) and `MySqlServerVersion::isAtLeast()` landed with #324; the container shares one `MySqlServer` singleton built on `ConnectionInterface`.
- `MySqlServer` needs a connection, so it cannot be injected into `MySqlConnection` (circular). The connection owns its `MySqlServer` instead (`server()`, created lazily with `$this`), and the module's `MySqlServer` binding resolves to that same instance when the shared connection is a `MySqlConnection`. A decorator such as `ReadWriteConnection` still gets `new MySqlServer($connection)`. One version check per connection.
- `Repository` already builds `"$sql RETURNING <quoted pk>"` for single-row `save()` (generated keys) and multi-row `insertBatch()` (auto-increment and generated keys). MariaDB accepts both forms and returns rows in insert order.
- `ReadWriteConnection::supportsReturning()` already delegates to the write connection.
- Unit tests can answer `SELECT VERSION()` without a server through a `Pdo\Sqlite` handle with a registered `VERSION()` function.

## Scope

### In Scope
- `MySqlConnection::server()` and server-aware `supportsReturning()`
- Module binding so the shared `MySqlServer` is the connection's own
- Integration tests on MariaDB (11.8 and 10.11 in CI) and MySQL 8.4 in `GeneratedPrimaryKeysTest.php`
- `RepositoryException::generatedKeyNotReadable()` wording
- `ConnectionInterface::supportsReturning()` docblock (no longer "must not require a live connection")
- Docs: `database.md`, `database-mysql.md`, `roadrunner-state-leaks.md`

### Out of Scope
- Using RETURNING for single-row auto-increment `save()` (it uses `lastInsertId()`, which is exact for one row)
- RETURNING for upserts

## Success Criteria
- [x] `supportsReturning()` true on MariaDB 10.5+, false on MySQL and MariaDB < 10.5, through the shared server check
- [x] Generated keys save and read back on MariaDB through `save()` and `insertBatch()`; auto-increment `insertBatch()` uses RETURNING on MariaDB
- [x] MySQL behaviour unchanged
- [x] All tests passing, `composer ci` green
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Server-aware supportsReturning() and shared MySqlServer binding | - | completed |
| 002 | Integration tests for RETURNING on MariaDB, MySQL unchanged | 001 | completed |
| 003 | RepositoryException wording and docs | 001 | completed |

## Architecture Notes
- No new detection: reuse `MySqlServer` / `MySqlServerVersion::isAtLeast('10.5')`.
- The version check needs a query, so `supportsReturning()` connects on first call (the repository is about to run SQL on it anyway). This breaks the documented `ConnectionInterface::supportsReturning()` contract ("must not require a live database connection"), so task 001 rewrites that docblock and task 003 the matching docs.
- `MySqlServer` binding is a closure: the connection's own `server()` for a `MySqlConnection`, `new MySqlServer($connection)` for anything else (ReadWriteConnection, plugin proxy).
- On MariaDB 10.5+ every auto-increment `insertBatch()` moves from `execute()` + `lastInsertId()` to `query(... RETURNING ...)`, so existing MariaDB-run integration tests that batch-insert exercise the new path (task 002 runs them).

## Risks & Mitigations
- Two `MySqlServer` instances for one connection: the module binding returns the connection's own instance.
- PDO returns RETURNING values as strings on MySQL: `Repository::assignPrimaryKey()` converts through the hydrator.
