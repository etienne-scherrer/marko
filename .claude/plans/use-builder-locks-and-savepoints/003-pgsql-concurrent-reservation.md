# Task 003: PostgreSQL concurrent reservation integration test

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Prove on a real PostgreSQL server that two workers on separate connections never reserve the same job: worker A holds its reservation's row lock inside an open transaction while worker B pops.

## Context
- Related files: packages/queue-database/tests/Integration/PgSqlRoundTripTest.php (group integration-services, skipped without DB_HOST)

## Requirements (Test Descriptions)
- [x] `it never hands the same job to two concurrent reservations on PostgreSQL`
- [x] `it skips a locked job instead of waiting for it on PostgreSQL`

## Acceptance Criteria
- Tests pass against postgres:17 in CI

## Implementation Notes
- Do NOT call `pgsqlQueueConnection()` for worker B. It drops and recreates `jobs`/`failed_jobs` and would wipe A's data. Extract a connection-only helper, and let only the first connection migrate.
- Each worker needs its own `PgSqlConnection` and its own `PgSqlQueryBuilderFactory($thatConnection)`. Use the constructor signature from task 002.
- `pop()` commits its own transaction. To hold A's lock, call `$connectionA->beginTransaction()` first, so A's pop nests as a savepoint and the row lock stays held. Roll back A in a `finally`.
- Before B pops, run `SET lock_timeout = '2s'` on connection B. A regression to a blocking lock then fails fast instead of hanging CI.
- Push two jobs. A gets job 1, B must get job 2 (not null, not job 1).
