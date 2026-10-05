# Task 002: RequestCookieJar

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Production `CookieJarInterface` that reads from the current request and queues outgoing cookies as `Marko\Routing\Http\Cookie` objects carrying the configured attributes.

## Context
- Related files: packages/authentication/src/Cookie/RequestCookieJar.php
- Patterns to follow: `SessionMiddleware::freshCookie()` / `expiredCookie()`

## Requirements (Test Descriptions)
- [ ] `it reads cookie values from the current request`
- [ ] `it returns null for cookies before a request is received`
- [ ] `it queues a cookie with configured attributes and expiry`
- [ ] `it serves a queued value to later reads in the same request`
- [ ] `it queues an expired cookie on delete`
- [ ] `it throws when writing a cookie outside an http request`
- [ ] `it clears the request and queued cookies on reset`

## Acceptance Criteria
- All requirements have passing tests
