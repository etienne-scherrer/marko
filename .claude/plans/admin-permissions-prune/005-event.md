# Task 005: PermissionsSynced carries updated/unregistered/pruned counts

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Append `updatedCount`, `unregisteredCount` and `prunedCount` (defaulted to 0, so existing constructions keep working) with getters to `PermissionsSynced`.

## Context
- Related files: `packages/admin-auth/src/Events/PermissionsSynced.php`, `tests/Unit/Events/EventTimestampTest.php`

## Requirements (Test Descriptions)
- [x] `it exposes the updated, unregistered and pruned counts of a sync`
- [x] `it defaults the updated, unregistered and pruned counts to zero`
- [x] `it keeps the created and total counts and the timestamp`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
