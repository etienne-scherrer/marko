# Task 001: AuthManager throws for undefined guard and missing driver

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Replace the silent `session` fallback in `AuthManager::guard()` with loud errors: an undefined guard name throws `AuthException::undefinedGuard()`, an entry without a `driver` key throws `AuthException::missingGuardDriver()`.

## Context
- Related files: `packages/authentication/src/AuthManager.php`, `packages/authentication/src/Exceptions/AuthException.php`, `packages/authentication/tests/Unit/AuthManagerTest.php`, `packages/authentication/tests/Unit/Exceptions/` (if present)
- Patterns to follow: `AuthException::unknownGuardDriver()` (message/context/suggestion)

## Requirements (Test Descriptions)
- [x] `it throws for a guard name missing from authentication.guards`
- [x] `it lists the configured guards when the guard name is undefined`
- [x] `it throws for a configured guard with no driver`
- [x] `it throws missingGuardDriver when the driver is null, empty or not a string`
- [x] `it suggests adding the guard or fixing the default guard name`

## Implementation Constraints
- Delete or rewrite the existing `test('it throws for unknown guard', ...)` (AuthManagerTest.php ~line 354). It currently asserts a `SessionGuard` for `'nonexistent'` and must assert `AuthException` instead.
- The `useGuard` regression test ALREADY EXISTS: `describe('useGuard')` -> `it('returns a guard put in place with useGuard under an unconfigured name')` (AuthManagerTest.php ~line 548). Do NOT add a duplicate. It must stay green unchanged.
- Validate the driver as a non-empty string before calling `createGuard(string $driver, ...)`: the config value is `mixed`, so a missing, null, empty or non-string `driver` throws `missingGuardDriver` (this also keeps PHPStan clean).
- Run the check after the `$this->guards` cache lookup so `useGuard()` names are never checked against config.
- Pass `array_keys($guardsConfig)` (as strings) to `undefinedGuard()`.

## Acceptance Criteria
- All requirements have passing tests
- The existing useGuard test for an unconfigured name still passes
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
(Left blank - filled in by programmer during implementation)
