# Devil's Advocate Review: admin-section-discovery-boot

## Critical (Must fix before building)

### C1. Task 002 changes `AdminException::duplicateSection()` but its only caller is fixed in task 004
`AdminSectionRegistry::register()` calls `AdminException::duplicateSection($id)` (packages/admin/src/AdminSectionRegistry.php:24). Task 002 adds "naming both classes" to that factory; task 004 is where the registry is updated. Between 002 and 004 the build (and PHPStan) is broken, and 003 runs on a broken tree. Fix: task 002 owns the new factory signature `duplicateSection(string $id, string $existingClass, string $duplicateClass)` AND updates `AdminSectionRegistry::register()` to pass `$this->sections[$id]::class` / `$section::class`. Move the "names both classes when a duplicate section id is registered" test from 004 to 002.

### C2. `AdminAuthException::duplicatePermission()` signature change breaks `PermissionRegistry::register()`
`PermissionRegistry::register()` only knows the key (packages/admin-auth/src/PermissionRegistry.php:24); it cannot name classes. Task 005 must not change the existing factory's required parameters. Fix: add a separate factory `duplicatePermissionFromSections(string $key, string $existingClass, string $duplicateClass)` (or optional trailing params); `PermissionDiscovery::registerFromDefinitions()` detects cross-definition duplicates itself before calling `register()`.

### C3. Interface contracts between 002 -> 003 -> 004/005 are undefined
004 and 005 run in parallel against 003's API, and 003 against 002's. None of `discoverAll()`, `DiscoveredAdminSections`, the cache record shape or the contributor key constant is spelled out. Fix: pin these in the task files:
- `AdminSectionDiscovery::discoverInModule(ModuleManifest): array<int, class-string>`; `discoverAll(array<ModuleManifest> $modules): array<int, AdminSectionDefinition>` (module order, then file order).
- `AdminSectionCacheContributor::KEY = 'admin_sections'`, `compile(array $modules): array`, `hydrate(array $section): array<int, AdminSectionDefinition>`; record shape `{className: string, id: string, label: string, icon: string, sortOrder: int, permissions: list<{id: string, label: string}>}`.
- `Marko\Admin\Discovery\DiscoveredAdminSections::all(): array<int, AdminSectionDefinition>`, constructor `(CachedDiscovery, ModuleRepositoryInterface, AdminSectionDiscovery, AdminSectionCacheContributor)`; not readonly (memo property).

## Important (Should fix before building)

### I1. Attribute id vs `getId()` can disagree
The duplicate check in `discoverAll()` and the permission grouping use the attribute id, but `AdminSectionRegistry` keys on the instance's `getId()` (the tutorial hand-writes `getId()` beside `#[AdminSection(id: 'posts')]`). A mismatch silently registers the section under a different id. Fix (task 004): boot throws a new `AdminException::sectionIdMismatch($className, $attributeId, $getIdValue)`; also throw `sectionMustImplementInterface` if the container returns something not implementing `AdminSectionInterface` (a Preference can swap it; PHPStan also needs the instanceof narrowing).

### I2. Manual + automatic registration produces an unhelpful duplicate error
Apps following the current tutorial call `$sectionRegistry->register(new PostsSection())` and `$permissionRegistry->register(...)`. After this change, the second registration throws. For permissions the registry error only names the key. Fix: the duplicate suggestion must say attribute-declared sections/permissions are registered automatically at boot and the manual `register()` call should be removed (task 002 for sections, task 005 for permissions: when `register()` throws for a definition's key, rethrow naming the section class with that suggestion).

### I3. Task 002 changes `discoverInModule()` return value; existing #313 tests assert file paths
The existing `AdminSectionDiscoveryTest` expectations are file paths. Task 002 must update them, not only add new tests, and update the docblock.

### I4. Contributor must use the `$modules` argument, not `DiscoveredAdminSections`
`DiscoveryCompiler` passes the module list to `compile()`. If the contributor goes through `DiscoveredAdminSections` (which reads `ModuleRepositoryInterface`), compile reads a different module list and memoised state. Fix (003): `compile()` calls `AdminSectionDiscovery::discoverAll($modules)` directly.

### I5. `PermissionRepositoryInterfaceTest` asserts `syncFromRegistry` returns void
packages/admin-auth/tests/Unit/Repository/PermissionRepositoryInterfaceTest.php:70 ("syncFromRegistry method signature returns void") breaks once 006 returns `int`. Task 006 must update that test and the interface.

### I6. Task 007 lacks a test location and setup recipe; guest requests get 401
`SectionController` sits behind `AdminAuthMiddleware`; a guest gets 401, and a user without the section's menu-item permissions gets the section filtered out. Fix: specify `packages/admin-api/tests/Feature/AdminSectionBootTest.php`, a `FakeGuard` with an `AdminUser`, a fixture section with no menu items, how to boot module.php files (BindingRegistry + bind `ModuleRepositoryInterface`/`CachedDiscovery` + `container->call($module['boot'])`), and a temp project path for the `discovery:cache` assertion.

### I7. Test-harness notes for 004/005
- 005 must not edit packages/admin/module.php (owned by 004, parallel). 005 tests wire `DiscoveredAdminSections` as a singleton themselves.
- "Without scanning" needs a proof mechanism: point the module's path at a non-existent directory or inject a spying `ClassFileParser`; on the cached path the fixture class must be autoloadable or pre-required.

### I8. Tutorial path in 008 is wrong; admin-auth docs show the old `syncFromRegistry` signature
Tutorial lives at packages/docs-markdown/docs/tutorials/build-an-admin-panel.md (lines ~266-299 register permissions and call `syncFromRegistry` by hand, line 378 registers the section by hand). admin-auth.md:277 documents `: void`. The new RoadRunner verdicts must state that section instances live for the worker lifetime (they are instantiated once at boot and held by the singleton registry), so sections must not hold request state.

### I9. Global namespace / braced namespaces in task 001
`namespace { ... }` produces an empty namespace name; the existing code would build `'\Foo'`. Add a requirement that an empty namespace yields an unqualified name, and that braced multi-namespace files qualify each class with its own block.

## Minor (Nice to address)
- Broader pre-filter means files such as admin-panel/admin-api controllers (they mention `AdminSectionRegistryInterface` and have `#[Get]`) are `require_once`d on every uncached boot. Reflection rejects them; cost is small, but worth a benchmark note.
- An abstract class with `#[AdminSection]` passes `parseAdminSectionClass()` and fails later inside the container with a generic error; a dedicated `AdminException` would be clearer.
- `discovery:cache` runs the scan twice (once in boot via `DiscoveredAdminSections`, once in `compile()`); acceptable.
- `PermissionDiscovery` is `readonly`; cross-batch duplicate class tracking is impossible across separate `discoverFromClass()` calls (only within one `registerFromDefinitions()` batch). Fine for boot.

## Questions for the Team
- Sections are instantiated eagerly at every boot (every FPM request, every console command including `discovery:cache` in a build step). A section whose constructor graph needs DB/env config would break non-admin requests and cache builds. Is eager instantiation acceptable, or should the registry hold lazy factories (would change `AdminSectionRegistryInterface`)?
- Should `admin-auth:permissions:sync` also remove/report permissions in the DB that no longer exist in code? (Plan only creates.)
- Should a cached boot verify that `className` still exists before resolving, to give a "run discovery:cache" hint instead of a container error?
