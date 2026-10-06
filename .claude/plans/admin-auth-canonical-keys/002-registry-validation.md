# Task 002: Validate keys in PermissionRegistry and PermissionDiscovery

**Status**: completed
**Depends on**: [001]
**Retry count**: 0

## Description
register() rejects keys outside the pattern; discovery validates #[AdminPermission] ids first and names the declaring section class.

## Context
- Related files: packages/admin-auth/src/PermissionRegistry.php, packages/admin-auth/src/Discovery/PermissionDiscovery.php, packages/admin-auth/src/Contracts/PermissionRegistryInterface.php
- Patterns to follow: existing admin-auth unit tests and MarkoException static factories
- GOTCHA: `registerFromDefinitions()` wraps every `AdminAuthException` thrown by `register()` as `permissionAlreadyRegistered()`. Validate ids in the FIRST loop (next to the duplicate check, before any `register()` call) using `invalidPermissionKey($permission->id, $definition->className)`. Otherwise an invalid key is reported as "already registered" and earlier sections end up partially registered.

## Requirements (Test Descriptions)
- [x] `it throws invalidPermissionKey when registering a key with uppercase letters`
- [x] `it registers nothing when the key is invalid`
- [x] `it throws invalidPermissionKey naming the section class for an invalid AdminPermission id`
- [x] `it registers no permissions from any section when one declared key is invalid`
- [x] `it does not report an invalid AdminPermission id as already registered`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
