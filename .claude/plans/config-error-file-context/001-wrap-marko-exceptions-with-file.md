# Task 001: Wrap Marko exceptions from config files with the file path

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `ConfigLoadException::fromFileFailure()` and catch `MarkoException` in `ConfigLoader::load()` while the config file runs, rethrowing an exception whose message names the file and which keeps the original context, suggestion and previous.

## Context
- Related files: packages/config/src/ConfigLoader.php, packages/config/src/Exceptions/ConfigLoadException.php, packages/config/tests/Unit/ConfigLoaderTest.php, packages/config/tests/Unit/fixtures/*, packages/core/config/discovery.php
- Patterns to follow: existing `ParseError` wrapping in `ConfigLoader::load()`; named constructors such as `DiscoveryCacheException::invalidEnabledValue()`
- `ConfigLoadException::__construct()` currently hardcodes the suggestion and derives context only from `$parseError`. Add optional trailing `string $context = ''` and `string $suggestion = ''` constructor params (non-empty values override the parse-error context / default suggestion) so `fromFileFailure()` can pass the original's `getContext()`, `getSuggestion()`, `getCode()` and `previous` through. Existing named-argument callers must keep working.
- Wrapper message shape: `{original message} [file: {path}]` (via the existing `$message` param), class stays a `ConfigException` subclass. Scope the new `catch (MarkoException)` to the `require` only, next to the `ParseError` catch.
- Existing consumer to keep green: `packages/page-cache/tests/Unit/Config/ShippedConfigFileTest.php` expects `toThrow(ConfigException::class, 'Environment variable "PAGE_CACHE_TTL" ...')` through `ConfigLoader` — run it.
- `DiscoveryEnvironment` reads `$_ENV` then `getenv()` (and `APP_ENV`); restore both in `finally`.

## Requirements (Test Descriptions)
- [x] `it names the config file when Env rejects an integer while loading`
- [x] `it keeps the Env context and suggestion when wrapping the rejection`
- [x] `it keeps the original Env exception as the previous exception`
- [x] `it names the config file when Env rejects a boolean while loading`
- [x] `it names the config file when the discovery config rejects DISCOVERY_CACHE_ENABLED`
- [x] `it lets non-Marko errors thrown by a config file pass through unchanged`
- [x] `it leaves Env exceptions thrown outside the loader unchanged`
- [x] `it keeps the default suggestion and parse-error context for existing ConfigLoadException callers`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
Added optional trailing `$context`/`$suggestion` constructor params and `fromFileFailure()`; `ConfigLoader::load()` catches `MarkoException` around the `require` only. Non-Marko throwables pass through. Fixtures: env-invalid-int.php, env-invalid-bool.php, throws-runtime-exception.php; the discovery case loads packages/core/config/discovery.php.
