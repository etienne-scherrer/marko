# Devil's Advocate Review: http-test-client

## Critical (Must fix before building)

1. **004: the fixture's `vendor/` would never be committed.** Root `.gitignore` ignores `vendor/` at any depth and only re-includes `!/packages/*/tests/Fixtures/**/vendor/` (capital F). The fixture lives at `tests/fixtures/http-app` (lowercase), so `vendor/marko/*/module.php` stubs are ignored on Linux CI. On macOS, `core.ignorecase=true` makes the capital-F negation match, so the stubs look fine locally. `tests/FixtureTrackingTest.php` only globs `packages/*/tests/Fixtures`, so it would not catch this. Fix: add the negation `!/packages/*/tests/fixtures/**/vendor/` and extend FixtureTrackingTest to scan the lowercase `fixtures` root too.
2. **003/006: `Marko\Routing\Http\Cookie` has no `value()` or `expires()` accessor.** The only accessors are `name()`, `path()`, `domain()` and `toSetCookieString()`. The cookie jar cannot read values or detect expiry, and `assertCookie($name, $value)` cannot compare values. Fix: add `value()`, `expires()`, `secure()`, `httpOnly()` and `sameSite()` accessors to Cookie (routing) in task 003.

## Important (Should fix before building)

1. **003/004: both tasks edit `packages/testing/composer.json`, and 003 needs routing first.** 003 (no deps) already uses `Response`, and 004 adds `marko/routing`. Fix: move the require, plus a PackageStructureTest assertion, into 003.
2. **001: the constructor change would ripple to 20 call sites.** `new WorkerRequestHandler(` appears in worker.php plus 19 times in two test files. Fix: define `RequestStateResetter(ContainerInterface)` with `reset(): void`. WorkerRequestHandler builds it from its existing `$container`, so its constructor stays the same.
3. **004/005: `head()` and `options()` can't be observed.** Routing has no Head/Options attribute. An unmatched route returns a plain 404 *before* global middleware runs, so no echo route or middleware ever sees the request. Fix: the fixture defines its own `Route` subclasses (Route is abstract with `getMethod()`) for HEAD and OPTIONS.
4. **005/007: "boot once" scope is undefined.** If the Application were cached statically across clients, the `useGuard()` / `GuardInterface` overrides from `actingAs()` would leak into later tests. Fix: one Application per TestClient instance, lazily booted, with no static cache.
5. **007: the default guard name has to be resolved.** `useGuard()` takes a name, so `actingAs($user)` must read `AuthConfig::defaultGuard()` from the container. The FakeGuard should carry that name.
6. **005: request building has to match `Request::fromGlobals()`.** Form data for POST/PUT/PATCH/DELETE goes into `post` (fromGlobals parses urlencoded bodies for PUT/PATCH/DELETE). `REQUEST_URI` must include the query string, and `QUERY_STRING` must be set. JSON helpers put data in `body` only.
7. **003: header assertions must be case-insensitive.** `Response::headers()` keeps the case the headers were set with (for example `Content-Type`).
8. **006: expiry rule and clock.** SessionMiddleware expires cookies with `clock->now() - offset`, and `expires` 0/null means a session cookie. The jar should drop a cookie when `expires !== null && expires !== 0 && expires <= now`.
9. **004: fixture side effects.** The session file path must be per-process under the system temp dir (as in the roadrunner fixture), and tests must clean it up. No fixture file may end in `Test.php`.

## Minor (Nice to address)
- The roadrunner `InProcessRequestHarness::reset()` could also use RequestStateResetter (it currently skips `ksort`).
- The cookie jar ignores domain/path matching; this should be documented.
- `actingAs()` when the authentication module isn't discovered should fail with a helpful message.

## Questions for the Team
- Should a client be shareable across tests (`beforeAll`)? The plan currently assumes one client per test.
