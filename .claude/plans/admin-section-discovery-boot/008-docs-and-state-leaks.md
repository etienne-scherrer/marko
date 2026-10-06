# Task 008: Docs and RoadRunner state-leak verdicts

**Status**: completed
**Depends on**: 004, 005, 006
**Retry count**: 0

## Description
Document how sections and permissions are discovered at boot, the cache note and the sync command; make the tutorial stop registering attribute-declared sections and permissions by hand; add state-leak verdicts for the new admin singletons and make the permission row accurate; include admin and admin-auth in the verdict-coverage test.

## Context
- Related files: packages/docs-markdown/docs/packages/admin.md, packages/docs-markdown/docs/packages/admin-auth.md, packages/docs-markdown/docs/packages/roadrunner-state-leaks.md, packages/docs-markdown/docs/tutorials/build-an-admin-panel.md, packages/roadrunner/tests/StateLeakSpikeTest.php
- Tutorial: remove the manual `permissionRegistry->register(...)` calls (~lines 266-290) for attribute-declared permissions, replace the hand-run `syncFromRegistry` (~line 299) with `marko admin-auth:permissions:sync`, and remove `$sectionRegistry->register(new PostsSection())` (~line 378); explain that `getId()` must match the attribute id.
- admin-auth.md: update the documented `syncFromRegistry(...)` signature (~line 277) from `: void` to `: int`; document the sync command and the duplicate-permission errors.
- admin.md / admin-auth.md: note that a warm discovery cache must be rebuilt (`discovery:cache`) after adding or changing an `#[AdminSection]`/`#[AdminPermission]` class.
- State-leak verdicts: add rows (with `Leaks:`) for `AdminSectionRegistryInterface` and `DiscoveredAdminSections`; the registry verdict must state that section instances are created once at boot and live for the worker lifetime, so sections must not hold request/user state. Update the existing `PermissionRegistryInterface` row so it says permissions are registered by admin-auth's boot from `#[AdminPermission]` attributes. Add `'admin'` and `'admin-auth'` entries to `$moduleFiles` in StateLeakSpikeTest.

## Requirements (Test Descriptions)
- [x] `it records a verdict for every singleton declared across the monorepo` (extended to admin and admin-auth)

## Acceptance Criteria
- Docs follow docs/DOCS-STANDARDS.md

## Implementation Notes
