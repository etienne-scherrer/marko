# Plan: Admin Permission Registry Binding

## Created
2026-10-06

## Status
completed

## Objective
Make `marko/admin-auth` bind `PermissionRegistryInterface` to `PermissionRegistry` as a shared singleton so `AdminAuthMiddleware` and admin-api's `SectionController` build on a stock install, and make `PermissionDiscovery` fail loudly.

## Related Issues
Closes #291

## Discovery Notes
- `packages/admin-auth/module.php` binds config, three repositories and the user provider, but not the registry. No other module binds it.
- `AdminAuthMiddleware` and `SectionController` both constructor-inject `PermissionRegistryInterface`; the container cannot autowire an unbound interface.
- `PermissionRegistry` holds state in an instance array, so the binding must be shared.
- `BindingRegistry::registerModule()` supports key-value `singletons` (bind + share in one step), which is the documented form for a simple shared interface → class mapping.
- `AdminAuthRouterTest` binds the registry by hand (`$container->instance(...)`).
- `PermissionDiscovery::discoverFromClass()` swallows `AdminException|ReflectionException` silently. Removing the catch is cheap and fits "loud errors".

## Scope

### In Scope
- Singleton binding in `packages/admin-auth/module.php`
- Module wiring test in admin-auth (same instance, middleware resolves, wildcard matching through injected registry)
- Wiring test in admin-api that `SectionController` resolves with only module bindings
- Router test uses the module's binding instead of a hand-made instance
- `PermissionDiscovery` stops swallowing exceptions
- Docs: "Registering Permissions" notes singleton + Preference replacement

### Out of Scope
- Wiring `PermissionDiscovery` into boot (no caller exists; separate concern)
- admin-api error shape (#292)

## Success Criteria
- [x] module.php binds `PermissionRegistryInterface` to `PermissionRegistry` as a singleton
- [x] Resolving the interface twice returns the same instance
- [x] `AdminAuthMiddleware` and `SectionController` resolve without app-level binding
- [x] Permission registered through one injected consumer is visible via `all()` to another (proves sharing)
- [x] Docs updated
- [x] All tests passing, `composer ci` green
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Bind registry singleton in admin-auth module.php with wiring tests | - | completed |
| 002 | admin-api SectionController resolves from module bindings | 001 | completed |
| 003 | PermissionDiscovery stops swallowing exceptions | - | completed |
| 004 | Docs: Registering Permissions singleton note | 001 | completed |

## Architecture Notes
- Use key-value `singletons` form (`PermissionRegistryInterface::class => PermissionRegistry::class`), equivalent to binding + list singleton; keeps module.php minimal.
- Do not also list the interface under `bindings`. The same module binding it twice throws `BindingConflictException`.
- Preference replacement must target `PermissionRegistryInterface`, not `PermissionRegistry`. The container does not re-check preferences after following a binding.
- `PermissionRegistry::matches()` is stateless. Sharing must be proven via `register()`/`all()` or instance identity, not via wildcard checks.
- Guard, admin config and section registry are not bound by admin-auth. Wiring tests `instance()` them, and only the registry comes from module bindings.
- Tests build a `Container` and register the module via `BindingRegistry::registerModule(new ModuleManifest(...))`, matching `packages/config/tests/Unit/ModuleBindingsTest.php`.

## Risks & Mitigations
- Another module binding the same interface would conflict: none exist (grep verified).
- Removing the catch in PermissionDiscovery could break callers: no non-test callers exist.
