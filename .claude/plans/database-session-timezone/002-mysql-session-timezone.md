# Task 002: MySQL connection pins the session time zone

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`MySqlConnection::connect()` passes `PDO\Mysql::ATTR_INIT_COMMAND` = `SET time_zone = '<offset or name>'`, so every new PDO (including after `disconnect()`) runs in `database.timezone`. Error 1298 becomes a `ConnectionException` naming the zone and the fix.

## Context
- Related files: packages/database-mysql/src/Connection/MySqlConnection.php, packages/database-mysql/src/Exceptions/ConnectionException.php, packages/database-mysql/tests/Connection/*
- SQLite-backed fakes that forward `$options` must drop the MySQL-only attributes. These are `new PDO('sqlite::memory:', options: $options)` in tests/Connection/MySqlConnectionTest.php (about 20 sites), tests/Query/MySqlQueryBuilderTest.php and tests/Query/MySqlQueryBuilderAggregatesTest.php. Use a shared helper that strips the MySQL attributes.
- Statement value: `$config->fixedTimezoneOffset() ?? $config->timezone` (see the task 001 contract). UTC is sent as `+00:00`, so the server needs no zone tables for it.
- Detect 1298 via `$e->errorInfo[1] ?? $e->getCode()` (both MySQL 8.4 and MariaDB 11.8 use 1298). Any other PDOException still maps to `connectionFailed`.
- Add `ConnectionException::unknownTimezone(string $timezone, PDOException $previous)`. The suggestion should cover loading the zone tables (`mysql_tzinfo_to_sql`) or using a UTC/offset zone.

## Requirements (Test Descriptions)
- [x] `it sets the session time zone to +00:00 on connect when the database timezone is UTC`
- [x] `it sets the session time zone to the named zone on connect`
- [x] `it sets the session time zone to the fixed offset of an offset or abbreviation timezone`
- [x] `it sets the session time zone again after a reconnect`
- [x] `it throws ConnectionException naming the zone when the server does not know the time zone`
- [x] `it still throws connectionFailed for other connect errors`

## Acceptance Criteria
- All requirements have passing tests
- Existing database-mysql tests still pass

## Implementation Notes
(Left blank - filled in by programmer during implementation)
