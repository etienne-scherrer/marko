# Devil's Advocate Review: test-client-cookie-semantics

## Critical (Must fix before building)

None. Nothing in the plan points at code that doesn't exist or at signatures that are wrong. `Cookie`, `JarCookie`, `TestClient::storeCookies()/cookiesFor()/now()` and the `/jar/set` fixture all match what the plan describes.

## Important (Should fix before building)

1. **Tasks 004 and 005 can run in parallel but edit the same code** (fixed). Both depend only on 002 and 003. Both rewrite `TestClient::storeCookies()` (004 adds `sameSite:` to the `new JarCookie(...)` call; 005 adds the public-suffix check to the same loop). Both also append to `TestClientCookieJarTest.php`. Two parallel workers would hit merge conflicts. Fix: 005 now depends on 004.

2. **The JarCookie contract is unspecified** (tasks 003, 004; fixed). `JarCookie` is documented public API (testing.md "JarCookie", including `matches(string $host, string $path, bool $secure)`). The plan doesn't say where the new properties go or how SameSite filtering plugs in. Changing `matches()` positionally would break the documented signature. Fix: append `?int $expiresAt = null` (003) and `?string $sameSite = null` (004) after `$hostOnly`, both with defaults. Leave `matches()` unchanged. Add separate helpers: `isExpired(int $now)` and `sentOn(bool $crossSite, string $method)`. `cookiesFor()` composes them.

3. **Expiry semantics are underspecified** (task 003; fixed).
   - The jar reads `Cookie` objects directly, not header lines. `maxAge()` returns a negative value as given (task 001), so the jar must treat any `maxAge <= 0` as delete, not only `0`.
   - "Max-Age wins over Expires" needs both directions tested: a positive Max-Age with a past Expires keeps the cookie, and a short Max-Age with a far Expires evicts early.
   - `expires === 0` must stay a session cookie (current `isExpired()` behaviour).

4. **SameSite value handling** (task 004; fixed). `Cookie::$sameSite` is a free string. `SessionMiddleware` uses `ucfirst()` on config, but other code may send `lax` or `STRICT`. Fix: compare case-insensitively. Treat unknown values as None.

5. **Cross-site detection edge cases** (task 004; fixed).
   - `Origin: null` (opaque origin) must count as cross-site.
   - A `Sec-Fetch-Site` of `same-site`, `same-origin` or `none` must win over a differing Origin.
   - Headers must be read from the final merged `$server` array (`HTTP_SEC_FETCH_SITE`, `HTTP_ORIGIN`), so client-wide `withHeaders()` and per-call headers both work.

6. **Rejected public-suffix cookies must not delete existing entries** (task 005; fixed). `storeCookies()` runs `unset($this->cookies[self::cookieKey($cookie->name(), null, $path)])` (TestClient.php:757) before the expiry check. Fix: put the rejection `continue` before that unset, as the existing domain-mismatch check already is. Otherwise an ignored `Domain=co.uk` cookie would still wipe a `withCookie()` entry. The same applies to a rejected cookie with `Max-Age=0`.

7. **Fixture gaps** (tasks 003, 004; fixed).
   - `expires_in` must be relative to the bound clock. Fix: read `$request->server('REQUEST_TIME')`, which TestClient sets from `now()`. Don't use `time()`; the existing `expired=1` path uses `time()`, which disagrees with a FakeClock.
   - `same_site=None` needs `secure=1`, or the `Cookie` constructor throws `CookieException`.
   - `/jar/{rest*}` is GET-only, so cross-site POST tests need `POST /echo` (which echoes cookies) or a `#[Post]` added to the jar echo route.

8. **Docs must replace stale statements, not just add** (task 006; fixed). testing.md line 410 ("Expiry" bullet) currently says only that an already-past `Expires` removes a cookie. Line 413 and the `JarCookie` API block (line 894) list the properties without `expiresAt` and `sameSite`. All three need updating.

## Minor (Nice to address)

- Task 001: spell out the exact attribute order in the test (`name=v; Expires=...; Max-Age=N; Path=...`) so the expected string is unambiguous.
- Task 001: extend the existing all-getters test at `CookieTest.php:84`.
- Task 003: `now + maxAge` can overflow to a float for a huge Max-Age, which gives a TypeError on `?int $expiresAt`. Clamp it to `PHP_INT_MAX`.
- Task 003: `cookies()` and `cookieJar()` now call `now()`, which can throw `ContainerExceptionInterface`. Add `@throws` to their docblocks.
- Task 002: `requestHost()` returns IPv6 hosts in brackets (`[::1]`), and `FILTER_VALIDATE_IP` rejects that form. Strip the brackets before the IP check.
- Task 002: the multi-label list is necessarily incomplete. List the exact suffixes in the class docblock so the limitation can be discovered.

## Questions for the Team

- RFC 6265bis lets Lax cookies through on any cross-site request with a "safe" method (GET, HEAD, ...), not only GET. Should `head()` cross-site also carry Lax cookies? The plan says GET only.
- `cookies()` and `cookieJar()` now mutate the jar (eviction) on read. Is that acceptable, or should they filter without evicting?
- Should the test client ever offer Chrome's Lax-by-default for SameSite-less cookies, for example as an opt-in? It is out of scope here.
