# Task 007: Feature test through the real router and discovery:cache output

**Status**: completed
**Depends on**: 004, 005
**Retry count**: 0

## Description
Boot admin and admin-auth from their real module.php files over a fixture app module that declares a section only by attribute, then request `GET /admin/api/v1/sections` through the real Router. Prove `discovery:cache` lists the `admin_sections` section.

## Context
- Related files: packages/admin-api/tests/Feature/AdminApiErrorShapeTest.php (router pattern), packages/core/src/Commands/DiscoveryCacheCommand.php, packages/core/tests/Command/DiscoveryCacheCommandTest.php (temp project pattern)
- Test file: packages/admin-api/tests/Feature/AdminSectionBootTest.php (admin-api already requires admin, admin-auth and routing).
- Setup: build `ModuleManifest`s from the real packages/admin/module.php and packages/admin-auth/module.php arrays (bindings, singletons, discovery, boot) via `BindingRegistry::registerModule()`, plus a fixture app module whose `path` contains `src/` with a section class declared only by `#[AdminSection]` (no menu items, so no permission filtering). Bind `ModuleRepositoryInterface`, `CachedDiscovery` (uncached), `AdminConfigInterface`, and a `FakeGuard` with a logged-in `AdminUser` (a guest gets 401 from `AdminAuthMiddleware`). Call each module's `boot` via `$container->call()` in module order, then route `GET /admin/api/v1/sections` with `middleware: [AdminAuthMiddleware::class]` through a real `Router`.
- discovery:cache: run `DiscoveryCompiler`/`DiscoveryCacheCommand` against a temp project path (scratch dir, cleaned up) with the admin module manifest's `discovery` contributor, and assert the output's `sections:` line contains `admin_sections`.

## Requirements (Test Descriptions)
- [x] `it returns a section declared only by attribute from GET /admin/api/v1/sections`
- [x] `it lists the admin sections section in the discovery:cache output`

## Acceptance Criteria
- Runs without database or Redis

## Implementation Notes
