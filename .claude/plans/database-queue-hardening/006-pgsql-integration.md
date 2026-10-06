# Task 006: PostgreSQL integration round-trip test

**Status**: completed
**Depends on**: 001, 004
**Retry count**: 0

## Description
A PostgreSQL-backed test (skipped with a clear reason when `DB_HOST` etc. are unset/unreachable) proving a job with private and protected properties round-trips through push → pop and through failed_jobs → queue:retry.

## Context
- Related files: packages/queue-database/tests/Integration/PgSqlRoundTripTest.php
- Uses PgSqlConnection + real migrations; mirrors #187's env var names (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD)

## Requirements (Test Descriptions)
- [x] `it round-trips a job with private and protected properties through push and pop on PostgreSQL`
- [x] `it round-trips a failed job through failed_jobs and queue:retry on PostgreSQL`

## Acceptance Criteria
- Passes locally against postgres:17; skipped (not failed) without a server

## Implementation Notes
