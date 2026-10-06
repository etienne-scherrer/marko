# Task 004: Interface docblock and admin-auth docs

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
Document the atomicity guarantee of `syncRoles()` on `AdminUserRepositoryInterface` and in `admin-auth.md`, matching `syncPermissions()`.

## Context
- Related files: packages/admin-auth/src/Repository/AdminUserRepositoryInterface.php, packages/admin-auth/src/Repository/AdminUserRepository.php, packages/docs-markdown/docs/packages/admin-auth.md
- Patterns to follow: the `RoleRepository::syncPermissions()` docblock (the atomicity text lives on the implementation; `RoleRepositoryInterface` has only `@throws Throwable`); the admin-auth.md syncPermissions paragraph (~line 491)

## Requirements (Test Descriptions)
- [x] Interface docblock states the sync is atomic, a savepoint inside a caller's transaction, and a caught failure lets the caller commit
- [x] Interface `syncRoles()` declares `@throws Throwable` (add `use Throwable;`), matching `RoleRepositoryInterface::syncPermissions()`
- [x] `AdminUserRepository::syncRoles()` docblock carries the same guarantee text as `RoleRepository::syncPermissions()`
- [x] admin-auth.md describes the same guarantee for syncRoles()

## Acceptance Criteria
- Docs match behaviour
- README unchanged (slim pointer has no repository detail)

## Implementation Notes
Docblocks on both interfaces and both implementations; one paragraph in admin-auth.md covers syncPermissions() and syncRoles(). README unchanged.
