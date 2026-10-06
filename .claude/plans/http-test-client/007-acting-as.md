# Task 007: actingAs

**Status**: completed
**Depends on**: 002, 005
**Retry count**: 0

## Description
`actingAs($user, ?string $guard = null)` puts a `FakeGuard` holding the user in place via `AuthManager::useGuard()` (and the `GuardInterface` singleton for the default guard). No session write.

## Requirements (Test Descriptions)
- [x] protected route returns 200 with a user
- [x] protected route redirects without one
- [x] no real session write
- [x] named guard support

- [x] actingAs before any request boots the app and still applies to the first request
- [x] actingAs on one client does not affect a second client

## Implementation Notes
- Resolve the default guard name with `AuthConfig::defaultGuard()` from the container when `$guard` is null. Build the FakeGuard with that name, register it with `AuthManager::useGuard($name, $fake)`, and override `GuardInterface` via `ContainerInterface::instance()` only when `$name` is the default.
- FakeGuard is not ResettableInterface, so it persists across requests on the client (intended).
