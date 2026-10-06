# Task 002: Entity-ownership unit test; delete dead migrations

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Guard against tables that only live in raw SQL, and remove the never-run MySQL migrations and their SQL-string test.

## Context
- Related files: packages/admin-auth/src/Repository/*.php, packages/admin-auth/database/migrations/, packages/admin-auth/tests/Migration/MigrationTest.php

## Requirements (Test Descriptions)
- [x] `it owns every table named in the repositories raw SQL with an entity`
- [x] `it owns every identifier the repositories quote with an entity table or column`
- [x] `it ships no hand-written migrations`

## Acceptance Criteria
- database/migrations and tests/Migration removed
- All requirements have passing tests

## Implementation Notes
