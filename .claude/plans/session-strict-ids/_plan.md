# Plan: Strict Session IDs

## Created
2026-10-05

## Status
completed

## Objective
Make `session.use_strict_mode=1` real: session handlers validate inbound ids, so a well-formed cookie for an unknown or expired session is discarded (fresh id, nothing stored, expired cookie sent) instead of being adopted and written on every request.

## Related Issues
Closes #266
Relates to #267 (lazy session start builds on these semantics), #260/#236 (lazy persistence)

## Discovery Notes
- `Marko\Session\Contracts\SessionHandlerInterface` extends only PHP's `SessionHandlerInterface`. For a user save handler PHP's default `validate_sid` calls `read()` and only fails when `read()` returns `false`; both shipped handlers return `''` for a missing id, so strict mode adopts any well-formed id.
- Without `updateTimestamp()`, PHP's user module falls back to `write()` for unchanged data under `lazy_write`, so every resumed request rewrites the payload.
- Handlers: `FileSessionHandler` (`marko/session-file`, has `SessionConfig` + clock), `DatabaseSessionHandler` (`marko/session-database`, has connection + clock, no config yet). Test doubles implementing the interface: in-memory handler in `SessionTest`, anonymous handler in `SessionShutdownHandlerTest`, `RecordingSessionHandler` in `StatelessRouteTest`.
- `session_regenerate_id()` resets PHP's `session_vars`, so the first save after a regenerate always calls `write()` (never `updateTimestamp()`) — regenerated sessions are not lost. Covered by a test.
- RoadRunner path: one long-running process, `Session::reset()` between requests, `session_set_save_handler()` registered once per process. Covered through `InProcessRequestHarness` with the file driver fixture app.
- `InvalidSessionIdException::forId()` echoes the raw (attacker-supplied) id into the exception context; the ticket forbids surfacing it.
- `FakeSession` implements `SessionInterface`, which this plan does not change.

## Scope

### In Scope
- `validateId()` + `updateTimestamp()` on the handler contract and both shipped handlers, lifetime checked through the injected PSR-20 clock
- `SessionMiddleware` expires a rejected inbound cookie (unknown, expired, or malformed id) when nothing was persisted
- Remove the raw id from `InvalidSessionIdException`
- Regression tests for create+destroy without cookie and destroy with cookie
- File, database and RoadRunner (in-process worker) integration tests
- Docs: session.md, session-file.md, session-database.md

### Out of Scope
- Lazy session start (#267)
- Compatibility shim for third-party handlers (pre-1.0, breaking by design)

## Success Criteria
- [x] Unknown well-formed cookie: no handler `write`, fresh id, `isModified()` false, expired cookie on the response
- [x] Repeating the same request N times creates no session file or row
- [x] Known unmodified session refreshes expiry via `updateTimestamp()` without rewriting the payload
- [x] Expired-but-present id is treated as unknown
- [x] Regression tests for create/destroy without cookie and destroy with cookie
- [x] `FakeSession` still satisfies the contract
- [x] Docs updated
- [x] All tests passing, `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Handler contract gains validateId/updateTimestamp; Session strict-id behaviour | - | completed |
| 002 | FileSessionHandler validateId/updateTimestamp (+ clock-stamped write, stat cache) | 001 | completed |
| 003 | DatabaseSessionHandler validateId/updateTimestamp (+ SessionConfig injection) | 001 | completed |
| 004 | SessionMiddleware expires rejected cookies (updates existing malformed-cookie test; fake gains rejectOnStart) | 001 | completed |
| 005 | Integration: file, database and RoadRunner worker paths | 002, 003, 004 | completed |
| 006 | Docs | 002, 003, 004 | completed |

## Architecture Notes
- The contract extends `\SessionUpdateTimestampHandlerInterface`; PHP wires `validateId`/`updateTimestamp` automatically from the registered object.
- Lifetime = `SessionConfig::lifetime()` minutes, compared against `ClockInterface::now()`.
- `updateTimestamp()` never creates a record: it only refreshes an existing one. It returns `true` even when the record is gone, because `false` makes PHP raise an E_WARNING.
- `Session::configure()` pins `session.lazy_write=1`; the `updateTimestamp()` path depends on it.
- The file driver uses one time source: `write()` and `updateTimestamp()` both `touch()` with the clock, and `validateId()` clears the stat cache first (it persists across requests in long-running workers).

## Risks & Mitigations
- Data loss if `updateTimestamp()` is called for a new id after regenerate: verified PHP resets `session_vars` on regenerate; test pins it.
- Third-party handlers break: called out in PR as breaking.
