# Task 001: authentication — clock for remember-token and cookie expiry

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Inject `ClockInterface` into `RememberTokenManager` and `RequestCookieJar` so remember-token expiry and queued cookie expiry read the injected clock.

## Context
- Related files: packages/authentication/src/Token/RememberTokenManager.php, packages/authentication/src/Cookie/RequestCookieJar.php, packages/authentication/module.php, packages/authentication/composer.json, fixture copies of module.php under packages/testing/tests/fixtures and packages/roadrunner/tests/Fixtures
- Patterns to follow: packages/session (required `ClockInterface` constructor parameter)

## Requirements (Test Descriptions)
- [ ] `it treats a token as valid until the clock passes its lifetime`
- [ ] `it treats a token as expired one second after its lifetime on the clock`
- [ ] `it filters expired tokens against the injected clock`
- [ ] `it sets the cookie expiry relative to the injected clock`
- [ ] `it expires a deleted cookie relative to the injected clock`
- [ ] `it builds RememberTokenManager with the bound clock from the module`

## Acceptance Criteria
- All requirements have passing tests
- `marko/clock` is already in `require` (verify; no change needed)
- Every existing test constructing `RememberTokenManager` / `RequestCookieJar` updated

## Implementation Notes
Exact signatures:
- `RememberTokenManager::__construct(ClockInterface $clock, ?int $lifetimeMinutes = null)`
- `RequestCookieJar::__construct(AuthConfig $config, ClockInterface $clock)` (autowired; no module change needed for the jar)

The module closure and both fixture copies (`packages/testing/tests/fixtures/http-app/vendor/marko/authentication/module.php`, `packages/roadrunner/tests/Fixtures/app/vendor/marko/authentication/module.php`) pass `$container->get(ClockInterface::class)`.

Existing test call sites that break (about 60 constructions), all of which must pass a clock (`new FakeClock()` or `new SystemClock()`):
- tests/Unit/Token/RememberTokenManagerTest.php, tests/Unit/Cookie/RequestCookieJarTest.php
- tests/Unit/AuthManagerTest.php, tests/Unit/Guard/{SessionGuardTest,SessionGuardRememberTest,SessionGuardEventDispatchingTest,GuardDriverRegistryTest}.php
- tests/Unit/Middleware/{QueuedCookiesMiddlewareTest,GuestMiddlewareTest,AuthMiddlewareTest}.php
- tests/Integration/{RememberMeIntegrationTest,AuthFlowIntegrationTest}.php

Grep `new RememberTokenManager(` and `new RequestCookieJar(` across `packages/` before finishing.
