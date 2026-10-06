# Task 004: SameSite on cross-site requests

**Status**: completed
**Depends on**: 002, 003
**Retry count**: 0

## Description
`JarCookie` stores `sameSite`. A request is cross-site when it sends `Sec-Fetch-Site: cross-site`, or, without that header, an `Origin` whose site differs from the target host's site. Cross-site requests withhold `Strict` cookies and carry `Lax` cookies only on `GET`.

## Context
- Related files: packages/testing/src/Http/JarCookie.php, packages/testing/src/Http/TestClient.php
- Patterns to follow: `cookiesFor()` filtering

## Requirements (Test Descriptions)
- [x] `it withholds a SameSite=Strict cookie from a cross-site request`
- [x] `it sends a SameSite=Lax cookie on a cross-site GET`
- [x] `it withholds a SameSite=Lax cookie from a cross-site POST`
- [x] `it sends SameSite=None and SameSite-less cookies on a cross-site POST`
- [x] `it treats an Origin on a subdomain of the same site as same-site`
- [x] `it treats Sec-Fetch-Site: cross-site as a cross-site request`
- [x] `it sends Strict and Lax cookies on a same-site request`
- [x] `it compares SameSite case-insensitively and treats unknown values as None`
- [x] `it treats Origin: null as a cross-site request`
- [x] `it lets Sec-Fetch-Site: same-site win over a cross-site Origin`
- [x] `it applies a client-wide Origin set with withHeaders`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Cookies without SameSite are treated as `None` (RFC 6265), not Chrome's Lax-by-default; documented.

Contract (review fixes):
- `JarCookie` gains `public ?string $sameSite = null` appended after `$expiresAt` (default keeps BC), plus `allowsCrossSite(string $method): bool` (called only for cross-site requests). The documented `matches()` signature stays unchanged; `cookiesFor()` composes the checks.
- Compare SameSite case-insensitively (`strict`/`lax`); any other value counts as None.
- Read `HTTP_SEC_FETCH_SITE` / `HTTP_ORIGIN` from the final merged `$server` array in `buildRequest()`, so `withHeaders()` and per-call headers both apply. `Sec-Fetch-Site` present → cross-site only when its value is `cross-site` (`same-site`/`same-origin`/`none` win over a differing Origin). Otherwise `Origin: null` or unparseable → cross-site; otherwise compare `PublicSuffixList` sites of the Origin host and target host.
- Fixture: `/jar/{rest*}` is GET-only. For cross-site POST tests, use `POST /echo` (it echoes cookies) with `path=/` cookies, or add `#[Post]` to the jar echo route. `same_site=None` needs `secure=1`.
