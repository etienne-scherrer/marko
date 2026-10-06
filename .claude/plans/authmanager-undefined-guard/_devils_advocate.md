# Devil's Advocate Review: authmanager-undefined-guard

## Critical (Must fix before building)
None. `CanConfigurationValidator::validate()` already wraps any `Throwable` from `AuthManager::guard()` in `cannotBuildForCan()`, `useGuard()` writes the cache that `guard()` checks first, and no production code (admin-auth, admin-api, testing `TestClient`) builds a guard from a name that the default config leaves undefined.

## Important (Should fix before building)

1. **Task 001: the `useGuard` regression test already exists.** `packages/authentication/tests/Unit/AuthManagerTest.php:548` already has `it('returns a guard put in place with useGuard under an unconfigured name', ...)` inside `describe('useGuard')`. A TDD worker following the requirement list would either add a duplicate or find that the "red" test is already green. Fix: drop it from the new-test list and require that the existing test stays green.

2. **Task 001: a driver that is not a string slips through.** If the check only tests `array_key_exists('driver', ...)`, then `'driver' => null`, `''` or a non-string value still reaches `createGuard(string $driver, ...)`. With strict_types that is a `TypeError`, or `unknownGuardDriver('')`, not a helpful error. The value is `mixed` (`AuthConfig::guards()` returns `array<string, array<string, mixed>>`), so PHPStan will also complain about passing it to a `string` parameter. Fix: treat any missing, non-string or empty `driver` as `missingGuardDriver`, and add a test for it.

3. **Task 001: the old test must be replaced, not left next to the new ones.** `test('it throws for unknown guard', ...)` (AuthManagerTest.php:354) asserts a `SessionGuard` for `'nonexistent'`. It must be deleted or rewritten, or the suite will contradict itself. This is already in the plan's Discovery Notes but not in the task file, so it is now written into the task.

4. **Task 002: the unit tests must use a real `AuthManager`.** The existing `CanConfigurationValidatorTest` uses `ValidatorAuthManager`, a stub whose `guard()` runs a closure. If the new unit tests reuse that stub to throw `AuthException::undefinedGuard`, they only repeat the existing generic wrapping test (`throws AuthorizationConfigurationException naming the guard...`) and prove nothing about #294. Fix: build a real `AuthManager` (FakeConfigRepository / FakeSession / FakeUserProvider / FakeEventDispatcher / FakeCookieJar / `RememberTokenManager(new FakeClock())`) with `authentication.guards` that leaves out the requested name. Then assert `getPrevious()` is an `AuthException` and the message names the guard.

5. **Task 002: the feature-test option has to be wired carefully.** In `canBootProject()`, `$guard` is set by the `token` and `customDriver` options, and the app `config/authentication.php` is merged with the package defaults, which already define the `session` and `token` guards. The new `defaultGuard` option must override `$guard` last, and the test must use a name that is in neither the fixture's guards nor the package defaults (e.g. `'sesion'`). Run it as a live boot like the existing tests.

## Minor (Nice to address)
- Guard entries that are not arrays (e.g. `'web' => 'session'`) would hit `TypeError` on array access. This is rare and could also be reported as `missingGuardDriver`.
- `undefinedGuard` lists configured guard names. A "did you mean" suggestion (levenshtein) would help with typos, but it is optional.
- Task 003: also check `docs/guides/authentication.md` and the testing docs on `actingAs()`. They should say that `actingAs()` with a custom guard name still works without config.

## Questions for the Team
- Should `actingAs($user, 'unconfigured')` keep working silently? The plan keeps it (via `useGuard`). Confirm this is intended, since it is the one remaining place where an unconfigured name works.
