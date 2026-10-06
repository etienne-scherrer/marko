# Task 007: Integration Tests on PostgreSQL and MySQL

**Status**: completed
**Depends on**: 002, 003, 004, 005, 006
**Retry count**: 0

## Description
Prove the behavior against real servers (integration-services group, skipped without the MARKO_TEST_* env vars).

## Context
- Related files: packages/database-pgsql/tests/Integration/, packages/database-mysql/tests/Integration/

## Requirements (Test Descriptions)
- [x] `it applies and rolls back a varchar to integer change on a populated table` (PostgreSQL)
- [x] `it creates a uuid primary key defaulting to gen_random_uuid() and diffs clean` (PostgreSQL)
- [x] `it stores a function-looking literal default as a string` (PostgreSQL)
- [x] `it widens an auto-increment integer key to bigint and keeps the sequence default` (PostgreSQL)
- [x] `it changes the type of a column whose default cannot be cast implicitly` (PostgreSQL, e.g. varchar default '0' to integer default 0)
- [x] `it creates expression defaults and diffs clean` (MySQL)

## Acceptance Criteria
- All requirements pass against postgres:17 and mysql:8.4
