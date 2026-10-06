# Task 003: AdminSectionCacheContributor and DiscoveredAdminSections

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
`AdminSectionCacheContributor` (key `admin_sections`) compiles every section definition into scalars for the discovery cache and hydrates them back, failing with `DiscoveryCacheException::malformedSection` on a bad record. `DiscoveredAdminSections` returns the definitions for this boot, from the cache when the boot used it and from a scan of the enabled modules otherwise, computed once and memoised so both admin boots share one parse.

## Context
- Related files: packages/database/src/Entity/EntityCacheContributor.php, packages/routing/src/RouteCacheContributor.php, packages/core/src/Discovery/CachedDiscovery.php
- Patterns to follow: RouteCacheContributor::hydrate field validation

## Requirements (Test Descriptions)
- [x] `it compiles every section definition into exportable records`
- [x] `it hydrates compiled records back into equal definitions`
- [x] `it throws a malformed section error for an invalid cached record`
- [x] `it returns the cached definitions without scanning when the boot used the cache`
- [x] `it scans the enabled modules when the boot did not use the cache`
- [x] `it parses the sections only once`

## Interface Contract (tasks 004/005 build against this)
- `Marko\Admin\Discovery\AdminSectionCacheContributor implements DiscoveryCacheContributorInterface` (readonly ok), `public const string KEY = 'admin_sections'`.
  - `compile(array $modules): array` calls `AdminSectionDiscovery::discoverAll($modules)` directly with the `$modules` argument from `DiscoveryCompiler` — it must NOT go through `DiscoveredAdminSections` or `ModuleRepositoryInterface`.
  - Record shape (list): `['className' => string, 'id' => string, 'label' => string, 'icon' => string, 'sortOrder' => int, 'permissions' => list<['id' => string, 'label' => string]>]`.
  - `hydrate(array $section): array<int, AdminSectionDefinition>` validates every field (string/int/list types, like `RouteCacheContributor`) and throws `DiscoveryCacheException::malformedSection(self::KEY, "section $index.$field ...")`.
- `Marko\Admin\Discovery\DiscoveredAdminSections` (NOT readonly — holds a `?array` memo; not final):
  - constructor `(CachedDiscovery $cachedDiscovery, ModuleRepositoryInterface $modules, AdminSectionDiscovery $discovery, AdminSectionCacheContributor $contributor)`
  - `all(): array<int, AdminSectionDefinition>` — `$cachedDiscovery->section(KEY)` hydrated when non-null, else `discoverAll($modules->all())`; memoised.
  - Declared as a singleton in packages/admin/module.php by task 004 (not this task).

## Requirements (additional)
- [x] `it throws a malformed section error when a permission record is not an id/label pair`

## Acceptance Criteria
- Compiled data contains only scalars and arrays

## Implementation Notes
