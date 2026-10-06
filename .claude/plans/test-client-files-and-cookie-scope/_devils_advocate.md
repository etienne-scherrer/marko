# Devil's Advocate Review: test-client-files-and-cookie-scope

## Critical (Must fix before building)

1. **Task 001 points at the wrong test file.** The upload tests already exist (red) in `packages/testing/tests/Feature/Http/TestClientUploadsTest.php`, including two cases not listed in the task (`nests list fields inside named fields`, `throws when a list field is later used as a single file`). Task 001 lists `TestClientRequestTest.php`; a worker would write duplicate tests there and leave the real file red. Fix: point 001 at `TestClientUploadsTest.php` and list all its cases.

2. **Task 001: the temp-file cleanup in `call()` assumes a flat array.** `foreach ($files as $file) { $file->tempPath() }` fatals on nested arrays (`photos => [UploadedFile, UploadedFile]`). Cleanup must walk recursively (e.g. `array_walk_recursive`). Added to 001's notes.

3. **Task 002: `storeCookies()` has no request context.** It takes only the `Response`, but default-path (RFC 6265 §5.1.4) and host-only/domain validation need the request host and path. Fix: pass host, path and secure flag (derived from the built `Request`) into `storeCookies()`.

## Important (Should fix before building)

4. **001 and 002 both edit `TestClient.php` and `TestClientException.php` but the task table says neither depends on the other.** Parallel workers would conflict. Fix: 002 depends on 001.

5. **Task 002: scheme/host come from the URL only, but `withServerVariables(['HTTPS' => 'on'])` / `HTTP_HOST` override the built request.** Matching on the parsed URL while the app sees HTTPS would make the jar disagree with the app. Fix: derive host (without port) and secure flag from the final `$server` array (`HTTPS`, `HTTP_HOST`) after overrides.

6. **Task 002: manual `withCookie()` entries ("any host", path `/`) never get removed by a response.** A response expiring `locale` (host-only `localhost`, `/`) has a different key than the manual any-host entry, so the manual cookie survives and keeps being sent. Fix: a response Set-Cookie with the same name and path replaces or expires an any-host entry too.

7. **Task 002: duplicate names and ordering are undefined.** When `/admin` and `/` cookies named `x` both match, the `Cookie` header carries both (most specific path first, then earliest-created, which keeps the existing `locale=nl%20nl; theme=dark` assertion). The `Request` `cookies:` array is keyed by name, so it should take the first one, as PHP does. Also `buildRequest()` currently passes `$this->cookies` straight to `Request`. It must pass the filtered set.

8. **Task 002: Domain attribute details.** Strip a leading `.`, lowercase it, and reject (don't store) a cookie whose Domain does not domain-match the request host. An IP host only matches exactly. Spelled out in the requirements.

9. **Task 002/003: the default `session.cookie.secure` is `true`** (`packages/session/config/session.php`). With strict Secure handling, an app using defaults and `get('/...')` (http://localhost) loses its session between requests, and login flows fail with no explanation. The fixture app sets `secure => false`, so the package tests won't show this. Fix: the docs must call this out (use `https://` URLs or set secure false in testing). See Question 1.

## Minor (Nice to address)

- `cookies()` semantics ("cookies sent to `/`") are ambiguous across hosts. Suggest: entries with path `/` that would go to `http(s)://localhost/`, plus any-host entries.
- `JarCookie` should carry `hostOnly` explicitly so `cookieJar()` output is unambiguous for any-host manual entries (domain `null`).

## Questions for the Team

1. Should the jar treat `http://localhost` / `127.0.0.1` as a secure context the way Chrome and Firefox do? That keeps default-config session tests working, but it contradicts the issue's "Secure not sent over http" exit criterion for localhost. The plan keeps strict behaviour and documents it.
2. Should `withCookie()` with no domain be any-host (current plan) or host-only for `localhost`?
