# Task 006: Integration tests on MySQL and PostgreSQL

**Status**: completed
**Depends on**: 001, 002, 004, 005, 008
**Retry count**: 0

## Description
integration-services tests that create tables through the generator, change uniqueness, migrate up and down, and assert
the diff is then empty; MySQL also asserts typed defaults diff empty right after creation.

## Context
- Related files: packages/database-mysql/tests/Integration/, packages/database-pgsql/tests/Integration/

## Requirements (Test Descriptions)
- [x] `it diffs a table created with a unique column as empty`
- [x] `it adds the unique index when a column becomes unique and the diff is then empty`
- [x] `it drops the unique index when a column stops being unique and the diff is then empty`
- [x] `it restores uniqueness in down`
- [x] `it diffs integer, boolean, decimal, varchar and timestamp defaults as empty after creation` (MySQL)
- [x] `it drops uniqueness from a foreign key column, migrates down, and the diff is then empty` (both drivers)

## Acceptance Criteria
- Tests pass against real servers, skip without them

## Implementation Notes
- Follow the setup/skip pattern of the existing `ModifyColumnMigrationTest.php` in each package's `tests/Integration/`.
- Build the entity side through `SchemaBuilder` (so task 002's canonicalization is exercised), not hand-built `Column`s.
