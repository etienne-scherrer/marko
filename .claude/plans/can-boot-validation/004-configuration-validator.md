# Task 004: CanConfigurationValidator

**Status**: completed
**Depends on**: 002, 003
**Retry count**: 0

## Description
Finds #[Can] routes and, if any, builds the authorization guard and the Gate's collaborators once; wraps failures in AuthorizationConfigurationException.

IMPORTANT: never resolve the `GateInterface` singleton. Its binding captures `AuthManager::guard()` at build time; building it at boot pins the boot-time guard, so `TestClient::actingAs()` (which calls `AuthManager::useGuard()` after boot) would authenticate in the middleware but be denied by the Gate. Build the guard via `AuthManager::guard(AuthorizationConfig::defaultGuard())` (useGuard replaces that cache entry) and resolve `PolicyRegistry` and `AuthorizationConfig` to prove the Gate's collaborators build.

## Context
- Related files: packages/authorization/module.php (Gate binding), AuthManager::guard()
- Patterns to follow: existing authorization package classes; DiscoveryCacheContributorInterface implementations

## Requirements (Test Descriptions)
- [x] `it builds nothing when no route uses Can`
- [x] `it builds the authorization guard and the Gate collaborators when a route uses Can`
- [x] `it never resolves the GateInterface singleton`
- [x] `it throws AuthorizationConfigurationException naming the guard when the guard cannot be built`
- [x] `it names the authentication default guard when authorization.default_guard is null`
- [x] `it throws when PolicyRegistry or AuthorizationConfig cannot be built`
- [x] `it returns the Can route keys`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards
- No decrease in test coverage

## Implementation Notes
Resolves AuthorizationConfig, AuthConfig (for the name), AuthManager::guard() and PolicyRegistry. Never resolves GateInterface (actingAs regression). Catches Throwable and wraps it.
