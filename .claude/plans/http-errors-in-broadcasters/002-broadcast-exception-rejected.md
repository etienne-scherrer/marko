# Task 002: BroadcastException::rejected

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `BroadcastException::rejected(driver, channel, status, body)` whose context carries the status and body and whose suggestion depends on the status.

## Context
- Related files: packages/broadcasting/src/Exceptions/BroadcastException.php, packages/broadcasting/tests

## Requirements (Test Descriptions)
- [x] `it reports the status and body of a rejected publish in the context`
- [x] `it suggests checking credentials when a publish is rejected with 401 or 403`
- [x] `it suggests shrinking the payload when a publish is rejected with 413`
- [x] `it suggests checking the event, channel and payload for other 4xx rejections`
- [x] `it reports a server-side failure for 5xx rejections`
- [x] `it gives a generic suggestion for non-success statuses outside 4xx and 5xx`
- [x] `it notes an empty body in the context of a rejected publish`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
Contract (tasks 003/004 assert on this):
- Signature: `rejected(string $driver, string $channel, int $status, string $body): self`. `$body` is the already-capped `HttpResponse::bodyExcerpt()`. `rejected()` does not cap or redact it.
- message: `Failed to broadcast to channel '$channel' via $driver.` This is the same wording as `publishFailed`, so existing `toThrow(..., "Failed to broadcast ...")` assertions keep working.
- context: `The $driver server responded with HTTP $status: $body`. When `$body === ''`, use `(empty body)` in its place.
- No `previous`.
- Suggestions: 401/403 → check the credentials in `config/broadcasting-{driver lowercased}.php`. 413 → shrink the payload. Other 4xx → check the event name, channel and payload. 5xx → server-side failure, retry later and check the server logs. Anything else (e.g. 304) → a generic "expected a 2xx response" suggestion.
