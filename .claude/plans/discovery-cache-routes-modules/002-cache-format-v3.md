# Task 002: Cache format v3 — modules, global middleware, sections, fingerprint, stale detection

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Bump `DiscoveryCache::CACHE_VERSION` to 3. `write()` additionally stores the ordered module list (as CachedModule records with project-relative paths), global middleware, contributor sections and a fingerprint. `load()` validates them, rebuilds absolute paths and throws `DiscoveryCacheException::stale()` when the current fingerprint differs. Add `DiscoveryFingerprint`.

## Context
- Related files: packages/core/src/Discovery/DiscoveryCache.php, packages/core/src/Exceptions/DiscoveryCacheException.php
- Fingerprint: hash of vendor/composer/installed.json (marker when absent) + sorted project-relative dirs holding composer.json under modules/ (recursive, stop at module) and app/ (one level) + a hash of each of those composer.json files' contents; never json_decode.
- Both `write()` and `load()` take paths from `ProjectPaths` (base/vendor/modules/app) — never from Application's constructor args — so command-side writes and boot-side loads agree.
- Relativize against `ProjectPaths->base`; a module path not under base is stored absolute (never `../`). Unit tests use temp dirs and `modulesPath: ''`.
- Payload contract (shared with task 003): existing four keys plus `modules: list<CachedModule>` (load order, incl. after/before/globalMiddleware snapshot), `globalMiddleware: list<class-string>`, `sections: array<string, array<mixed>>`. `load()` returns the same keys with absolute paths restored.
- Add `DiscoveryCacheException::stale(string $path, string $reason)` and `::missingSection(string $key)` (used by CachedDiscovery in 004), each with a suggestion to run `marko discovery:cache`.

## Requirements (Test Descriptions)
- [x] `it round-trips the module list in order with paths relative to the project root`
- [x] `it round-trips global middleware and contributor sections`
- [x] `it throws a stale DiscoveryCacheException when installed.json changes after the cache is written`
- [x] `it throws a stale DiscoveryCacheException when a module directory is added under app or modules`
- [x] `it throws versionMismatch for a version 2 cache file`
- [x] `it throws malformed when a module record or section is invalid`
- [x] `it computes the same fingerprint without parsing any composer.json`
- [x] `it throws a stale DiscoveryCacheException when an app or modules composer.json changes`
- [x] `it stores module paths outside the project base as absolute paths`

## Acceptance Criteria
- All requirements have passing tests; existing DiscoveryCache tests updated to the new payload

## Implementation Notes
(Left blank - filled in by programmer during implementation)
