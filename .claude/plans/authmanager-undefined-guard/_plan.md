# Plan: AuthManager Undefined Guard

## Created
2026-10-06

## Status
completed

## Objective
Make `AuthManager::guard()` fail loudly for a guard name missing from `authentication.guards` or a guard entry with no `driver`, instead of silently building a `SessionGuard`, so the `#[Can]` boot check also catches misspelt guard names.

## Related Issues
Closes #294

## Discovery Notes
- `AuthManager::guard()` (`packages/authentication/src/AuthManager.php`) uses `$guardsConfig[$name] ?? []` and `$guardConfig['driver'] ?? 'session'`, so any unknown name or driverless entry becomes a session guard.
- `useGuard()` writes to the same `$guards` cache that `guard()` checks first, so a guard put in place by name (the testing client's `actingAs()`) is returned before any config lookup. No change needed there.
- `AuthException` already has `unknownGuardDriver`, `tokenDriverNotInstalled`, `guardNameMismatch` factories with message/context/suggestion; the new factories follow the same shape.
- `CanConfigurationValidator` (`packages/authorization/src/Routing/CanConfigurationValidator.php`) resolves the guard through `AuthManager::guard($guard)` inside a `try` that wraps any `Throwable` in `AuthorizationConfigurationException::cannotBuildForCan()`. Once `guard()` throws for unknown names, the boot check surfaces them with no validator change.
- Live boot coverage lives in `packages/authorization/tests/Feature/CanBootValidationTest.php` (`canBootProject()` builds a temp project); it needs a `defaultGuard` option to configure a default guard name missing from `authentication.guards`.
- `AuthManagerTest.php` has `test('it throws for unknown guard', ...)` pinning the silent fallback; it is rewritten.

## Scope

### In Scope
- `AuthException::undefinedGuard(string $guard, array $configuredGuards)` and `AuthException::missingGuardDriver(string $guard)`
- `AuthManager::guard()` throws them
- Rewrite the misleading unit test; keep the existing `useGuard()` regression test for an unconfigured name (AuthManagerTest.php `describe('useGuard')`) green. Do not duplicate it
- A missing, null, empty or non-string `driver` all throw `missingGuardDriver`
- Authorization: unit test on `CanConfigurationValidator` and live boot feature test for a misspelt default guard
- Docs: `authentication.md` Guards and Guard Drivers sections

### Out of Scope
- Changes to `useGuard()` or the `GuardDriverRegistry`
- A compatibility shim for the old fallback (pre-1.0)

## Success Criteria
- [x] `AuthManager::guard('nonexistent')` throws `AuthException` naming the guard and listing configured guards
- [x] A configured guard with no `driver` key throws `AuthException` naming the guard
- [x] A guard set with `useGuard()` under an unconfigured name is still returned
- [x] Live boot with a `#[Can]` route and a default guard missing from `authentication.guards` fails with `AuthorizationConfigurationException` wrapping the new `AuthException`
- [x] Docs updated
- [x] All tests passing (`composer ci` green)
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | AuthManager throws for undefined guard and missing driver | - | completed |
| 002 | Authorization boot check surfaces undefined guard names | 001 | completed |
| 003 | Document the new errors | 001 | completed |

## Architecture Notes
- Exceptions follow the existing `AuthException` named-constructor pattern (message, context, suggestion).
- The check sits in `guard()` after the cache lookup, so `useGuard()` keeps working for unconfigured names.

## Risks & Mitigations
- Other tests or fixtures relying on the silent fallback: run the authentication, authentication-token, authorization, testing, admin-auth and roadrunner suites and fix any fixture missing a guard entry.
