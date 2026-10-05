# Task 006: Cookie jar

**Status**: completed
**Depends on**: 005
**Retry count**: 0

## Description
Cookies set by responses persist across requests on the same client; `withCookie()` adds one; `withoutCookies()` clears the jar; expired cookies are removed.

## Requirements (Test Descriptions)
- [x] `it sends cookies set by a previous response`
- [x] `it removes a cookie the response expires`
- [x] `it sends a cookie added with withCookie`
- [x] `it clears the jar with withoutCookies`
- [x] session-backed flow works (login then protected page)

## Implementation Notes
- Read values/expiry via `Cookie::value()` / `Cookie::expires()` (added in task 003), never by parsing `toSetCookieString()`.
- Expiry rule: drop the cookie when `expires !== null && expires !== 0 && expires <= now`. `null` and `0` mean a session cookie, which is kept. SessionMiddleware expires cookies via `ClockInterface::now() - offset`, so use the container's `Psr\Clock\ClockInterface` when bound, else `time()`.
- Jar is keyed by name (domain/path matching out of scope; document it).
