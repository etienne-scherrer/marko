# Task 001: Bind registry singleton in admin-auth module.php with wiring tests

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add a shared binding of `PermissionRegistryInterface` to `PermissionRegistry` in `packages/admin-auth/module.php`, prove it with a module wiring test, and switch `AdminAuthRouterTest` to use the module binding.

## Context
- Related files: packages/admin-auth/module.php, packages/admin-auth/tests/Feature/AdminAuthRouterTest.php, new packages/admin-auth/tests/Unit/ModuleWiringTest.php
- Patterns to follow: packages/config/tests/Unit/ModuleBindingsTest.php (BindingRegistry + ModuleManifest)

## Requirements (Test Descriptions)
- [x] `it binds PermissionRegistryInterface to PermissionRegistry as a singleton in module.php` (assert `$moduleConfig['singletons'][PermissionRegistryInterface::class] === PermissionRegistry::class` AND that the key is NOT in `bindings`)
- [x] `it resolves the same PermissionRegistry instance each time from the module bindings`
- [x] `it builds AdminAuthMiddleware from the module bindings without an app-level registry binding`
- [x] `it shares registered permissions between separately injected consumers` (two test-local consumer classes each constructor-inject `PermissionRegistryInterface`; register via one, assert visible in the other's `all()`; this fails without `shared`)
- [x] Router test builds its container from the module bindings rather than `instance(PermissionRegistryInterface ...)`

## Gotchas
- Use ONLY the key-value `singletons` array. Do not also list the interface under `bindings`. `BindingRegistry::registerModule()` registers both arrays, and a duplicate from the same module throws `BindingConflictException`.
- `PermissionRegistry::matches()` is stateless, so wildcard-matching tests do not prove sharing. Use `register()`/`all()` to prove it.
- "Without an app-level registry binding" means only the registry. `AdminAuthMiddleware` also needs `GuardInterface` and `AdminConfigInterface`, which admin-auth does not bind. Tests must `instance()` those (e.g., `FakeGuard` from marko/testing plus a small `AdminConfigInterface` stub, as AdminAuthRouterTest already does).
- In AdminAuthRouterTest, keep the guard/config `instance()` calls. Replace only the registry line with `BindingRegistry::registerModule(new ModuleManifest(name: 'marko/admin-auth', version: '1.0.0', bindings: $moduleConfig['bindings'], singletons: $moduleConfig['singletons'] ?? []))`.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Also added a test that a wildcard admin passes AdminAuthMiddleware built from the module container (ticket exit criterion), and a test that a Preference on PermissionRegistryInterface replaces the registry and stays shared (backs the docs in task 004).
