# Task 002: admin-api SectionController resolves from module bindings

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Prove admin-api's `SectionController` (which injects `PermissionRegistryInterface`) can be built from a container wired with `marko/admin-auth`'s module.php and no app-level registry binding, and that it shares the registry instance with `AdminAuthMiddleware`.

## Context
- Related files: packages/admin-api/src/Controller/SectionController.php, new packages/admin-api/tests/Unit/Controller/SectionControllerWiringTest.php
- Patterns to follow: Task 001 wiring test

## Requirements (Test Descriptions)
- [x] `it builds SectionController from the admin-auth module bindings without an app-level registry binding`
- [x] `it injects the same PermissionRegistry instance into SectionController and AdminAuthMiddleware`
- [x] `it fails to build SectionController when no module binds PermissionRegistryInterface` (regression guard showing the binding is what makes it work; expect `Marko\Core\Exceptions\BindingException`)

## Gotchas
- `SectionController` also needs `AdminSectionRegistryInterface` and `GuardInterface`. `AdminAuthMiddleware` needs `GuardInterface` and `AdminConfigInterface`. admin-auth's module.php binds none of these, so the test must `instance()` them (FakeGuard, a real `AdminSectionRegistry` or stub, and a small `AdminConfigInterface` stub). Only the registry comes from module bindings.
- Both classes keep the registry in a `private` property, and `matches()` is stateless, so sharing is not observable through behaviour. Assert identity by reading the `permissionRegistry` property with `ReflectionProperty` on both objects and comparing each to `$container->get(PermissionRegistryInterface::class)` with `toBe`.
- Load admin-auth's module.php via `dirname((new ReflectionClass(PermissionRegistry::class))->getFileName(), 2) . '/module.php'` rather than a hardcoded relative path.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Implemented as specified.
