# Task 002: AdminSectionDiscovery covers every class and every attribute spelling

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`discoverInModule()` returns the class names (not file paths) of every `#[AdminSection]` class in the module's `src/`, checking every class in each file. The text pre-filter selects any file that contains `#[` and mentions `AdminSection` case-insensitively, so the fully-qualified form, aliased imports and grouped attributes are candidates; reflection still confirms the attribute. `discoverAll()` parses every section across the given modules into `AdminSectionDefinition`s and fails loudly on a duplicate section id, naming both classes.

## Context
- Related files: packages/admin/src/Discovery/AdminSectionDiscovery.php, packages/admin/src/Exceptions/AdminException.php, packages/admin/src/AdminSectionRegistry.php, packages/admin/tests/Unit/Discovery/AdminSectionDiscoveryTest.php, packages/admin/tests (AdminSectionRegistry test)
- Patterns to follow: routing's `RouteCollector` (module `src/` scan)

## Interface Contract (tasks 003/004/005 build against this)
- `discoverInModule(ModuleManifest $manifest): array<int, class-string>` — class names, not file paths.
- `discoverAll(array $modules): array<int, AdminSectionDefinition>` — `$modules` is `array<ModuleManifest>`; order = module order, then file/declaration order.
- `AdminException::duplicateSection(string $id, string $existingClass, string $duplicateClass): self` — message names the id and both classes; suggestion says each section id must be unique AND that `#[AdminSection]` classes are registered automatically at boot, so any manual `AdminSectionRegistryInterface::register()` call for them must be removed.

## Requirements (Test Descriptions)
- [x] `it discovers every admin section class in a file that declares several classes`
- [x] `it discovers a section marked with the fully-qualified attribute name`
- [x] `it discovers a section marked with an aliased attribute import`
- [x] `it discovers a section whose attribute is grouped with another attribute`
- [x] `it parses every discovered section across modules in module order`
- [x] `it throws duplicateSection naming both classes when two sections share an id`
- [x] `it names both classes when a duplicate section id is registered` (AdminSectionRegistry::register passes `$this->sections[$id]::class` and `$section::class`; moved here from task 004 so the factory signature change and its only caller change together)

## Acceptance Criteria
- Existing #313 behaviour (skip comment-only and longer-name matches, report invalid sections) still holds
- Existing `AdminSectionDiscoveryTest` expectations that assert file paths are updated to class names (not left failing); docblock updated
- `AdminSectionRegistry::register()` is updated in this task so the build and PHPStan stay green after the `duplicateSection()` signature change
- Files that declare an interface/trait before the section class still work (`loadClass()` returns false for interfaces; continue to the next name)

## Implementation Notes
