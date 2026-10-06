# Devil's Advocate Review: unmatched-request-middleware

## Critical (Must fix before building)

1. **Task 003 breaks the session test suite on its own.** `packages/session/tests/Unit/Middleware/SessionMiddlewareTest.php:366` builds an anonymous `implements SessionInterface` fake. Adding `isModified()`/`discard()` to the interface without updating that fake is a fatal error (abstract methods), and it takes the whole session test run down with it. Task 003 has to add both methods to that fake.
2. **Task 001 contradicts an existing test.** `packages/routing/tests/WithoutMiddlewareTest.php:132` asserts "still runs every global middleware for unmatched requests". Task 001 has to rewrite it. Also, `RouterTest` passes global middleware names that are not real classes (`'App\\Middleware\\GlobalMiddleware'`) with a mocked container. If the attribute is reflected in the constructor, or without a `class_exists` guard, `ReflectionException` breaks those tests. Reflection must happen only on the unmatched path, and it must handle class names that do not exist.
3. **Task 004's discard path never says what happens to the cookie.** `attachSessionCookie()` sends a fresh cookie whenever `getId() !== $inboundId`, and an *expired* cookie when the id is `''`. If `discard()` keeps the generated id, a fresh cookie still goes out. If it clears the id, an expired cookie goes out. Both break the "no cookie" criterion. The middleware must return the response untouched when it discards.
4. **Task 004 breaks existing middleware tests.** `SessionMiddlewareTest` cases at lines 153, 183, 206, 223, 242, 259 and 304 start a new session and expect a cookie (or a save) without ever writing to it. Each one has to write a value first, or be rewritten.

## Important (Should fix before building)

1. **Task 004: "inbound cookie exists" is ambiguous.** `seedSessionId()` silently ignores a malformed cookie, and that request then gets a brand-new session. If the rule is simply "a cookie header was present", any bot sending `marko_session=garbage` causes a write on every request. The rule must mean the session actually resumed the inbound id (`$inboundId !== null && session->getId() === $inboundId` after start).
2. **Task 003: modification tracking must compare snapshots, not count calls.** `Inertia::render` calls `flash()->all()` on every render (`packages/inertia/src/Inertia.php:170`), and `FlashBag::syncToSession()` runs even when nothing changes. A dirty flag set on every mutating call would make every Inertia page persist a session. Add a test that reading an empty flash bag, or a `get()`/`has()`, does not count as a modification.
3. **Task 003: `Session::discard()` semantics.** It must call `session_abort()` (handler `close()`, no `write()`), set `started = false`, and do nothing when the session is not started. A later `start()` in the same process (RoadRunner) must not throw "A session is already active".
4. **Task 005 / 004 tests can fail at random because of GC.** `session_start()` runs GC with the configured probability, which issues a DELETE on the database handler. The integration harness must set `session.gc_probability => 0`, as `StatelessRouteTest` already does.
5. **RoadRunner E2E test breaks (task 004).** `packages/roadrunner/tests/Integration/EndToEndTest.php:114` expects `/session/read` (a read-only route) to set a cookie on the first request. With lazy persistence it won't. Point the test at `/session/write`, or invert it into a test of lazy behaviour. This test is in the `integration-destructive` group, so `composer test` won't catch it.
6. **Task 007: custom 404/405 pages that touch the session or auth.** Session and authentication middleware no longer run on unmatched requests. An error template that reads the session (flash, auth user, CSRF token) now throws `SessionNotStartedException`, so the user gets a 500 instead of the 404. This must be documented in routing.md and session.md.

## Minor (Nice to address)

- Task 001: the Router is `readonly`, so the filtered list cannot be memoised lazily. Reflecting on each unmatched request is fine; just keep it off the matched path.
- Task 006: the matched-route 419 test needs a started session (e.g. `FakeSession`) bound for `CsrfTokenManager`.
- Task 005: give the recording connection a unique name or namespace (`MockConnection` already exists in `Marko\Session\Database\Tests\Unit`).

## Questions for the Team

- A cookie that is valid in format but stale or expired (replayed by a bot) still resumes and saves a session row on every request. Is that acceptable, or should "resumed" require that the handler's `read()` returned data?
- A new session that is destroyed in the same request still emits an expired cookie to a client that never had one. Is that harmless enough to keep?
- Should `QueuedCookiesMiddleware` (authentication) opt in? It is not needed today, since nothing queues cookies on unmatched requests.
