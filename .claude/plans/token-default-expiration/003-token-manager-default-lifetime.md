# Task 003: TokenManager applies the default lifetime and sets createdAt

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`TokenManager` takes `TokenConfig` and `Psr\Clock\ClockInterface`. When `createToken()` is called without `expiresAt`, it stores clock now + `expirationDays()` days; a `null` config stores `null`; an explicit `expiresAt` always wins. It also sets `createdAt` from the clock, and `TokenCreatedEvent::$expiresAt` carries the computed expiry.

## Context
- Related files: `packages/authentication-token/src/Service/TokenManager.php`, `tests/Service/TokenManagerTest.php`, `tests/Feature/TokenGuardWiringTest.php` (`issueToken()` constructs `TokenManager`), `tests/Integration/App/AuthTest.php` (root; resolves `TokenManager` from the real app container, must still pass)
- Patterns to follow: `FakeClock` (marko/testing), `FakeConfigRepository` with flat dot keys
- Keep the existing `format('Y-m-d H:i:s')` storage (timezone handling is #276); `createdAt` uses the same `Y-m-d H:i:s` format
- Read the config only through `TokenConfig::expirationDays()` (which uses `get()`; never `getInt()`, which casts `null` to `0`)
- `TokenGuardWiringTest`: `bootTokenContainer()`'s `FakeConfigRepository` has no `authentication-token.token_expiration_days` key; add it with the shipped value (`require` the package config file) or container resolution throws `ConfigNotFoundException`. `issueToken()` must use a clock at the same `$now` as the booted container (resolve `TokenManager` from that container, or pass a `FakeClock($now)`); `it reaches the #[Can] gate check with a valid token` issues a token without `expiresAt`, which now gets the default expiry
- New constructor: `(TokenRepositoryInterface $repository, TokenConfig $config, ClockInterface $clock, ?EventDispatcherInterface $eventDispatcher = null)`

## Requirements (Test Descriptions)
- [x] `it stores an expiry of now plus the configured days when no expiresAt is given`
- [x] `it stores a null expiry when token_expiration_days is null`
- [x] `it stores the explicit expiresAt instead of the configured default`
- [x] `it sets createdAt from the clock`
- [x] `it dispatches TokenCreatedEvent with the computed default expiry`
- [x] `it fails loudly on an invalid token_expiration_days even when an explicit expiresAt is given`
- [x] `it resolves TokenManager from the container with the shipped config` (wiring)

## Acceptance Criteria
- All requirements have passing tests
- Existing TokenManager and wiring tests updated to the new constructor
- `tests/Integration/App/AuthTest.php` (`integration-services` group) still passes
- Code follows code standards

## Implementation Notes
`createToken()` reads `TokenConfig::expirationDays()` first on every call, so a bad config value fails loudly even when an explicit `expiresAt` is passed. The default is `clock->now()->add(new DateInterval("P{N}D"))`. `createdAt` uses the same `now`. Both are stored with `format('Y-m-d H:i:s')`, the same format as before (timezone handling is #276). `TokenGuardWiringTest::issueToken()` now gets `TokenManager` from the booted container with the shipped config value. Two new end-to-end cases cover the 365-day default at the guard: accepted 1s before the expiry, rejected 1s after it.
