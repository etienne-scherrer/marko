# Task 001: Server detection in the integration fixture

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Give `IntegrationDatabase` a way for tests to ask whether the server is MariaDB, and let CI declare which server each run must hit (`MARKO_TEST_MYSQL_SERVER=mysql|mariadb`) so a mispointed port fails instead of silently skipping MariaDB-only tests.

## Context
- Related files: packages/database-mysql/tests/Fixtures/IntegrationDatabase.php, packages/database-mysql/tests/Unit/IntegrationDatabaseTest.php (unit tests for config()), new packages/database-mysql/tests/Integration/ServerIdentityTest.php
- Patterns to follow: `IntegrationDatabase::config(?array $env)` injectable env

## Interface Contract (tasks 003, 004 build against this)
On `Marko\Database\MySql\Tests\Fixtures\IntegrationDatabase`:
- `public static function isMariaDbVersion(string $version): bool` (pure: version string contains `mariadb`, case-insensitive)
- `public static function isMariaDb(ConnectionInterface $connection): bool` (runs `SELECT VERSION() AS version`, delegates to `isMariaDbVersion`)
- `public static function assertServer(string $version, ?array $env = null): void`: reads `MARKO_TEST_MYSQL_SERVER` (`$env ??= getenv()`). Unset or empty means no check (local runs). `mysql`/`mariadb` (case-insensitive) must match the version or it throws `RuntimeException` naming both the expected and the connected server. Any other value throws `RuntimeException` listing the allowed values.

## Wiring (required, or the check never runs)
Add `packages/database-mysql/tests/Integration/ServerIdentityTest.php` (`pest()->group('integration-services')`, same skip/required behaviour as its siblings via `IntegrationDatabase::config()`). It connects and calls `assertServer()` with the real `VERSION()`, so the CI MySQL run and the MariaDB run each fail on a mispointed port instead of passing by skipping MariaDB-only tests.

## Requirements (Test Descriptions)
- [ ] `it reports MariaDB when the server version names MariaDB`
- [ ] `it reports MySQL when the server version does not name MariaDB`
- [ ] `it fails when MARKO_TEST_MYSQL_SERVER names a different server than the one connected`
- [ ] `it rejects an unknown MARKO_TEST_MYSQL_SERVER value`
- [ ] `it does not check the server when MARKO_TEST_MYSQL_SERVER is unset`
- [ ] `it connects to the server MARKO_TEST_MYSQL_SERVER names` (integration, ServerIdentityTest)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
