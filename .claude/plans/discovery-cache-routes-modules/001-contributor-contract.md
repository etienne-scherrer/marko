# Task 001: Contributor contract, manifest `discovery` key, cached manifest parsing

**Status**: pending
**Depends on**: none
**Retry count**: 0

## Description
Add `Marko\Core\Discovery\DiscoveryCacheContributorInterface` (`key(): string`, `compile(array $modules): array`), a `discovery` field on `ModuleManifest` parsed from module.php's `'discovery'` key, a `CachedModule` value object (composer-derived manifest fields) and `ManifestParser::parseCached(CachedModule)` which requires module.php without reading composer.json.

## Context
- Related files: packages/core/src/Module/ManifestParser.php, ModuleManifest.php, ModuleDiscovery.php (withPathAndSource copies fields)
- Patterns to follow: how `globalMiddleware` is parsed
- ALREADY PRESENT in the worktree (build on them, do not recreate): `packages/core/src/Discovery/DiscoveryCacheContributorInterface.php`, `packages/core/src/Module/CachedModule.php`, `packages/core/tests/Unit/Module/ManifestParserCachedTest.php`.
- Extend `CachedModule` with module.php snapshot fields `after`, `before`, `globalMiddleware` (default `[]`, copied in `fromManifest()`); `parseCached()` ignores them (they come from live module.php) — task 004 compares them for stale detection.

## Requirements (Test Descriptions)
- [ ] `it parses the discovery contributor list from module.php`
- [ ] `it defaults the discovery contributor list to empty`
- [ ] `it builds a manifest from a cached module without reading composer.json`
- [ ] `it keeps module.php closures live when building a manifest from a cached module`
- [ ] `it keeps the discovery list when ModuleDiscovery sets path and source`
- [ ] `it snapshots sequence and global middleware when building a CachedModule from a manifest`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
