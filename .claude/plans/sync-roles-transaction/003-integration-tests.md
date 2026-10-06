# Task 003: Real-database pivot tests for a failed syncRoles()

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Prove on real MySQL 8.4 / MariaDB and PostgreSQL that a failing `syncRoles()` keeps the user's previous roles.

## Context
- Related files: packages/admin-auth/tests/Integration/MySql/PivotRepositoryTest.php, packages/admin-auth/tests/Integration/PgSql/PivotRepositoryTest.php
- Patterns to follow: "rejects a duplicate role permission and keeps the earlier assignments"
- Both files already have `it('rejects a duplicate user role')`. That test seeds the user with **no** roles, so it passes with or without the fix. **Rewrite it in place**, renamed as below, instead of adding a second near-identical test.
- In every test, seed the user with a previous role through `AdminAuthSchema::userWith(..., [(int) $previous->id])`, so the "keeps previous roles" assertion can fail on the old code (DELETE committed, INSERT failed).
- Make identical changes to the MySql and PgSql files.

## Requirements (Test Descriptions)
- [x] `it rejects an unknown role id and keeps the user's previous roles` (expects `Marko\Database\Exceptions\ForeignKeyConstraintViolationException`; use an id such as `PHP_INT_MAX` or `max id + 1000`)
- [x] `it rejects a duplicate user role and keeps the user's previous roles` (expects `UniqueConstraintViolationException`; replaces the existing `rejects a duplicate user role`)
- [x] `it lets the caller commit its own transaction after a failed role sync` (begin transaction, do own write, catch the failed sync, commit; own write and previous roles both persist. On PostgreSQL this shows the savepoint keeps the outer transaction usable)

## Acceptance Criteria
- Tests pass against local MySQL 8.4, MariaDB and PostgreSQL servers
- Code follows code standards

## Implementation Notes
Rewrote the duplicate-role test in place; added unknown-role-id (ForeignKeyConstraintViolationException) and caller-commit tests. Verified red on the old code and green on MySQL 8.4, MariaDB 11.8, MariaDB 10.11 and PostgreSQL 17.
