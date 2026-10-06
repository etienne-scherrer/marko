# Task 008: PermissionRepository::findByGroup() without driver-specific syntax

**Status**: completed
**Depends on**: 006
**Retry count**: 0

## Description
Replace the hard-coded MySQL backtick SQL in `PermissionRepository::findByGroup()` with `$this->findBy(['group' => $group])->toArray()`, so the column is quoted by the connection's rule. `findBy()` returns an `EntityCollection`; the interface method returns `array<Permission>`, so `->toArray()` is required.

## Context
- Related files: `packages/admin-auth/src/Repository/PermissionRepository.php`, `packages/admin-auth/tests/Unit/Repository/PermissionRepositoryTest.php` (line ~166 asserts `` `group` = ? ``; it becomes `"group" = ?` with the ANSI-quoting stub)

## Requirements (Test Descriptions)
- [x] `it finds permissions by group through the connection's quoting`
- [x] `it contains no backtick in PermissionRepository`
- [x] `it returns an array of Permission entities from findByGroup`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
