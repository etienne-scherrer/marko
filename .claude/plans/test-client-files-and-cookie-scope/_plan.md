# Plan: TestClient Multi-File Uploads and Cookie Scope

## Created
2026-10-05

## Status
completed

## Objective
Let `Marko\Testing\Http\TestClient` upload several files per field and nested file fields (form notation), and make its cookie jar honour `Path`, `Domain` and `Secure` like a browser, so tests cannot pass for flows a real browser would break.

## Related Issues
Closes #227

## Discovery Notes
- `TestClient::withFile()` stores `$this->files[$field]`; a second call silently overwrites.
- `Request` already accepts `array<string, UploadedFile|array<mixed>>` files keyed like form fields, and `Request::files($key)` returns a list (dot-notation lookup).
- `storeCookies()` keys the jar by name only; every cookie goes to every request. `Cookie` exposes `path()`, `domain()`, `secure()`, `expires()`.
- `buildRequest()` already knows the request host (URL host or `localhost`) and scheme.
- Fixture app at `packages/testing/tests/fixtures/http-app` (CookieController, EchoController) is where new routes go.
- Ask is concrete (issue lists approach and exit criteria); no clarification round needed.

## Scope

### In Scope
- Form-notation file fields: `photos[]` appends, `documents[passport]` nests, arbitrary depth; `withFiles($field, $paths)` helper
- Loud `TestClientException` for a repeated non-array field, a conflicting shape, or a malformed field name
- Cookie jar keyed by (name, domain, path): RFC 6265 path-match and default-path, domain-match / host-only, `Secure` only over HTTPS, expiry per key, most specific path first
- `cookies()` kept (cookies sent to `/`), new `cookieJar()` returning structured `JarCookie` entries
- `withCookie()` gains optional `path`, `domain`, `secure`
- Docs page and README update

### Out of Scope
- Max-Age, SameSite enforcement, public-suffix checks
- PSR-20 clock adoption in TestClient (#221)

## Success Criteria
- [x] Two `withFile('photos[]', ...)` reach the controller as `$request->files('photos')` with two files, in order
- [x] `withFile('documents[passport]', ...)` reaches the controller under the nested key
- [x] Repeated non-array field throws `TestClientException`
- [x] `Path=/admin` cookie sent to `/admin/x`, not `/api`; same-name/different-path cookies coexist; `Secure` not sent over http; expiry removes only its own key
- [x] Docs page documents multi-file uploads and cookie scoping
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Multi-file and nested uploads | - | completed |
| 002 | Scoped cookie jar | 001 | completed |
| 003 | Docs and README | 001, 002 | completed |

## Architecture Notes
- Both 001 and 002 edit `TestClient.php` and `TestClientException.php`; run them sequentially in practice.
- `JarCookie` is a small readonly value object in `Marko\Testing\Http`.

## Risks & Mitigations
- Breaking existing cookie tests: `cookies()` keeps returning name => value for cookies on `/`; manual `withCookie()` defaults to path `/` and any host.
- `session.cookie.secure` defaults to `true`: apps on default config that test session flows over `http://` will lose the session once Secure is enforced. The fixture app sets it to false, so package tests won't catch this. Task 003 documents it.
- Host/scheme for cookie matching come from the final server array (after `withServerVariables()`), not only the URL.
