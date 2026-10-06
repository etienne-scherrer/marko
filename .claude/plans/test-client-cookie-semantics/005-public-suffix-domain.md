# Task 005: Public-suffix Domain rejection in the jar

**Status**: completed
**Depends on**: 002, 003, 004
**Retry count**: 0

## Description
`storeCookies()` ignores a response cookie whose `Domain` is a public suffix, like a browser (RFC 6265 §5.3 step 5). When the public-suffix domain equals the request host, the cookie becomes host-only instead.

## Context
- Related files: packages/testing/src/Http/TestClient.php

## Requirements (Test Descriptions)
- [x] `it ignores a Domain=co.uk cookie set by a.example.co.uk`
- [x] `it ignores a Domain=com cookie set by example.com`
- [x] `it stores a cookie for the registrable domain example.co.uk`
- [x] `it stores a Domain=localhost cookie from localhost as host-only`
- [x] `it leaves an existing withCookie entry alone when a public-suffix cookie is ignored`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Ignored like a browser rather than thrown: an app may legitimately send such a cookie to a host the test client does not model; `assertCookie()` still sees it on the response.

Ordering (review fix): the public-suffix rejection `continue` must run before the `unset($this->cookies[self::cookieKey($cookie->name(), null, $path)])` in `storeCookies()` (and before the expiry/delete branch), alongside the existing domain-mismatch check. Otherwise an ignored cookie, including one with Max-Age=0, would still wipe a `withCookie()` entry. Depends on 004 because both tasks edit `storeCookies()` and `TestClientCookieJarTest.php`.
