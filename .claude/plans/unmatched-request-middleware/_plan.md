# Plan: Unmatched Request Middleware

## Created
2026-10-05

## Status
completed

## Objective
Stop stateful global middleware (session, CSRF, auth, authorization) from running on requests that match no route, via an explicit opt-in `#[RunsOnUnmatched]` middleware attribute (option B of #236), and make session persistence lazy so no request creates a session it never used (option D).

## Related Issues
Closes #236
Relates to #233 (lazy `#[Can]` resolution — runs in parallel; this plan does not touch `marko/authorization`)

## Discovery Notes
- `Router::dispatch()` sends unmatched requests through `$this->globalMiddleware` with a terminal handler that throws 404/405 or answers OPTIONS with 204 + `Allow`.
- `Router::middlewareFor()` already filters global middleware for matched routes (`#[WithoutMiddleware]`); the unmatched path gets a sibling `unmatchedMiddleware()`.
- First-party global middleware: Cors, Session (session-file / session-database), Authentication, Authorization, Layout, PageCache. Layout and PageCache already pass through when no route matched, so only CORS must opt in (its preflights for paths without an explicit OPTIONS route are unmatched requests).
- `SessionMiddleware` starts the session on every request and always saves + sets a cookie for new sessions. `Session` keeps data in `$data` (FlashBag mutates it by reference), so "modified" is a snapshot comparison.
- `SessionInterface` implementors: `Session`, `Marko\Testing\Fake\FakeSession`, an anonymous fake in `SessionMiddlewareTest`.
- Decision issue: implementing the issue's recommendation (B + D) without waiting for the maintainer, per instructions.

## Scope

### In Scope
- `Marko\Routing\Attributes\RunsOnUnmatched` class attribute; Router runs only opted-in global middleware on unmatched requests
- `CorsMiddleware` opts in
- `SessionInterface::isModified()` and `SessionInterface::discard()`; implemented in `Session` and `FakeSession`
- `SessionMiddleware` persists (and sets a cookie) only when an inbound session cookie exists or the session was modified; otherwise discards without writing
- Tests for every exit criterion of #236 (routing, cors, security, session, session-database)
- Docs: routing.md, session.md, security.md, cors.md, testing.md (FakeSession), architecture.md

### Out of Scope
- `marko/authorization` changes (#233)
- Avoiding the session *read* on cookieless requests (start is still eager; only persistence is lazy)

## Success Criteria
- [x] POST to unknown path with global CsrfMiddleware → 404; POST to GET-only path → 405 with Allow
- [x] CORS preflight to known path → 204 with CORS headers; to unknown path behaves as documented
- [x] 404 with session-database: no session write, no cookie
- [x] Matched route not touching session creates none; one that writes does
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | RunsOnUnmatched attribute and router filtering | - | completed |
| 002 | CORS opts in to unmatched requests | 001 | completed |
| 003 | Session tracks modification and can discard | - | completed |
| 004 | Lazy session persistence in SessionMiddleware | 003 | completed |
| 005 | Session-database integration tests (404 + lazy) | 001, 004 | completed |
| 006 | CSRF unmatched-request tests | 001 | completed |
| 007 | Documentation | 001-006 | completed |

## Architecture Notes
- Attribute over interface method: no change to `MiddlewareInterface`, discoverable by reflection, mirrors `#[WithoutMiddleware]`.
- The attribute is read from the class name declared in `globalMiddleware` (not the Preference that replaces it), so a Preference keeps the declared middleware's behaviour.
- Default for third-party global middleware: does not run on unmatched requests (documented, `breaking`).

## Risks & Mitigations
- Apps relying on custom global middleware for 404 pages: documented; they add `#[RunsOnUnmatched]`.
- CSRF tokens need a session: generating a token writes to the session, which marks it modified, so it is persisted.
- Custom 404/405 templates that read session/auth will throw `SessionNotStartedException` (session middleware no longer runs on unmatched requests): documented in task 007.
- Malformed inbound session cookies count as "no cookie" (only a resumed id forces persistence), so garbage cookies from bots cause no writes.
- Existing tests that encode the old behaviour must be updated: `WithoutMiddlewareTest` (001), the `SessionMiddlewareTest` anonymous fake (003), cookie-on-new-session cases in `SessionMiddlewareTest` (004), RoadRunner `EndToEndTest` `/session/read` cookie test (004, `integration-destructive`).
