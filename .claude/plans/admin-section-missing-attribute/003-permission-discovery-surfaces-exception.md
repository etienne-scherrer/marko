# Task 003: PermissionDiscovery surfaces the new exception

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Prove that admin-auth's `PermissionDiscovery::discoverFromClass()` propagates the new missing-attribute `AdminException` unchanged and registers nothing.

## Context
- Related files: packages/admin-auth/tests/Unit/Discovery/PermissionDiscoveryTest.php, packages/admin-auth/src/Discovery/PermissionDiscovery.php

- Existing fixture `DiscoveryNotASection` has only `#[AdminPermission]` (no `#[AdminSection]`, no interface). After task 001 it hits the missing-attribute path, so:
  - The existing test `it throws AdminException when the class does not implement AdminSectionInterface` silently stops exercising the interface path. Add a new fixture `DiscoveryAttributeWithoutInterface` (`#[AdminSection(id: 'reports', label: 'Reports')]`, no interface) and point that test at it, asserting the message contains `does not implement AdminSectionInterface`.
  - The existing `it registers nothing when discovery fails` test already covers "no permissions registered" for the missing-attribute case; do not add a duplicate test.
- This task is test-only; tests pass once 001 lands (no red phase expected). Do not change production code.

## Requirements (Test Descriptions)
- [ ] `it surfaces the missing AdminSection attribute exception unchanged` (uses `DiscoveryNotASection`; asserts `AdminException` with message containing `is not marked with #[AdminSection]`)
- [ ] Existing interface test re-pointed at new `DiscoveryAttributeWithoutInterface` fixture and asserts the interface message
- [ ] Existing `it registers nothing when discovery fails` still passes (now via the missing-attribute path)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented with strict TDD (tests written first and observed failing). See the commit for the code.
