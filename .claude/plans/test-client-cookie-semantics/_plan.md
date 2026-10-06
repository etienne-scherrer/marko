# Plan: TestClient Cookie Semantics

## Created
2026-10-05

## Status
completed

## Objective
Give `marko/routing`'s `Cookie` a `Max-Age` attribute and make `marko/testing`'s `TestClient` cookie jar honour expiry (on the bound clock), `SameSite` on cross-site requests, and public-suffix `Domain` rejection, so cookie-dependent feature tests no longer pass falsely.

## Related Issues
Closes #255

## Discovery Notes
- `Cookie` (readonly, `packages/routing/src/Http/Cookie.php`) only has `expires`; `toSetCookieString()` is the single place Set-Cookie lines are built (`Response::headerLines()`), so `Max-Age` only needs emitting there.
- `TestClient::storeCookies()` reads `Cookie` objects straight off the `Response` (no header parsing), drops a cookie only if already expired, and `JarCookie` stores no expiry or SameSite.
- `TestClient::now()` already reads the bound `ClockInterface` (falls back to `SystemClock`, #221), so `FakeClock` bound with `container->instance()` drives jar expiry.
- The http-app fixture's `CookieScopeController` (`/jar/set`) builds cookies from the query string; it is extended with `max_age`, `expires_in` and `same_site`.
- `SessionMiddleware` sets its cookie with `SameSite` from config; all existing tests send no `Origin`, so they stay same-site and unchanged.

## Scope

### In Scope
- `Cookie::$maxAge` (last constructor param), `maxAge()` getter, `Max-Age=` emission (`<= 0` emitted as `Max-Age=0`)
- `JarCookie` gains `expiresAt` and `sameSite`; jar computes expiry at store time (`Max-Age` wins over `Expires`), evicts expired entries on every read
- Cross-site detection: `Sec-Fetch-Site` header when given, otherwise an `Origin` header whose site differs from the target host's site; `Strict` withheld, `Lax` sent only on `GET`
- Built-in public-suffix list (`PublicSuffixList`): responses setting a public-suffix `Domain` are ignored (or become host-only when the domain equals the request host, RFC 6265 §5.3 step 5)
- Docs: routing.md (Setting Cookies, Cookie API), testing.md (Cookie scope, JarCookie)

### Out of Scope
- Adopting `maxAge` in `marko/authentication` / `marko/session`
- Full Mozilla PSL dependency
- Schemeful same-site, Lax-by-default for cookies without SameSite (treated as None, per RFC 6265)

## Success Criteria
- [x] `Cookie` supports `maxAge`, emits `Max-Age=`, unit tested incl. `0`
- [x] Jar evicts on `FakeClock` advance; `Max-Age` beats `Expires`
- [x] Cross-site withholds Strict, Lax only on GET, same-site unchanged
- [x] `Domain=co.uk` from `a.example.co.uk` not stored
- [x] Docs updated
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Cookie Max-Age in marko/routing | - | completed |
| 002 | PublicSuffixList | - | completed |
| 003 | Jar expiry with Max-Age precedence | 001 | completed |
| 004 | SameSite on cross-site requests | 002, 003 | completed |
| 005 | Public-suffix Domain rejection in the jar | 002, 003, 004 | completed |
| 006 | Docs (routing.md, testing.md, READMEs) | 001, 003, 004, 005 | completed |

## Architecture Notes
- `Cookie` stays readonly; `maxAge` appended at the end of the constructor.
- `JarCookie` is documented public API: new properties `?int $expiresAt = null`, `?string $sameSite = null` are appended after `$hostOnly` with defaults; `matches()` keeps its documented signature, and expiry/SameSite checks are separate helpers (`isExpired()`, `allowsCrossSite()`) composed in `cookiesFor()`.
- 004 and 005 both edit `storeCookies()` and the cookie-jar test file, so 005 runs after 004.
- `Max-Age` without `Expires`: no Expires is derived (deriving it would need a clock in a value object); documented.
- Public suffix rules: every single-label domain is a suffix (PSL default `*` rule) plus a small built-in list of common multi-label suffixes. IP addresses are never suffixes.

## Risks & Mitigations
- Existing tests rely on cookies without SameSite always being sent: absent SameSite is treated as None, so nothing changes for same-site or cross-site.
- Shared `testing.md` with #265: edits confined to the "Cookie scope" section and the `JarCookie` API entries.
