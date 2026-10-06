# Devil's Advocate Review: config-error-file-context

## Critical (Must fix before building)
None.

## Important (Should fix before building)
1. **Task 001: `ConfigLoadException::__construct()` hardcodes context and suggestion.** The constructor always sets
   `$suggestion = 'Verify the file exists and contains valid PHP syntax...'` and only builds context from
   `$parseError`. A `fromFileFailure()` named constructor that calls `new static(...)` cannot pass the original
   context and suggestion through. The constructor needs optional `string $context = ''` and `string $suggestion = ''`
   parameters (appended after the existing ones so current named-argument callers keep working). When they are
   non-empty they replace the parse-error context and the default suggestion. Also pass `$previous->getCode()` through. Fix applied to task 001.
2. **Task 001: existing callers that check the message must still pass.** `packages/page-cache/tests/Unit/Config/ShippedConfigFileTest.php`
   loads its config file through `ConfigLoader` and expects `toThrow(ConfigException::class, 'Environment variable "PAGE_CACHE_TTL" must be ...')`.
   The wrapper keeps passing only if it stays a `ConfigException` and its message starts with the original message
   followed by ` [file: ...]`. Pest's `toThrow` matches a substring, so that works. The plan should state this message
   shape explicitly and require the page-cache suite to stay green. Fix applied to task 001.

## Minor (Nice to address)
- The DiscoveryCacheException test loads `packages/core/config/discovery.php` from config's tests by a relative
  monorepo path. That works in the monorepo but ties config's tests to the sibling layout. Resolve the path with
  `dirname(__DIR__, 3) . '/core/config/discovery.php'` and document why.
- `DiscoveryEnvironment` reads `$_ENV` then `getenv()`, so tests must restore both, along with `APP_ENV`, which it also reads.
- Nested `ConfigLoadException` wrapping (noted in Architecture Notes): if it ever happens, the message ends up with two `[file: ...]` suffixes. This is acceptable as the plan states.

## Questions for the Team
- Should `ConfigLoadException`'s own existing exceptions (not-found, non-array) be excluded from wrapping? They are
  thrown outside the `require` try block, so they are naturally excluded. No action needed, but worth confirming the catch is scoped only around `require`.
