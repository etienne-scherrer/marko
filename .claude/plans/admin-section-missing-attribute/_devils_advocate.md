# Devil's Advocate Review: admin-section-missing-attribute

## Critical (Must fix before building)

None. The targeted code matches the plan's description: `parseAdminSectionClass()` indexes `getAttributes(AdminSection::class)[0]` blindly (AdminSectionDiscovery.php:65-66), `marko/admin` already requires `marko/core`, and the `= new ClassFileParser()` promoted-default pattern already exists (`Application.php:104`). It is legal on a `readonly class` because PHP 8.1+ allows `new` in parameter defaults.

## Important (Should fix before building)

### I1. Task 003: the existing admin-auth fixture silently changes which path it tests
`PermissionDiscoveryTest.php` has `DiscoveryNotASection`, which carries only `#[AdminPermission]`: no `#[AdminSection]` and no interface. Once task 001 checks the attribute first, two existing tests switch to the missing-attribute path without failing:
- `it throws AdminException when the class does not implement AdminSectionInterface` still passes, because it only asserts the exception class. Its name is now wrong, and admin-auth no longer has any test for the interface path.
- `it registers nothing when discovery fails` already covers task 003's second requirement (`it registers no permissions when the class has no AdminSection attribute`), so that requirement would be a duplicate.

**Fix applied:** Task 003 now asserts the missing-attribute message using the existing `DiscoveryNotASection` fixture. It also adds a fixture that has `#[AdminSection]` but no interface, and points the misnamed interface test at it so both paths stay covered. The duplicate requirement is folded into the existing "registers nothing" test.

### I2. Task 002: discovery now `require_once`s fixture files, so fixture FQCNs must be unique
Before this change `discoverInModule()` never loaded the files it found. Now it does. Every temp-file fixture class (existing and new) gets declared in the test process. If two fixtures share a namespace and class name but live at different paths, PHP fatals with "Cannot declare class". Existing tests use distinct namespaces (`AdminDiscoveryTest1`, `AdminDiscoveryTestMulti1`, ...), but new tests written from copy-paste could collide.

**Fix applied:** Task 002 now requires a unique namespace for each new fixture and an explicit `null` check on `extractClassName()` before `loadClass()`, following `EntityDiscovery::discoverInPath()`. It also notes that `loadClass()` returns false for interfaces and traits (so they are skipped) and re-throws for non-Marko missing dependencies, which is the intended loud behaviour.

### I3. Task 004: documenting `discoverInModule()` pulls #314 into scope
`admin.md` does not document `AdminSectionDiscovery` at all today; the only exception text is line 219, which covers the registry. Because discovery is not wired into boot yet (#314), the requirement `docs describe that discoverInModule only reports classes that carry the attribute` would document an API that does nothing for users and that #314 will reshape. That goes against "keep it a small bugfix" and the "no pseudo-functionality" principle.

**Fix applied:** Task 004 is narrowed. The docs now state, in the "Registering an Admin Section" text and next to line 219, that a class must carry `#[AdminSection]` and implement `AdminSectionInterface`, and that otherwise `AdminException` is thrown. `discoverInModule()` is not documented. The dependency on 002 is dropped (it now depends only on 001), and the `_plan.md` scope and success criteria were updated to match.

## Minor (Nice to address)

- **Task 002:** `ClassFileParser::extractClassName()` returns the first type declared in a file. If a file declares a helper class before the section class, the section is missed. This limitation already exists across the framework, so no change is needed.
- **Task 002:** The text pre-filter `#[AdminSection` does not match FQCN usage (`#[\Marko\Admin\Attributes\AdminSection(...)]`) or aliased imports. This is pre-existing and arguably belongs with #314.
- **Task 002:** `discoverInModule()` could use `$this->classFileParser->findPhpFiles()` instead of its own `RecursiveIteratorIterator` + `RegexIterator`. This is optional cleanup that #314 may reshape anyway.
- **Task 001:** The new global-namespace fixtures in `AdminSectionDiscoveryTest.php` need unique names across the admin test suite (for example `AdminSectionMissingAttribute` and `AdminSectionMissingAttributeAndInterface`).
- **Task 003:** This task is test-only. Once 001 has landed its tests will pass immediately, so there is no red phase. That is fine for characterization tests, but the worker should not "fix" production code to get a red.

## Questions for the Team

- Memory notes say the post-implementation `doc-updater` agent handles docs pages automatically. Is task 004 still needed, or would it duplicate that pipeline step?
- If a file passes the text filter but `loadClass()` returns false because it depends on an uninstalled Marko package, it is now skipped silently. Is that acceptable for admin sections? It matches every other discovery.
