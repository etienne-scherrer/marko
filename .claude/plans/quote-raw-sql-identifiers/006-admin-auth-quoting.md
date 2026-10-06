# Task 006: admin-auth pivot/join table names

**Status**: completed
**Depends on**: [none]
**Retry count**: 0

## Description
Quote the permissions, role_permissions, roles and admin_user_roles table names in RoleRepository and AdminUserRepository raw SQL. Keep the edit to the SQL strings only (#341 and #347 touch the same files).

## Context
- Related files: packages/admin-auth/src/Repository/RoleRepository.php, packages/admin-auth/src/Repository/AdminUserRepository.php
- Patterns to follow: packages/database/tests/Repository/RepositoryIdentifierQuotingTest.php (backtick-quoting stub connection), #331

## Requirements (Test Descriptions)
- [ ] `it quotes the pivot and joined tables in role permission queries`
- [ ] `it quotes the pivot and joined tables in admin user role queries`

## Notes (from review)
- PermissionRepository already quotes through the connection (lines 141-228); follow its sprintf style.
- RoleRepositoryTest/AdminUserRepositoryTest doubles quote with `"`; update any exact-SQL assertions. The SQLite fixture (tests/Fixtures/SqlitePermissionConnection.php) and the admin-auth MySql/PgSql integration suites must still pass.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
