# Plan: Lazy Admin Sections

## Created
2026-10-06

## Status
completed

## Objective
Stop marko/admin's boot callback from building every `#[AdminSection]` class. Boot registers only the section definitions; the registry builds a section through the container the first time `get()` or `all()` needs it, and keeps it for the rest of the process.

## Related Issues
Closes #329

## Discovery Notes
- `packages/admin/module.php` boot resolves each discovered section with `$container->get()` and calls `register()`, so every HTTP request and every CLI command (including `db:migrate` and `discovery:cache`) builds every section.
- `AdminSectionRegistry` stores only built instances; the interface has `register()`, `all()`, `get()`.
- `AdminSectionDefinition` already carries class name, id, label, icon, sort order and permissions.
- Core resolves lazily elsewhere: `CommandRunner` (`ContainerInterface` + definitions) and `EventDispatcher`.
- The container does not wrap constructor exceptions, so the registry must wrap them to name the section class.
- Implementations of the interface outside marko/admin are only test stubs in marko/admin-panel; marko/admin-api tests construct `AdminSectionRegistry` directly.
- `SectionController::index()` filters sections by each section's menu items, so it needs built instances; the optional `definitions()` accessor would not save any building there and is left out.

## Scope

### In Scope
- `AdminSectionRegistryInterface::registerDefinition(AdminSectionDefinition)` (added, `register()` unchanged)
- `AdminSectionRegistry` takes `ContainerInterface`, stores definitions, builds lazily, caches instances, sorts by the instance's `getSortOrder()` (unchanged)
- Boot-time checks without instantiation: class exists, implements `AdminSectionInterface`, duplicate ids
- First-resolution checks: constructor failure (wrapped, names class and id), id mismatch
- `module.php` boot registers definitions only
- Update stubs/tests in admin-panel and admin-api
- Docs: admin.md, roadrunner-state-leaks.md, build-an-admin-panel tutorial

### Out of Scope
- Resolving sections during `discovery:cache` (would put section constructors back on the build's critical path)
- `definitions()` accessor / changing `SectionController::index()`

## Success Criteria
- [x] Boot builds no section; a throwing constructor does not break boot
- [x] First `get()`/`all()` builds once and reuses; a throwing constructor fails that call naming the class
- [x] Duplicate id / missing class / wrong interface fail at registration (boot); id mismatch fails at first resolution
- [x] Manual `register()` still works
- [x] All tests passing, `composer ci` green
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Lazy registry: registerDefinition, lazy build, sorting, errors | - | completed |
| 002 | Boot registers definitions only (module.php + wiring tests) | 001 | completed |
| 003 | Update dependent packages' stubs and tests (admin-panel, admin-api) | 001 | completed |
| 004 | Docs: admin.md, roadrunner-state-leaks.md, tutorial | 001, 002 | completed |

## Architecture Notes
- Registry depends on `ContainerInterface` (like `CommandRunner`), not closures, so it stays a plain shared singleton.
- One ordered map of entries keyed by id keeps registration order stable for equal sort orders.
- `all()` builds every entry, then sorts all instances by `getSortOrder()` (unchanged behavior; the attribute `sortOrder` defaults to 0 and is not authoritative).
- Constructor failures are wrapped in `AdminException::sectionBuildFailed($className, $id, $previous)`. Failed builds are not cached.

## Risks & Mitigations
- Public interface change breaks third-party implementations: additive method only; documented in PR.
- Constructor change on `AdminSectionRegistry`: only constructed via container in production; tests updated.
