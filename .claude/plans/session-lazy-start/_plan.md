# Plan: Session Lazy Start

## Created
2026-10-06

## Status
completed

## Objective
Start the session lazily on cookieless matched requests so anonymous traffic that never touches the session makes zero session-handler calls, while resumed sessions keep eager start. Pin that `QueuedCookiesMiddleware` and `SessionMiddleware` never run on unmatched requests.

## Related Issues
Closes #267

## Discovery Notes
- Issue #267 is a maintainer-decision issue; this plan implements its recommendation B (lazy start for cookieless requests only) and "no change" for `QueuedCookiesMiddleware`, plus a pinning test and a routing docs sentence.
- #266 (strict ids) is merged: unknown/expired ids are replaced by PHP strict mode via `validateId()`. Telling "unknown" from "known" needs a handler call, so only cookieless and malformed-cookie requests can take the lazy path.
- `SessionMiddleware` currently calls `start()` on every matched request. `Session::ensureStarted()` throws `SessionNotStartedException`.
- `SessionGuard::ensureSessionStarted()` checks `$session->started`. `CsrfTokenManager`, `Inertia` (flash) and `FlashBag` only call accessors, so they work through lazy start without changes.
- `Session::reset()` (ResettableInterface) is what RoadRunner calls between requests; it must clear the armed state.
- Implementers of `SessionInterface`: `Session`, `FakeSession` (marko/testing), an anonymous test double in `SessionMiddlewareTest`, and an anonymous double in `tests/Integration/PageCacheSessionMiddlewareTest.php`. Task 001 must update all four so the suite keeps compiling.
- `Inertia::render` reads `flash()->all()` and `SessionGuard::user()` reads the session, so those routes still lazily start on cookieless requests; "zero handler calls" applies only to routes that never touch the session.

## Scope

### In Scope
- `SessionInterface::arm()` and `SessionInterface::isAvailable()`; `Session` lazily starts on first accessor call when armed
- `SessionMiddleware` arms instead of starting when there is no usable inbound cookie (absent or malformed)
- `SessionGuard` checks `isAvailable()`
- `FakeSession` supports the armed state
- Pinning test: `QueuedCookiesMiddleware` and `SessionMiddleware` have no `#[RunsOnUnmatched]`
- Docs: session.md lazy persistence / GC note, routing.md unmatched-request note

### Out of Scope
- Lazy start for requests carrying a well-formed cookie (option C; changes sliding expiry)
- Running session or queued cookies on unmatched requests

## Success Criteria
- [x] A cookieless matched request whose controller never touches the session makes zero handler calls
- [x] A cookieless request calling `set()` starts, saves and sends a cookie as before
- [x] A cookieless request calling only `get()`/`has()` starts lazily, reads, stores nothing and sends no cookie
- [x] `SessionGuard` login on a cookieless request regenerates and persists
- [x] Unarmed access still throws `SessionNotStartedException`
- [x] `FakeSession` supports the new state
- [x] Docs updated
- [x] All tests passing, `composer ci` green
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Session arm/isAvailable and lazy start | - | completed |
| 002 | SessionMiddleware arms cookieless requests | 001 | completed |
| 003 | FakeSession supports the armed state | 001 | completed |
| 004 | SessionGuard, CSRF and flash on an armed session | 001, 002, 003 | completed |
| 005 | Long-running reset of the armed state | 001, 002 | completed |
| 006 | Pin no RunsOnUnmatched on session/queued-cookie middleware + routing docs | - | completed |
| 007 | Session docs: lazy start and GC scheduling | 002, 003 | completed |

## Architecture Notes
- "Armed" is an explicit third state between not started and started. Only `SessionMiddleware` arms; nothing else auto-starts, so unprepared access still fails loudly.
- `save()`, `discard()`, `destroy()` and `reset()` disarm, so the armed state never outlives the request. Disarm happens before the `!started` early return, and destroying an armed but unstarted session only disarms (no handler call).
- `getId()`/`setId()` never trigger lazy start; `SessionMiddleware` guards with `isAvailable()` instead of `started`.
- `started` keeps meaning "session_start() ran"; consumers that need "usable" switch to `isAvailable()`.

## Risks & Mitigations
- Third-party code checking `$session->started`: documented; `isAvailable()` is the replacement.
- Lazy start skips the GC dice roll on cookieless traffic: docs tell busy anonymous-traffic sites to schedule `marko session:gc`.
