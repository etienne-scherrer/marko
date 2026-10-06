# Devil's Advocate Review: session-lazy-start

## Critical (Must fix before building)

1. **Task 001 breaks every other `SessionInterface` implementer the moment it lands.** Adding `arm()`/`isAvailable()` to the interface is a fatal "contains abstract methods" error for `FakeSession` (packages/testing/src/Fake/FakeSession.php), the anonymous double in `packages/session/tests/Unit/Middleware/SessionMiddlewareTest.php:631`, and a fourth implementer the Discovery Notes miss: `tests/Integration/PageCacheSessionMiddlewareTest.php:20`. FakeSession is loaded by test suites in almost every package, so 001's worker cannot get a green run and 002/003 inherit a broken suite. Fix: 001 adds the interface methods to all four implementers (minimal compiling versions in FakeSession and the two doubles); 003 and 002 then flesh them out.

2. **Task 002 invalidates existing middleware tests that the plan doesn't mention.** `SessionMiddlewareTest` "starts session before passing to next handler" (line 15) and "ignores an invalid inbound session cookie and starts a fresh session" (line 138, asserts a non-empty id) both send cookieless or malformed requests, so arming makes them fail. Every `writingHandler($session)` test depends on the double's `isModified()` (`started && modified`), so the double has to lazily start on `set()` once armed or about eight cookie tests go red. Fix: 002 rewrites those two tests and gives the double arm plus lazy start.

## Important (Should fix before building)

3. **The disarm points are underspecified (001).** `save()` and `discard()` return early when `!started`. The common path (armed, never touched, middleware calls `discard()`) would leave the session armed unless disarm happens before the early return. The plan's Architecture Notes say `destroy()` disarms, but 001 has no test for that. Also unspecified: `destroy()` on an armed but unstarted session (should disarm with no handler call, not lazy-start just to destroy), `getId()` while armed (should return `''` and not start), and `start()` while armed.
4. **The middleware guard should use `isAvailable()` (002).** `if (!$this->session->started)` re-arms an already-armed session, and it gives no well-defined answer if the middleware runs twice.
5. **"Zero handler calls" and "expires a malformed cookie without calling the handler" can't be seen through the unit double (002).** These need the real `Session` with the statement-recording connection in `BotTrafficTest`. Specify that, and tighten the existing "creates no session for a matched route that never touches it" test to `queries` empty.
6. **Missing dependency: 004 needs 003.** SessionGuard unit tests use `FakeSession`, so arming it requires 003. The test locations were also vague. The roadrunner `InProcessRequestHarness` fixture already has `/session/write` (loginById), `/csrf/token` and `/session/read`, so it is the natural cookieless integration bed.
7. **Parallel file conflict between 006 and 002.** Both can put tests in `SessionMiddlewareTest.php`. 006 should use new test files.
8. **007 also covers FakeSession docs, so it needs 003.** testing.md:86 shows `expect($session->started)`.

## Minor (Nice to address)

- 005's "clears the armed state on reset" duplicates 001's reset test. 005 is really a single harness test.
- `roadrunner-state-leaks.md:145` mentions `started` and may need a touch-up.
- `TokenRequestMiddlewareTest` / `CorsGlobalTest` show the existing pattern for pinning RunsOnUnmatched.

## Questions for the Team

- **Lazy start for reads is mostly wasted work.** `Inertia::render` reads `flash()->all()` on every page, and `SessionGuard::user()` reads the session. On Inertia or auth-checking pages every cookieless request still lazily starts the session (create_sid, open, read, close), so "zero handler calls" only holds for routes that never read. A cookieless armed session is empty by definition, so `get`/`has`/`all`/flash reads could return empty without starting, and only writes would start it. Is that worth the added complexity (a FlashBag over an unstarted buffer)?
