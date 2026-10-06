# Task 001: Lazy Registry

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Teach `AdminSectionRegistry` to hold section definitions and build each section through the container on first use, caching the instance. Add `registerDefinition()` to `AdminSectionRegistryInterface` and a wrapping exception for constructor failures.

## Context
- Related files: packages/admin/src/AdminSectionRegistry.php, packages/admin/src/Contracts/AdminSectionRegistryInterface.php, packages/admin/src/Exceptions/AdminException.php, packages/admin/tests/Unit/AdminSectionRegistryTest.php, packages/admin/tests/Unit/Contracts/AdminSectionRegistryInterfaceTest.php
- Patterns to follow: packages/core/src/Command/CommandRunner.php (container-backed lazy resolution)

## Requirements (Test Descriptions)
- [x] `it does not build a section when its definition is registered`
- [x] `it builds a registered definition through the container on first get`
- [x] `it builds each section only once and reuses the instance`
- [x] `it sorts all sections by the built instance getSortOrder even when the attribute sortOrder differs`
- [x] `it keeps registration order for sections with equal sort order`
- [x] `it wraps a section constructor failure in an AdminException naming the section class and id with the original as previous`
- [x] `it does not cache a failed build and retries on the next call`
- [x] `it throws sectionIdMismatch on first resolution when getId differs from the definition id`
- [x] `it rejects a definition whose class does not exist without building anything`
- [x] `it rejects a definition whose class does not implement AdminSectionInterface`
- [x] `it rejects a definition whose id is already taken, naming both classes`
- [x] `it rejects a manual section whose id is taken by a definition`
- [x] `it keeps registering built sections by hand`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- Sorting: `all()` builds every entry anyway, so sort the built instances by `getSortOrder()`, the same as today. Do NOT sort by the attribute `sortOrder`. It defaults to 0 and many sections set their order only in `getSortOrder()`, so switching would silently reorder menus. `usort` is stable in PHP 8+.
- New factory: `AdminException::sectionBuildFailed(string $className, string $id, Throwable $previous): self`. The message names the class and the id, and it passes `previous: $previous` to the constructor (MarkoException accepts `?Throwable $previous`).
- Wrap only the `$this->container->get($className)` call in try/catch(Throwable). Run the id-mismatch check outside the try so `sectionIdMismatch` is not wrapped. Cache the instance only after both checks pass.
- Update the `context` of `sectionIdMismatch` (currently "...at boot") to say it is raised when the section is first built.
- Duplicate check for a definition: name the existing entry's class (definition className, or the instance's `::class`) and the new class.
- Interface check without instantiating: `is_subclass_of($className, AdminSectionInterface::class)`, run after `class_exists`. Reuse `sectionClassNotFound` / `sectionMustImplementInterface`.
- Constructor: `__construct(private ContainerInterface $container)`. Update the existing `new AdminSectionRegistry()` calls in AdminSectionRegistryTest and the reflection test in AdminSectionRegistryInterfaceTest (method list now includes `registerDefinition`).

- Implemented as `AdminException::sectionBuildFailed(string $id, string $className, Throwable $previous)` (id first, matching `duplicateSection()`).
