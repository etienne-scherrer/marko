# Task 006: Docs

**Status**: completed
**Depends on**: 001, 003, 004, 005
**Retry count**: 0

## Description
Document `maxAge` in routing.md ("Setting Cookies" and the `Cookie` API reference) and expiry, SameSite and the public-suffix rule in testing.md ("Cookie scope" and `JarCookie`). Keep testing.md edits minimal (#265 edits the same file).

## Context
- Related files: packages/docs-markdown/docs/packages/routing.md, packages/docs-markdown/docs/packages/testing.md, docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `routing.md documents Cookie maxAge`
- [x] `testing.md documents jar expiry, SameSite and public suffixes`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS.md

## Implementation Notes
packages/testing/README.md: the `cookieJar()` bullet now mentions expiry and SameSite. The routing README needed no change.

Review fix, stale content to replace (not just append) in testing.md:
- "Cookie scope" → the **Expiry** bullet says only that an already-past `Expires` removes a cookie. Rewrite it to cover eviction as the bound clock advances, Max-Age precedence and Max-Age<=0 deletion.
- The `cookieJar()` paragraph lists `name, value, domain, path, secure, hostOnly`; add `expiresAt` and `sameSite`.
- The `### JarCookie` API block: add `public ?int $expiresAt;` and `public ?string $sameSite;`, plus any new public helpers (`isExpired()`, `allowsCrossSite()`).
- Note that `cookies()`/`cookieJar()` evict expired entries.
