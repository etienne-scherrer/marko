# Task 005: errors-simple Environment delegates to AppEnvironment

**Status**: completed
**Depends on**: 002
**Retry count**: 0

## Description
`Marko\ErrorsSimple\Environment::isProduction()`/`isDevelopment()` delegate to `AppEnvironment`, keeping the injectable `$envVars` constructor argument used by tests.

## Context
- Related files: packages/errors-simple/src/Environment.php, packages/errors-simple/tests/Unit/EnvironmentTest.php
- Behaviour change: an unset environment is now production (fail safe), not development.
- Implementation: `isProduction()` returns `(new AppEnvironment($this->envVars))->isProduction()`. `isDevelopment()` stays `!isProduction()`, so errors-simple keeps a binary model and `staging` still shows detailed errors as before. Delete the private `getEnvVar()`.
- Per the task 002 contract, injected `$envVars` is now the only source (no `getenv` fallback for missing keys).
- The existing test `defaults to development when no environment variable set` (tests/Unit/EnvironmentTest.php:35) MUST be replaced by `it treats an unset environment as production`. It is the one existing test that is expected to change.

## Requirements (Test Descriptions)
- [x] `it treats an unset environment as production`
- [x] `it treats local and dev as development`
- [x] `it prefers MARKO_ENV over APP_ENV`

## Acceptance Criteria
- All requirements have passing tests; all other existing errors-simple tests still pass

## Implementation Notes
Deviated from the review's suggestion: `isDevelopment()` delegates to `AppEnvironment::isDevelopment()` and `isProduction()` is `!isDevelopment()`. The errors-simple docs already promised "any other value (or no value) is treated as production"; the old code showed details for `staging` and for an unset env, contradicting them. This keeps the binary show/hide-details model while failing safe.
