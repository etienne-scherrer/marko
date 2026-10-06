# Task 006: Integration tests on MySQL/MariaDB and PostgreSQL

**Status**: completed
**Depends on**: [002, 003, 004, 005]
**Retry count**: 0

## Description
Real-database tests showing the same outcome on every driver.

## Context
- Related files: packages/admin-auth/tests/Integration/{MySql,PgSql}/CanonicalValuesTest.php, packages/admin-auth/tests/Integration/AdminAuthSchema.php
- Patterns to follow: existing `tests/Integration/{MySql,PgSql}/PermissionRepositoryTest.php` (IntegrationDatabase fixture, `integration-services` group, AdminAuthSchema create/drop and repository/userProvider helpers)
- Seeding a stored case variant (e.g. `Blog.Posts.View`) must use a raw INSERT through the connection, with quoted identifiers (`key` and `group` are reserved words). `save()` and `AdminAuthSchema::permission()` reject non-canonical keys after task 003.
- The duplicate-email test expects the database's unique-constraint exception from the second insert (the email is lowercased first, so it collides on every driver), not an AdminAuthException.

## Requirements (Test Descriptions)
- [x] `it repairs a stored case variant on re-sync without a duplicate or a failure`
- [x] `it logs in with a different-case email`
- [x] `it rejects a second admin whose email differs only in case`
- [x] `it rejects a role with a non-canonical slug before it reaches the database`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
