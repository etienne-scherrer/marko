# Task 003: Shared connection (#159) and real env vars (#160)

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Convert the #159 and #160 todos. Remove the #160 fixture workaround: config files read `$_ENV` (the documented lookup) instead of `getenv()`.

## Context
- Related files: `tests/Integration/App/TransactionsTest.php`, new `tests/Integration/App/ConfigTest.php`, `Fixture/config/{cache-redis,database}.php`, new `Fixture/config/integration.php`

## Requirements (Test Descriptions)
- [x] `it rolls back writes made through two repositories when a transaction spanning both fails`
- [x] `it resolves TransactionInterface to the same connection the repositories use`
- [x] `it honours a config value supplied only as a real environment variable`

## Acceptance Criteria
- Tests pass against real services; fixture configs no longer carry the "fixture does not install marko/env" workaround

## Implementation Notes
- `EnvLoader` mirrors with `$_ENV[$name] ??= $value` (never overwrites), and `$_ENV` persists for the whole test process. ConfigTest must:
  - use a variable name unique to this test;
  - `unset($_ENV[$name])` before boot, which is how it simulates `variables_order` without E (that cannot be changed at runtime);
  - `putenv()` **before** booting, so do not use the boot-first `setUpIntegrationTest()`; build the project and call `bootIntegrationApp()` in the test;
  - clear both `$_ENV[$name]` and `putenv($name)` in a `finally`.
- In CI the `DB_*`/`REDIS_*` values equal the config defaults, so the `database.php`/`cache-redis.php` conversion is only proven by ConfigTest. Make the `integration.php` default differ from the env value.
- `database.php` passes `$_ENV` (not `getenv()`) to `integrationDatabaseName()`.
- Leave `DatabaseTestingFixture/config/database.php` on `getenv()` unless its boot path runs `EnvLoader`.
