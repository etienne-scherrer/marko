# Task 002: MySqlServer cached detection service

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
A service that reads the server version through `ConnectionInterface` with `SELECT VERSION()` the first time it is asked and caches it, exposing `version()` and `isMariaDb()`. Registered as a container singleton so the whole app shares one answer per connection.

## Context
- Related files: packages/database-mysql/src/Connection/MySqlServer.php (new), packages/database-mysql/module.php, packages/database-mysql/tests/Module/ModuleBindingsTest.php
- Works through ReadWriteConnection because it only uses ConnectionInterface::query().

## Requirements (Test Descriptions)
- [x] `it reports MariaDB for a MariaDB server`
- [x] `it reports MySQL for a MySQL server`
- [x] `it queries the server version only once`
- [x] `it does not query the server until asked`
- [x] `it detects the server through a connection decorator`
- [x] `it registers MySqlServer as a singleton`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- Constructor takes ONLY `ConnectionInterface $connection`. The container autowires every class-typed parameter, including nullable ones with `= null` defaults (`Container::resolve()` never checks the default for class types). A `?MySqlServerVersion $version = null` parameter would make it try to build `MySqlServerVersion` and throw `BindingException`. Keep the cached version in a private non-readonly property (`private ?MySqlServerVersion $version = null`), not a promoted one.
- Use exactly `SELECT VERSION() AS version` and read `$rows[0]['version']`, so existing test mocks keyed on `VERSION()` keep matching.
- An empty or missing result throws `ServerVersionException::unreadable()` through `MySqlServerVersion::fromString()`. Do not swallow it.
- Register it list-style in module.php: `'singletons' => [ConnectionInterface::class, MySqlServer::class]`. It is autowired with the shared `ConnectionInterface`, which is the `ReadWriteConnection` when database-readwrite is installed.
- Expose `version(): MySqlServerVersion` and `isMariaDb(): bool`.
