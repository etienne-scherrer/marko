# Task 004: Response::withoutBody and non-streaming HEAD for StreamingResponse

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `Response::withoutBody()` that empties the body while preserving the concrete subclass, status, headers and cookies, and make `StreamingResponse::send()` emit headers only when the body was omitted.

## Context
- Related files: packages/routing/src/Http/Response.php, packages/sse/src/StreamingResponse.php, packages/routing/tests/Http/ResponseTest.php, packages/sse/tests/
- API contract (task 005 calls these): `#[NoDiscard] public function withoutBody(): static` (clone-based like the other `with*()` methods; sets body to '' and a private `bodyOmitted` flag) and `public function isBodyOmitted(): bool`. Do not change `Response`'s constructor signature (subclasses call it).
- `Response::send()` sends status and headers and echoes nothing when the body was omitted.
- `StreamingResponse::send()` with the body omitted: send status and headers only, do NOT acquire a connection-limiter slot, do NOT call `prepareOutput()` or iterate the stream, but DO still call `$this->stream->close()`.

## Requirements (Test Descriptions)
- [x] `it removes the body while keeping status headers and cookies`
- [x] `it preserves the concrete response subclass when removing the body`
- [x] `it reports whether the body was omitted`
- [x] `it sends headers without streaming when the body was omitted`
- [x] `it closes the stream and acquires no connection slot when the body was omitted`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
