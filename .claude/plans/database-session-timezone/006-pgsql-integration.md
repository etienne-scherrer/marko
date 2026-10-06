# Task 006: PostgreSQL integration tests

**Status**: completed
**Depends on**: 003
**Retry count**: 0

## Description
Real-server tests on PostgreSQL 17 with the database default zone set to a non-UTC zone: `NOW()`/`DEFAULT CURRENT_TIMESTAMP` on a `TIMESTAMP` column reads back in `database.timezone`; a named zone and a fixed offset are pinned with the right sign.

## Context
- Related files: packages/database-pgsql/tests/Integration/, packages/database-pgsql/tests/Fixtures/IntegrationDatabase.php
- Use `ALTER DATABASE ... SET timezone` and reset it in `finally`.

## Requirements (Test Descriptions)
- [x] `it runs the session in database.timezone although the database default zone is not UTC`
- [x] `it fills DEFAULT CURRENT_TIMESTAMP with the current time in database.timezone`
- [x] `it pins a named database timezone on the session`
- [x] `it pins a fixed offset with the sign PHP uses`

## Acceptance Criteria
- Pass against PostgreSQL 17

## Implementation Notes
(Left blank - filled in by programmer during implementation)
