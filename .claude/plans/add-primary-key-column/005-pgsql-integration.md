# Task 005: PostgreSQL Real-Database Tests

**Status**: completed
**Depends on**: 003
**Retry count**: 0

## Description
Prove against PostgreSQL 17 that adding a key column to a table with rows creates the key constraint in the same statement, settles and rolls back.

## Context
- Related files: packages/database-pgsql/tests/Integration/SchemaDiffSettlesTest.php

## Requirements (Test Descriptions)
- [x] `it adds a serial primary key column to a table with rows and the diff is then empty`
- [x] `it adds a uuid primary key column with a gen_random_uuid() default to a table with rows and the diff is then empty`
- [x] `it adds a non-auto-increment primary key column without a default to an empty table and the diff is then empty`
- [x] `it fails loudly adding a primary key column without a default to a table with rows and leaves the table unchanged` (NULLs in the key column)
- [x] `it refuses to add a primary key column to a table that already has a primary key`
- [x] `it drops the added primary key column in down and the table matches the original`

## Acceptance Criteria
- All requirements pass on PostgreSQL 17

## Implementation Notes
(Left blank - filled in by programmer during implementation)
