# Task 003: Jar expiry with Max-Age precedence

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`JarCookie` stores `?int $expiresAt`, computed when a response sets the cookie: `Max-Age` relative to the client's clock wins over `Expires`. Expired entries are evicted before every request and every `cookies()`/`cookieJar()` read.

## Context
- Related files: packages/testing/src/Http/JarCookie.php, packages/testing/src/Http/TestClient.php, packages/testing/tests/Feature/Http/TestClientCookieJarTest.php, packages/testing/tests/fixtures/http-app/app/web/src/Http/Controllers/CookieScopeController.php
- Patterns to follow: FakeClock binding in TestClientRequestTest (`container->instance(ClockInterface::class, $clock)`)

## Requirements (Test Descriptions)
- [x] `it stops sending a cookie once its Expires passes on the bound clock`
- [x] `it stops sending a cookie once its Max-Age passes on the bound clock`
- [x] `it lets Max-Age win over Expires`
- [x] `it removes a cookie the response sets with Max-Age=0`
- [x] `it exposes the expiry in cookieJar, null for a session cookie`
- [x] `it evicts expired cookies from cookies and cookieJar`
- [x] `it keeps a cookie whose Expires is past when a positive Max-Age is set`
- [x] `it removes a cookie the response sets with a negative Max-Age`
- [x] `it keeps treating Expires=0 as a session cookie`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Fixture `/jar/set` gained `max_age`, `expires_in` (relative to the bound clock) and `same_site` query parameters.

Contract (review fixes):
- `JarCookie` gains `public ?int $expiresAt = null` appended after `$hostOnly` (with a default, so `withCookie()` and the documented constructor stay BC), plus `isExpired(int $now): bool` (`expiresAt !== null && expiresAt <= now`). Do NOT change the documented `matches()` signature; `cookiesFor()` composes `matches()` with `isExpired()`.
- Expiry at store time: if `maxAge() !== null`, then `maxAge() <= 0` (including negative, which `maxAge()` returns raw) means delete, otherwise `expiresAt = now + maxAge` (clamp against int overflow). Else `expires()` null or `0` → session cookie (`expiresAt = null`); else `expiresAt = expires()`, with `<= now` meaning delete.
- Fixture `expires_in` must be computed from `$request->server('REQUEST_TIME')` (TestClient sets it from the bound clock), not `time()`. `same_site=None` requires `secure=1` or `Cookie` throws `CookieException`.
- `cookies()` / `cookieJar()` now call `now()`: add `@throws ContainerExceptionInterface` to their docblocks.
