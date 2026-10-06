# Task 003: TestDatabase: boot and migrate once per process

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
`Marko\Testing\Database\TestDatabase::boot($basePath, fresh: false)` boots the application once per process (Application::boot, as TestClient::boot does), refuses production, checks a driver is bound, and runs `Migrator::migrate()` once. `fresh: true` resets then migrates once per process. Offers application(), connection(), client(), seedTable(), getTableRowCount().

## Context
- Related files: packages/testing/src/Database/, packages/testing/src/Exceptions/DatabaseTestException.php
- DatabaseTestHelper keeps working; TestDatabase delegates seedTable/getTableRowCount to it.

## Requirements (Test Descriptions)
- [ ] `it throws a clear exception when marko/database is not installed`
- [ ] `it throws a clear exception when no database driver is installed`
- [ ] `it refuses to run in the production environment`
- [ ] `it reuses the booted application for the same base path`
- [ ] `it returns a TestClient that does not reset the connection`
- [ ] `it refuses production before booting the application or touching the database`
- [ ] `it checks the driver binding before resolving the connection`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Check order: (1) marko/database package guard, (2) production refusal via `AppEnvironment` (reads env vars, no boot needed), (3) `Application::boot`, (4) driver bound check with `container->has(ConnectionInterface::class)` before any `get()` (a missing binding would otherwise surface as a generic BindingException), (5) `Migrator::migrate()` once.
- The package guard must be testable in the monorepo where `ConnectionInterface` always exists: put the `interface_exists()` check behind a small static method that takes the interface name and throws `DatabaseTestException::databasePackageMissing()`; the test passes a non-existent interface name.
- Unset `APP_ENV` = production (#170). Tests in this task must set `APP_ENV=testing` explicitly (and restore it afterwards); the production test sets it to `production` / unsets it. Never depend on the developer's shell.
- `client()` lives on TestDatabase (not RefreshDatabase): `TestClient::forApplication($app)->withoutResetting($container->get(ConnectionInterface::class), $container->get(TransactionInterface::class))` (same instance in practice; exclusion is by identity). This also keeps `ReadWriteConnection` unreset, which matters: its `reset()` would roll back the write connection and clear sticky-write, sending later reads to replicas that cannot see the uncommitted test data.
