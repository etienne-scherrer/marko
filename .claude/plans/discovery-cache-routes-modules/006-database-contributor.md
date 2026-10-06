# Task 006: Database entity contributor

**Status**: completed
**Depends on**: 004
**Retry count**: 0

## Description
Add `EntityCacheContributor` (key `entities`) returning the discovered entity class list; declare it in database's module.php; the boot callback uses the cached list when present instead of scanning.

## Context
- Do NOT edit `Application.php` (owned by 004). The boot closure type-hints `CachedDiscovery` (autowires to an uncached instance when not bound) and calls `section('entities')`; only scans when it returns null.
- The contributor ignores `$modules` and uses `EntityDiscovery` over `ProjectPaths`, matching the current boot callback.

## Requirements (Test Descriptions)
- [x] `it compiles the discovered entity classes`
- [x] `it links extenders from the cached entity list without scanning`
- [x] `it declares the entity contributor in module.php`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
