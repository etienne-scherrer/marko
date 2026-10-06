# Task 001: IdentifierFormat and exception factories

**Status**: completed
**Depends on**: [none]
**Retry count**: 0

## Description
Add a class holding the documented permission-key and role-slug patterns, plus AdminAuthException factories for invalid keys and slugs.

## Context
- Related files: packages/admin-auth/src/IdentifierFormat.php, packages/admin-auth/src/Exceptions/AdminAuthException.php
- Patterns to follow: existing admin-auth unit tests and MarkoException static factories
- NOTE: `IdentifierFormat.php` and both factories already exist in the worktree without tests. Write the tests and fix the code where they fail; do not recreate the class.
- Fixed API (tasks 002-005 build against it): `IdentifierFormat::PERMISSION_KEY_PATTERN`, `IdentifierFormat::ROLE_SLUG_PATTERN`, `IdentifierFormat::isPermissionKey(string): bool`, `IdentifierFormat::isRoleSlug(string): bool`, `AdminAuthException::invalidPermissionKey(string $key, ?string $declaringClass = null)`, `AdminAuthException::invalidRoleSlug(string $slug)`

## Requirements (Test Descriptions)
- [x] `it accepts lowercase dotted permission keys`
- [x] `it accepts the global wildcard and wildcard segments after the first`
- [x] `it rejects permission keys with uppercase letters, spaces, accents, empty segments or a trailing newline`
- [x] `it rejects a wildcard in the first segment of a dotted key`
- [x] `it accepts lowercase dotted role slugs and rejects uppercase, wildcards and whitespace`
- [x] `it names the key and the declaring class in invalidPermissionKey`
- [x] `it names the slug in invalidRoleSlug`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
