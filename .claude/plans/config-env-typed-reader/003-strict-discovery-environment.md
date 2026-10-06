# Task 003: Strict DiscoveryEnvironment::enabled(); discovery.php delegates

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`marko/core` cannot depend on `marko/config` (config requires core), so `core/config/discovery.php` delegates to `DiscoveryEnvironment` (the boot gate's own reader) instead of re-parsing env with a hand-rolled list. `enabled()` accepts the `Env::bool` token set plus the documented empty = disabled, and throws `DiscoveryCacheException` on anything else.

## Context
- Related files: packages/core/src/Discovery/DiscoveryEnvironment.php, packages/core/config/discovery.php, packages/core/src/Exceptions/DiscoveryCacheException.php, packages/core/tests/Unit/Discovery/DiscoveryEnvironmentTest.php

## Requirements (Test Descriptions)
- [x] `it treats on/yes/true/1 as enabled and off/no/false/0/empty as disabled` (case-insensitive, trimmed, matching `Env::bool`)
- [x] `it throws DiscoveryCacheException for an unrecognised DISCOVERY_CACHE_ENABLED value` (e.g. `'enabled'`, `'ture'`)
- [x] `it builds config/discovery.php from DiscoveryEnvironment`

## Implementation Notes (from review)
- The existing test `returns enabled() true for any other present DISCOVERY_CACHE_ENABLED value` (DiscoveryEnvironmentTest.php ~line 58) asserts `'enabled'` -> true. Rewrite it: drop `'enabled'` and move it to the throwing test.
- Add a named factory such as `DiscoveryCacheException::invalidEnabledValue(string $value)`. Its message names `DISCOVERY_CACHE_ENABLED` and the value; its suggestion lists the accepted forms.
- Keep `core/src` free of any `Marko\Config` reference (an existing structure test in DiscoveryEnvironmentTest enforces this). Hardcode the token set in core; do not import `Env`.
- `discovery.php` becomes `$env = new \Marko\Core\Discovery\DiscoveryEnvironment(); return ['enabled' => $env->enabled(), 'environment' => $env->environment(), 'cache_path' => $env->cachePath()];`. Note: `environment` now follows `AppEnvironment` (MARKO_ENV takes precedence; trimmed and lowercased). This is intentional because it matches the boot gate. The default stays `'production'`.
- `core/src` is under PHPStan level 6; keep `composer phpstan` at zero errors.

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
