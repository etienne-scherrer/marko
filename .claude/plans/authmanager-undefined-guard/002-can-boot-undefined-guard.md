# Task 002: Authorization boot check surfaces undefined guard names

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Prove that the `#[Can]` boot check now fails when the guard it resolves (`authorization.default_guard` or `authentication.default.guard`) is missing from `authentication.guards`, wrapping the new `AuthException`.

## Context
- Related files: `packages/authorization/src/Routing/CanConfigurationValidator.php` (no change expected), `packages/authorization/tests/Unit/Routing/CanConfigurationValidatorTest.php`, `packages/authorization/tests/Feature/CanBootValidationTest.php`
- Patterns to follow: existing `canBootProject()` options in the feature test

## Requirements (Test Descriptions)
- [x] `it throws AuthorizationConfigurationException wrapping AuthException when the default guard is not configured` (unit, validator)
- [x] `it throws when authorization.default_guard names a guard missing from authentication.guards` (unit, validator)
- [x] `fails a live boot when a Can route exists and the default guard is not configured` (feature, live boot)

## Implementation Constraints
- Unit tests: do NOT use the `ValidatorAuthManager` closure stub to throw `AuthException` by hand. That only repeats the existing generic wrapping test. Build a REAL `AuthManager` (FakeConfigRepository, FakeSession, FakeUserProvider, FakeEventDispatcher, FakeCookieJar, `new RememberTokenManager(new FakeClock())`), put it in the container with `$container->instance(AuthManager::class, ...)`, and configure `authentication.guards` without the requested name. Assert `getPrevious()` is an `AuthException` and the message names the guard.
- Feature test: add a `defaultGuard` option to `canBootProject()`. Apply it LAST, after the `token`/`customDriver` options that also set `$guard`. Use a name that is in neither the fixture's guards nor the package defaults (`session`, `token`), e.g. `'sesion'`, because the app config is merged with the package's `config/authentication.php`.
- Existing tests in `CanBootValidationTest.php` and `CanConfigurationValidatorTest.php` must stay green.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
