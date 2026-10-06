# Task 003: PermissionRepository canonical keys and case-variant repair on sync

**Status**: completed
**Depends on**: [001]
**Retry count**: 0

## Description
save() rejects invalid keys, findByKey() returns null for non-canonical keys without querying, and sync renames a stored row that differs from a registered key only in case instead of inserting a colliding or duplicate row.

## Context
- Related files: packages/admin-auth/src/Repository/PermissionRepository.php
- Patterns to follow: existing admin-auth unit tests and MarkoException static factories
- `PermissionRepository` has no `save()` override today. Add one (guard with `instanceof Permission`, validate, then `parent::save()`), plus an `insertBatch()` override that validates every entity before delegating, so the multi-row INSERT is not a way around validation.
- Repair matching: a stored row matches a registered key when `strtolower($stored->key) === $registered->key` and no stored row has the exact key. Rename it in place (same id), so its `role_permissions` rows stay.

## Requirements (Test Descriptions)
- [x] `it throws invalidPermissionKey when saving a permission with a non-canonical key`
- [x] `it throws invalidPermissionKey from insertBatch and inserts nothing when any key is non-canonical`
- [x] `it returns null from findByKey for a non-canonical key without querying`
- [x] `it renames a stored case variant of a registered key to the canonical key and keeps its role assignments`
- [x] `it reports the renamed key as updated and not as unregistered`
- [x] `it matches the exact key and reports the case variant as unregistered when both are stored`
- [x] `it renames the lowest-id variant and reports the others as unregistered when several case variants are stored`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
