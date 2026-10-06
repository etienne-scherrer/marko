# Task 001: Deprecate env() with E_USER_DEPRECATED

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Mark the global `env()` helper `@deprecated` and make every call emit `E_USER_DEPRECATED` with the variable name and the `Marko\Config\Env` method that replaces it, while keeping its return values unchanged.

## Context
- Related files: `packages/env/src/functions.php`, `packages/env/tests/Unit/EnvFunctionTest.php`
- `marko/env` must not depend on `marko/config`; name the class as a string.

## Requirements (Test Descriptions)
- [x] `it emits E_USER_DEPRECATED when called`
- [x] `it names the variable and Marko\Config\Env in the deprecation message`
- [x] `it suggests Env::bool for a boolean default`
- [x] `it suggests Env::int for an integer default`
- [x] `it suggests Env::float for a float default`
- [x] `it suggests Env::string for a string default`
- [x] `it suggests Env::list for an array default`
- [x] `it suggests Env::nullableString when no default is given`
- [x] `it says env() will be removed in 1.0`
- [x] `it emits even when the variable is unset and the default is returned`
- [x] `it does not change return values` (existing coercion/default tests still pass with deprecations captured)
- [x] scoped error handler captures only `E_USER_DEPRECATED` (returns `false` for other levels) and is removed with `restore_error_handler()` in `afterEach`, so no test is flagged risky

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
- `trigger_error()` runs first in `env()`, so unset variables that return the default also warn (covered by the tests that call `env()` on unset names).
- The replacement is an inline `match (true)` on the default type that also renders the default (`Env::int('DB_PORT', 3306)`); arrays render as `[...]`. No second global function.
- Message: `env('{KEY}') is deprecated and will be removed in Marko 1.0. Read it in a config file with Marko\Config\Env instead: Env::{method}('{KEY}', {default}). Env throws on a value it cannot parse and treats an empty value as unset.`
- `#[\Deprecated]` not used (fixed message per call, cannot name the variable; would double the notice).
- Tests: file-level `beforeEach` installs a handler scoped to `E_USER_DEPRECATED` (the level mask passes other levels through) and `afterEach` restores it.
