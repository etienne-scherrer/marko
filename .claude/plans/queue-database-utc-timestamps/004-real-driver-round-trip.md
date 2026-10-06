# Task 004: Real-driver round trip with a non-UTC PHP default

**Status**: completed
**Depends on**: 002, 003
**Retry count**: 0

## Description
Extend packages/queue-database/tests/Integration with a PostgreSQL round trip (and MySQL when a server is configured) where the PHP default timezone is America/New_York: queue timestamps land in UTC, a delayed job is not popped early and is popped once due, and a failed job's failedAt equals the stored instant.

## Context
- Related files: packages/queue-database/tests/Integration/PgSqlRoundTripTest.php, packages/database-mysql/tests/Fixtures/IntegrationDatabase.php
- Skip (with reason) when the service is not configured, per existing conventions.
- MySQL: add `marko/database-mysql` to packages/queue-database/composer.json `require-dev`. Get the config from `IntegrationDatabase::config()` (it reads `MARKO_TEST_MYSQL_*`, returns null to skip, and throws under `MARKO_INTEGRATION_REQUIRED`); do not reuse `DB_HOST`. Tag it with the `integration-services` group like the PostgreSQL tests.
- Build queues with `DatabaseTimezoneConfig::fromName('UTC')`; always restore `date_default_timezone_set()` in a `finally`/afterEach.

## Requirements (Test Descriptions)
- [x] `it stores queue timestamps in UTC with a non-UTC PHP default timezone on PostgreSQL`
- [x] `it pops a delayed job on time with a non-UTC PHP default timezone on PostgreSQL`
- [x] `it reads failed_at back as the stored instant on PostgreSQL`
- [x] `it pops a delayed job on time with a non-UTC PHP default timezone on MySQL`

## Acceptance Criteria
- Tests pass against a real server, skip cleanly without one

## Implementation Notes
PostgreSQL cases added to PgSqlRoundTripTest (DB_* variables); new MySqlRoundTripTest reads MARKO_TEST_MYSQL_* and fails under MARKO_INTEGRATION_REQUIRED. marko/database-mysql added to require-dev. Verified against postgres:17 and mysql:8.4 containers.
