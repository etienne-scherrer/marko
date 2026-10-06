# Task 004: SessionGuard, CSRF and flash on an armed session

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
`SessionGuard` accepts an available (armed) session. Verify login on a cookieless request regenerates and persists, and that CSRF tokens and flash messages work through lazy start.

## Context
- Related files: packages/authentication/src/Guard/SessionGuard.php, packages/security/src/CsrfTokenManager.php, packages/session/src/Flash/FlashBag.php

## Requirements (Test Descriptions)
- [x] `it logs in on an armed session`
- [x] `it still throws when the session is neither armed nor started`
- [x] `it regenerates and persists a login on a cookieless request`
- [x] `it issues and persists a csrf token on a cookieless request`
- [x] `it persists a flash message set on a cookieless request`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes (from review)
- `SessionGuard::ensureSessionStarted()` (packages/authentication/src/Guard/SessionGuard.php:239) switches to `$this->session->isAvailable()`. Unit tests go in packages/authentication/tests/Unit/Guard/SessionGuardTest.php using `FakeSession::arm()` (needs task 003).
- Cookieless end-to-end tests (login, csrf) use the roadrunner `InProcessRequestHarness` (packages/roadrunner/tests/Support), whose fixture DemoController already has `/session/write` (loginById), `/csrf/token` and `/session/read`. Send requests with NO session cookie and assert a session cookie is returned and the next request with that cookie sees the logged-in user or the same token.
- There is no flash route in the fixture. Test flash persistence with real `Session` + `SessionMiddleware` + in-memory handler in packages/session/tests (or add a fixture route).
