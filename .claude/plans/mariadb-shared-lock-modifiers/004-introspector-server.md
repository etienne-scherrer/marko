# Task 004: Introspector uses MySqlServer

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Replace `MySqlIntrospector`'s private `isMariaDb()` (one `SELECT VERSION()` per call) with the shared `MySqlServer`; module.php passes the singleton.

## Context
- Related files: packages/database-mysql/src/Introspection/MySqlIntrospector.php, packages/database-mysql/module.php, tests/Introspection/*

## Requirements (Test Descriptions)
- [x] `it reads the server version once across several getColumns calls`
- [x] `it uses the MySqlServer it is given`
- [x] `it binds IntrospectorInterface with the shared MySqlServer`

## Acceptance Criteria
- Existing introspector tests keep passing
- Code follows code standards

## Implementation Notes
- Signature: `__construct(ConnectionInterface $connection, string $database, ?MySqlServer $server = null)`, falling back lazily to `new MySqlServer($connection)`. Many tests call `new MySqlIntrospector($conn, 'testdb')`. module.php's `IntrospectorInterface` closure passes `$container->get(MySqlServer::class)`.
- Replace both `isMariaDb()` call sites (in `getColumns()` around line 167 and in the default matcher around line 243) and delete the private method.
- Breaking for fixtures: `createMockConnection()` in `MySqlIntrospectorTest.php` returns `[]` for unmatched SQL, and an empty version now throws `ServerVersionException` instead of meaning "MySQL". Make `createMockConnection()` answer `VERSION()` with `8.4.3` by default (overridable by an explicit `'VERSION()'` key), and check `MySqlExpressionDefaultMatcherTest.php` for the same.
- `createMockConnection()` is a `readonly` anonymous class and cannot count calls. Write a small counting connection for the "reads the server version once" test.
