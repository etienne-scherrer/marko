# Task 002: Scoped cookie jar

**Status**: completed
**Depends on**: 001 (both edit TestClient.php and TestClientException.php)
**Retry count**: 0

## Description
Store cookies keyed by (name, domain, path) with the Secure flag, and send only cookies matching the request's host, path and scheme (RFC 6265 §5.1.3/5.1.4). Keep `cookies()`, add `cookieJar()`.

## Context
- Related files: packages/testing/src/Http/TestClient.php, new packages/testing/src/Http/JarCookie.php, fixture CookieController, packages/testing/tests/Feature/Http/TestClientCookieJarTest.php

## Requirements (Test Descriptions)
- [x] `it sends a Path=/admin cookie to /admin/x but not to /api`
- [x] `it keeps same-name cookies with different paths side by side`
- [x] `it does not send a Secure cookie over http but sends it over https`
- [x] `it removes an expired cookie only for its own name, domain and path`
- [x] `it scopes a cookie without Path to the default path of the request`
- [x] `it sends a host-only cookie only to the host that set it`
- [x] `it sends a Domain cookie to subdomains and ignores one for a foreign domain`
- [x] `it returns structured entries from cookieJar`
- [x] `it treats the request as secure when HTTPS is set via withServerVariables`
- [x] `it sends the more specific path first when two same-name cookies match, and exposes the first as the request cookie`
- [x] `it lets a response expire a cookie added with withCookie`
- [x] `it rejects a Set-Cookie whose Domain does not domain-match the request host`

## Acceptance Criteria
- All requirements have passing tests; existing cookie jar tests in TestClientCookieJarTest.php still pass unchanged (including the exact `HTTP_COOKIE` string `locale=nl%20nl; theme=dark`)

## Implementation Notes
- `storeCookies()` currently receives only the Response. Pass the request context (host, path, secure) so default-path and host-only/domain validation are possible.
- Derive host and secure from the final `$server` array built in `buildRequest()` (after `withServerVariables()` overrides): host from `HTTP_HOST` with the port stripped and lowercased; secure when `HTTPS` is set and not `off`. Do not use only the parsed URL.
- `buildRequest()` must pass the *filtered* matching cookies to both `HTTP_COOKIE` and `Request(cookies: ...)`, not the whole jar.
- Ordering: longer path first, then earliest-created (stable insertion order). If two matching cookies share a name, both go in the header; the `Request` cookies array keeps the first (as PHP does).
- Domain attribute: strip a leading `.`, lowercase it, and do not store the cookie when it does not domain-match the request host. An IP-address host only matches exactly. With no Domain, the cookie is host-only.
- No Path (or a Path not starting with `/`) uses the RFC 6265 §5.1.4 default-path of the request path.
- `withCookie(name, value, path = '/', domain = null, secure = false)`: `domain = null` means any host (`JarCookie` carries an explicit `hostOnly` flag / any-host marker). A response Set-Cookie (or expiry) with the same name and path must replace or remove such an any-host entry. Otherwise a manual cookie can never be cleared by the app.
- Expiry removes only the exact (name, domain, path) key (plus the any-host rule above).
- `JarCookie`: readonly value object, no final, constructor promotion.
