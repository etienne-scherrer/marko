# Task 005: MySQL/MariaDB integration tests

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Real-server tests (integration-services) on MySQL 8.4 and MariaDB 11.8 with the server default zone set to a non-UTC zone: a `TIMESTAMP` column and a `DEFAULT CURRENT_TIMESTAMP` column read back in `database.timezone`, for UTC and a named zone, plus the reconnect case.

## Context
- Related files: packages/database-mysql/tests/Integration/, packages/database-mysql/tests/Fixtures/IntegrationDatabase.php
- Set `SET GLOBAL time_zone` in the test and restore it in `finally`.

## Requirements (Test Descriptions)
- [x] `it runs the session in database.timezone although the server zone is not UTC`
- [x] `it stores a TIMESTAMP value in the DST gap of the server zone unchanged`
- [x] `it fills DEFAULT CURRENT_TIMESTAMP with the current time in database.timezone`
- [x] `it pins a named database timezone on the session`
- [x] `it pins the session zone again after a reconnect`

## Acceptance Criteria
- Pass against MySQL 8.4 and MariaDB 11.8

## Implementation Notes
(Left blank - filled in by programmer during implementation)
