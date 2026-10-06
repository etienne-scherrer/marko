# Task 008: Integration test and docs

**Status**: completed
**Depends on**: 005, 006, 007
**Retry count**: 0

## Description
Replace the #173 todo in KnownGapsTest with a real integration test (routes identical and same responses warm vs cold). Update core.md (discovery cache now covers modules/routes/middleware/sections, stale detection, contributors, Deploying to production), routing.md, database.md and READMEs if they mention the cache.

## Context
- Keep `->issue(173)` on the flipped test — `HarnessTest` checks every hand-off ticket is referenced.
- Set `APP_ENV=production` and a temp `DISCOVERY_CACHE_PATH`; restore `$_ENV`/`putenv` afterwards. Build the cache from a live (cache-bypassed) boot via DiscoveryCompiler + DiscoveryCache.
- Docs: state that module.php edits to `enabled`/`sequence`/`globalMiddleware` throw stale, enabling a previously disabled module is NOT detected, and `discovery:cache` must run on every deploy.

## Requirements (Test Descriptions)
- [x] `it routes identically with the discovery cache warm and cold`

## Acceptance Criteria
- Docs follow docs/DOCS-STANDARDS.md

## Implementation Notes
(Left blank - filled in by programmer during implementation)
