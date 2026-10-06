# Task 005: marko/admin-auth registers #[AdminPermission] at boot

**Status**: completed
**Depends on**: 003
**Retry count**: 0

## Description
`PermissionDiscovery::registerFromDefinitions(array $definitions): void` (`array<AdminSectionDefinition>`) registers every permission of the given section definitions, grouped by the first key segment. It tracks key => className itself across the batch and, before calling `register()`, throws a new `AdminAuthException::duplicatePermissionFromSections(string $key, string $existingClass, string $duplicateClass)` naming both classes when two sections declare the same key. `discoverFromClass()` reuses it. admin-auth's `boot` (`function (DiscoveredAdminSections $sections, PermissionDiscovery $discovery): void`) feeds it `$sections->all()`.

Do NOT change the existing `AdminAuthException::duplicatePermission(string $key)` required parameters: `PermissionRegistry::register()` calls it and only knows the key. When `register()` throws for a definition's key (the key was registered by hand elsewhere, e.g. code following the old tutorial), rethrow an `AdminAuthException` that names the declaring section class and suggests removing the manual `PermissionRegistryInterface::register()` call, since `#[AdminPermission]` entries are registered automatically at boot (chain the original as `previous`).

## Context
- Related files: packages/admin-auth/src/Discovery/PermissionDiscovery.php, packages/admin-auth/src/Exceptions/AdminAuthException.php, packages/admin-auth/module.php, packages/admin-auth/tests/Unit/ModuleWiringTest.php
- Builds against task 003's `DiscoveredAdminSections::all()` contract. Do NOT edit packages/admin/module.php (owned by task 004, built in parallel); tests here bind `DiscoveredAdminSections` as a singleton, plus `ModuleRepositoryInterface` and `CachedDiscovery`, in the test container themselves, then `$container->call($module['boot'])`.
- Existing tests that build a container from admin-auth's module.php (ModuleWiringTest, AdminAuthRouterTest, admin-api's SectionControllerWiringTest and AdminApiErrorShapeTest) ignore `boot` and must keep passing.

## Requirements (Test Descriptions)
- [x] `it registers the permissions of every section definition grouped by the first key segment`
- [x] `it throws duplicatePermission naming both classes when two sections declare the same key`
- [x] `it names the section class and suggests removing the manual registration when a key was already registered by hand`
- [x] `it registers attribute-declared permissions at boot`
- [x] `it registers permissions from a warm discovery cache without scanning`

## Acceptance Criteria
- marko/admin does not depend on marko/admin-auth
- No database access at boot

## Implementation Notes

Took the review's "optional trailing params" alternative instead of a new `duplicatePermissionFromSections()` factory: `AdminAuthException::duplicatePermission(key, ?existingClass, ?duplicateClass)` keeps its required parameter (so `PermissionRegistry::register()` is unchanged) and is the factory the issue's exit criteria name. `AdminException::duplicateSection()` got the same shape. A key already registered by hand is rethrown as `AdminAuthException::permissionAlreadyRegistered(key, sectionClass, previous)`.
