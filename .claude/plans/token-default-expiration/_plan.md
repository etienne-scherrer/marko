# Plan: Token Default Expiration

## Created
2026-10-05

## Status
completed

## Objective
Wire the shipped-but-unread `authentication-token.token_expiration_days` config as the default personal access token lifetime (option B of #269), so tokens created without an explicit `expiresAt` expire after the configured number of days, and remove the two never-thrown token exceptions.

## Related Issues
Closes #269
Relates to #276 (timezone of stored timestamps; not fixed here)

## Discovery Notes
- `packages/authentication-token/config/authentication-token.php` declares `token_expiration_days => 365`; no code reads it and the package has no config class.
- `TokenManager::createToken()` stores the caller's `?DateTimeInterface $expiresAt` as a `Y-m-d H:i:s` string or `null`; it never sets `createdAt`. `TokenManager` currently takes `(TokenRepositoryInterface, ?EventDispatcherInterface)`.
- `TokenGuard` already rejects a token whose `expires_at` is set and past, using the injected PSR-20 clock. Storing the computed expiry keeps enforcement in that existing check.
- Config class pattern: `readonly` class wrapping `ConfigRepositoryInterface` (see `AuthorizationConfig::defaultGuard()`, which validates and throws `Marko\Config\Exceptions\ConfigException` with message/context/suggestion).
- `FakeClock` lives in `marko/testing`; `FakeConfigRepository` takes a flat dot-notation array.
- `ExpiredTokenException` and `InvalidTokenException` are referenced only by their own tests and the docs API reference. `TokenException` stays (base of nothing else after removal, but `StatelessGuardException` extends `AuthException`; check whether `TokenException` becomes orphaned and keep it only if still used).
- Callers of `new TokenManager(...)`: `tests/Service/TokenManagerTest.php`, `tests/Feature/TokenGuardWiringTest.php`. Container resolution: root `tests/Integration/App/AuthTest.php` (real config and clock). `TokenGuardWiringTest::bootTokenContainer()` needs the `authentication-token.token_expiration_days` key added.
- #276 timezone bug (formatting in the caller's zone, parsing in default zone) is explicitly out of scope; keep the existing `format('Y-m-d H:i:s')` behaviour.

## Scope

### In Scope
- `Marko\AuthenticationToken\Config\TokenConfig::expirationDays(): ?int` (int >= 1 or null; anything else throws `ConfigException`)
- `TokenManager` constructor gains `TokenConfig` and `ClockInterface`; default expiry = clock now + N days; config `null` = never; explicit `expiresAt` wins
- `TokenManager` sets `createdAt` from the clock
- `TokenCreatedEvent::$expiresAt` carries the computed expiry
- Delete `ExpiredTokenException`, `InvalidTokenException` and their tests
- Docs page and README updates

### Out of Scope
- Timezone normalisation of stored token timestamps (#276)
- A per-call `neverExpires` flag
- Max-age enforcement at validation time (option C)

## Success Criteria
- [x] `TokenConfig` validates `token_expiration_days` loudly
- [x] `createToken()` without `expiresAt` stores now + N days from the injected clock
- [x] Config `null` stores `null`; explicit `expiresAt` overrides
- [x] `createdAt` set from the clock
- [x] Unused exceptions removed
- [x] Docs describe the default lifetime and how to opt out; API reference lists the new constructor
- [x] All tests passing, `composer ci` green
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | TokenConfig reads and validates token_expiration_days | - | completed |
| 002 | Remove unused ExpiredTokenException, InvalidTokenException and TokenException | - | completed |
| 003 | TokenManager applies the default lifetime and sets createdAt | 001 | completed |
| 004 | Docs page and README | 001, 002, 003 | completed |

## Architecture Notes
- `TokenConfig` is autowirable (only `ConfigRepositoryInterface`); `TokenManager` stays autowirable (`ClockInterface` is bound by `marko/clock`, already required).
- Compute the default with `DateTimeImmutable::add(new DateInterval("P{N}D"))` on `clock->now()`.
- Constructor order: `TokenRepositoryInterface $repository, TokenConfig $config, ClockInterface $clock, ?EventDispatcherInterface $eventDispatcher = null`.

## Risks & Mitigations
- Behaviour change (new tokens get a 365-day expiry by default): call it out in the PR description and docs; opt out by setting the config to `null`.
- Constructor signature change breaks direct instantiation: update in-repo callers; pre-1.0, no shim.
