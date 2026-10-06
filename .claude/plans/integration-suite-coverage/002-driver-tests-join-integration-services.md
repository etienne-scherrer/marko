# Task 002: Driver integration tests and RedisLiveTest join integration-services

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Move the pgsql and MySQL driver integration tests from group `integration` to `integration-services`, and give `RedisLiveTest` the same group. Every one of these suites must fail instead of skip when `MARKO_INTEGRATION_REQUIRED` is set and its service is not configured.

## Context
- Related files: `packages/database-pgsql/tests/Integration/*`, `packages/database-mysql/tests/Integration/*`, `packages/broadcasting-amphp/tests/Feature/RedisLiveTest.php`
- Patterns to follow: `packages/pubsub-redis/tests/Integration/SharedConnectionLiveTest.php`
- Package tests ship with their split repos, so the env handling lives in each package's `tests/Fixtures`, not in the monorepo `tests/Integration/App/Helpers.php`.

## Requirements (Test Descriptions)
- [x] `it reads the pgsql driver test connection from MARKO_TEST_PGSQL_* variables`
- [x] `it returns no pgsql config when MARKO_TEST_PGSQL_HOST is unset`
- [x] `it throws instead of skipping when MARKO_INTEGRATION_REQUIRED is set and MARKO_TEST_PGSQL_HOST is unset`
- [x] the same three for MySQL
- [x] `it throws instead of skipping the live redis broadcast test when MARKO_INTEGRATION_REQUIRED is set and redis is unusable`
- [x] every driver integration file and RedisLiveTest is in the integration-services group

## Acceptance Criteria
- All driver suites pass against real services; none skip in the Integration job

## Implementation Notes
- `Fixtures/IntegrationDatabase::config()` (pgsql and mysql) is the single env reader. Every `tests/Integration/*` file must use it, and the duplicated per-file `pgsql*Config()`/`mysql*Config()` functions and `*_SKIP_REASON` constants must be deleted.
- Replace `->skip(fn (): bool => ...Config() === null, ...)` with a `beforeEach` (or in-body check) that calls `IntegrationDatabase::config()` and `markTestSkipped()` on null. A throw inside a skip closure is not a reliable test failure; a throw from `beforeEach` is.
- `Fixtures/Concurrency/deadlock-contender.php` keeps reading `getenv()` (the subprocess inherits the environment).
- RedisLiveTest: copy the `MARKO_INTEGRATION_REQUIRED` throw from `SharedConnectionLiveTest::sharedConnectionSkipReason()`. Keep the function name unique, because all package test files load into one process.
