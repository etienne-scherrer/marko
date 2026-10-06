# Task 004: marko/admin module wiring

**Status**: completed
**Depends on**: 003
**Retry count**: 0

## Description
Move `AdminSectionRegistryInterface` to the key-value `singletons` form, share `DiscoveredAdminSections`, declare `AdminSectionCacheContributor` under `discovery`, and add a `boot` closure that resolves each discovered section through the container and registers it. (The registry's duplicate-id message naming both classes is done in task 002.)

The boot closure verifies each resolved instance: it must implement `AdminSectionInterface` (else `AdminException::sectionMustImplementInterface`; a Preference may swap the class, and PHPStan needs the narrowing), and its `getId()` must equal the attribute id from the definition (else a new `AdminException::sectionIdMismatch(string $className, string $attributeId, string $instanceId)`), because the registry keys on `getId()` while discovery duplicate checks and permissions use the attribute id.

## Context
- Related files: packages/admin/module.php, packages/admin/src/AdminSectionRegistry.php, packages/admin/src/Exceptions/AdminException.php, packages/admin-auth/tests/Unit/ModuleWiringTest.php (pattern), packages/admin/tests/Unit/Config/AdminConfigTest.php (reads `$module['bindings']`; must still pass)
- Boot closure signature: `function (DiscoveredAdminSections $sections, AdminSectionRegistryInterface $registry, ContainerInterface $container): void`
- Test harness: register the module via `BindingRegistry::registerModule(new ModuleManifest(..., bindings:, singletons:))`, bind `ModuleRepositoryInterface` (a `ModuleRepository` over a fixture module manifest) and `CachedDiscovery`, then `$container->call($module['boot'])`.
- "Without scanning" proof: on the cached path, point the fixture module manifest's `path` at a non-existent directory (or inject a spying `ClassFileParser`) and make sure the fixture section class is autoloadable/pre-required, since a cached boot only has the class name.

## Requirements (Test Descriptions)
- [x] `it binds AdminSectionRegistryInterface to AdminSectionRegistry as a singleton in module.php`
- [x] `it shares registered sections between separately injected consumers`
- [x] `it registers an attribute-declared section at boot with no manual registration`
- [x] `it resolves sections through the container so they can inject dependencies`
- [x] `it registers sections from a warm discovery cache without scanning`
- [x] `it declares the admin section contributor in module.php`
- [x] `it declares DiscoveredAdminSections as a singleton in module.php`
- [x] `it throws sectionIdMismatch when getId differs from the attribute id`

## Acceptance Criteria
- No database access at boot
- packages/admin/module.php remains free of any marko/admin-auth reference

## Implementation Notes
