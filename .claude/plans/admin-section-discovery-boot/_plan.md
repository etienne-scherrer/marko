# Plan: Admin Section Discovery at Boot

## Created
2026-10-06

## Status
completed

## Objective
Discover `#[AdminSection]` classes and their `#[AdminPermission]` entries at boot (without database access), cache the scan through the discovery cache, share the section registry, and expose an explicit command for persisting permissions to the database.

## Related Issues
Closes #314

## Discovery Notes
- Nothing calls `AdminSectionDiscovery` or `PermissionDiscovery` outside tests; neither `marko/admin` nor `marko/admin-auth` has a `boot` closure.
- `AdminSectionRegistryInterface` is a plain binding, so every consumer gets its own empty registry.
- `EntityCacheContributor` + `packages/database/module.php` boot is the pattern for a cached-or-scan boot (`CachedDiscovery::section(KEY) ?? scan`). `RouteCacheContributor::hydrate()` is the pattern for validating a cached section.
- Boot closures can inject `ModuleRepositoryInterface` (enabled modules in load order) and `CachedDiscovery`; routing scans `$module->path . '/src'` the same way `AdminSectionDiscovery::discoverInModule()` does.
- #313 gaps: `ClassFileParser::extractClassName()` only returns the first class in a file, and the `#[AdminSection` text pre-filter misses `#[\Marko\Admin\Attributes\AdminSection(...)]`, aliased imports (`use ...\AdminSection as Section; #[Section]`) and grouped attributes (`#[Foo, AdminSection]`).
- The admin tutorial registers the attribute-declared permissions and the section by hand; once boot does it, those manual calls would throw duplicate errors, so the tutorial must change too.
- Decision (issue recommendation): boot stays in-memory only; persisting permissions is the explicit `admin-auth:permissions:sync` command.

## Scope

### In Scope
- `ClassFileParser::extractClassNames()` (every class-like declaration in a file, namespace-aware)
- `AdminSectionDiscovery`: every class per file, a pre-filter that catches every spelling of the attribute, `discoverAll()` over modules with a duplicate-id check naming both classes
- `AdminSectionCacheContributor` (compile + hydrate)
- `DiscoveredAdminSections` shared, memoised list of definitions (cache or scan, parsed once)
- `marko/admin` boot: registers every section (resolved through the container) in the shared registry
- `marko/admin-auth` boot: registers every `#[AdminPermission]` from the same definitions
- Duplicate errors naming both classes (`AdminException::duplicateSection`, `AdminAuthException::duplicatePermission`)
- `admin-auth:permissions:sync` command; `syncFromRegistry()` returns the number created
- Feature test through the real router for `GET /admin/api/v1/sections`
- Docs: admin.md, admin-auth.md, roadrunner-state-leaks.md, build-an-admin-panel tutorial; state-leak verdict test covers admin + admin-auth

### Out of Scope
- Database access at boot
- Dispatching `PermissionsSynced` from the command
- Changes to admin-panel / admin-api controllers

## Success Criteria
- [x] `AdminSectionRegistryInterface` is a shared singleton; wiring test proves two consumers share sections
- [x] An `#[AdminSection]` class appears in the registry after boot with no manual registration
- [x] Its `#[AdminPermission]` entries appear in `PermissionRegistryInterface::all()` after boot, grouped by first key segment
- [x] Duplicate section id / permission key fails boot loudly, naming both classes
- [x] Warm discovery cache registers sections and permissions without scanning; `discovery:cache` output lists the section
- [x] `GET /admin/api/v1/sections` returns an attribute-only section through the real router
- [x] Sync command has tests
- [x] Docs accurate
- [x] All tests passing (`composer ci`)

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | ClassFileParser::extractClassNames | - | completed |
| 002 | AdminSectionDiscovery: all classes, robust pre-filter, discoverAll; duplicateSection names both classes (factory + registry caller) | 001 | completed |
| 003 | AdminSectionCacheContributor + DiscoveredAdminSections | 002 | completed |
| 004 | marko/admin module wiring: singleton registry, boot (with getId/attribute id check), discovery contributor | 003 | completed |
| 005 | marko/admin-auth boot registers permissions from definitions (duplicatePermission names both classes; permissionAlreadyRegistered for manual collisions) | 003 | completed |
| 006 | admin-auth:permissions:sync command | - | completed |
| 007 | Feature test through the real router + discovery:cache output | 004, 005 | completed |
| 008 | Docs and RoadRunner state-leak verdicts | 004, 005, 006 | completed |

## Architecture Notes
- `marko/admin` must not depend on `marko/admin-auth`; admin-auth reads `DiscoveredAdminSections` from admin.
- `DiscoveredAdminSections` is lazy and memoised, so boot order between the two modules does not matter and the parse happens once.
- The cache stores definitions as scalars (class name, id, label, icon, sort order, permissions), so a cached boot neither scans nor reflects.
- Shared contracts (full detail in task files 002/003): `AdminSectionDiscovery::discoverAll(array<ModuleManifest>): array<AdminSectionDefinition>`; `AdminSectionCacheContributor::KEY = 'admin_sections'`, `hydrate(): array<AdminSectionDefinition>`; `DiscoveredAdminSections::all(): array<AdminSectionDefinition>`. The contributor's `compile()` scans the `$modules` it is given, never `DiscoveredAdminSections`.
- Exception factories: `AdminException::duplicateSection(id, existingClass, duplicateClass)` changes together with its only caller `AdminSectionRegistry::register()` in task 002. `AdminAuthException::duplicatePermission(key)` stays as is (PermissionRegistry only knows the key); task 005 gives it optional `existingClass`/`duplicateClass` parameters (the review's alternative) and adds `permissionAlreadyRegistered()` for keys registered by hand.
- The registry keys on `getId()` while discovery uses the attribute id; admin's boot rejects a mismatch (`AdminException::sectionIdMismatch`).
- Duplicate errors caused by leftover manual registration suggest removing the manual `register()` call.

## Risks & Mitigations
- Broader pre-filter loads more candidate files: reflection still confirms the attribute; candidates must mention `AdminSection` and contain `#[`.
- Apps that registered sections/permissions by hand now get duplicate errors: docs and tutorial explain that attribute classes are registered automatically.
