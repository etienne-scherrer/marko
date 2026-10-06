# Plan: MariaDB Shared-Lock Modifiers

## Created
2026-10-06

## Status
completed

## Objective
Detect MariaDB once per connection in `marko/database-mysql` and use that answer to compile `sharedLock()->noWait()` / `->skipLocked()` as `LOCK IN SHARE MODE NOWAIT|SKIP LOCKED` on MariaDB, while MySQL keeps `FOR SHARE NOWAIT|SKIP LOCKED`.

## Related Issues
Closes #324

## Discovery Notes
- `MySqlQueryBuilder::buildLockClause()` compiles `FOR SHARE` + modifier for both servers; MariaDB rejects it.
- `MySqlIntrospector::isMariaDb()` is private and runs `SELECT VERSION()` on every call.
- The query builder only holds a `ConnectionInterface`, which may be a `ReadWriteConnection` (`marko/database-readwrite` re-registers `ConnectionInterface` as an instance), so detection must go through `ConnectionInterface`, not PDO.
- `MySqlQueryBuilder` is bound directly to `QueryBuilderInterface` (autowired) and also built by `MySqlQueryBuilderFactory`; many tests build it with `new MySqlQueryBuilder($connection)`.
- The container autowires a nullable class-typed parameter (it resolves the class), so an optional `?MySqlServer` constructor parameter still receives the shared singleton in the app.
- #325 (MariaDB RETURNING) will reuse the detection: `MySqlConnection::supportsReturning()` can build `new MySqlServer($this)` or receive the version; `MySqlServerVersion::isAtLeast()` covers the 10.5+ check.
- Decision issue: implements the issue's recommendation (detect from the server once per connection, cached, shared), not the `database.server` config alternative.

## Scope

### In Scope
- `MySqlServerVersion` value object parsing the version string (`8.4.3`, `11.8.7-MariaDB-ubu2404`, `5.5.5-10.11.8-MariaDB-...`) with `isMariaDb`, normalized `version`, `isAtLeast()`.
- `MySqlServer` service: runs `SELECT VERSION()` once through `ConnectionInterface`, caches the parsed version; loud exception on an empty/unparseable version. Registered as a singleton.
- `MySqlQueryBuilder` / `MySqlQueryBuilderFactory` accept the shared `MySqlServer`; detection is only consulted for a shared lock with a modifier.
- `MySqlIntrospector` uses `MySqlServer` instead of its private `isMariaDb()`.
- Unit tests for both servers; integration tests in `TransactionPrimitivesTest` that run on both servers with no MariaDB skip.
- Docs: `database-mysql.md`, `database.md` lock table.
- Optional: MariaDB 10.11 LTS CI step and raised documented floor, if the suite passes and runtime stays small.

### Out of Scope
- MariaDB `RETURNING` (#325).
- A `database.server` config override.

## Success Criteria
- [x] One cached MariaDB check used by the query builder and the introspector, working through `ReadWriteConnection`
- [x] Shared lock modifiers compile per server; nothing else changes on MySQL
- [x] Unit and integration tests pass on MySQL 8.4 and MariaDB 11.8
- [x] Docs updated
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | MySqlServerVersion value object | - | completed |
| 002 | MySqlServer cached detection service + singleton | 001 | completed |
| 003 | Query builder compiles shared-lock modifiers per server | 002 | completed |
| 004 | Introspector uses MySqlServer | 002 | completed |
| 005 | Integration tests on both servers | 003, 004 | completed |
| 006 | Docs | 003, 004 | completed |
| 007 | Optional MariaDB 10.11 CI step | 005 | completed |

## Architecture Notes
- `MySqlServer` lives in `Marko\Database\MySql\Connection` next to the connection; it depends only on `ConnectionInterface`, so it works through any decorator.
- Cache lifetime = the `MySqlServer` instance; it is a container singleton bound to the shared connection, so one `SELECT VERSION()` per process at most, and only when something asks.
- Replicas run the same server type as the primary, so reading the version from a replica through `ReadWriteConnection` is correct.

## Risks & Mitigations
- Existing unit tests' mock connections return `[]` for `SELECT VERSION()`, which now throws `ServerVersionException`. Tasks 003 and 004 must update those fixtures (LockingUpsert modifier tests, `createMockConnection()` defaulting `VERSION()` to `8.4.3`).
- The container autowires nullable class-typed parameters even when they default to `null`. `MySqlServer`'s constructor takes only `ConnectionInterface`. The builder, factory and introspector take an optional trailing `?MySqlServer` and fall back lazily to `new MySqlServer($connection)`.
- Parallel PRs (#304, #323) touch `MySqlConnection`/`ConnectionInterface`: this plan does not modify either; rebase before the PR.
