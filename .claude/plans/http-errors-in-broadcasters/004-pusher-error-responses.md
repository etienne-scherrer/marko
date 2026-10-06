# Task 004: PusherBroadcaster reads error responses without leaking the signature

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Send `http_errors => false`, throw `BroadcastException::rejected()` for non-2xx. For transport failures, redact the signed query string from the message and do not chain the original exception (its message carries the signed URL).

## Context
- Related files: packages/broadcasting-pusher/src/Driver/PusherBroadcaster.php, packages/broadcasting-pusher/tests/Unit/Driver/PusherBroadcasterTest.php

## Requirements (Test Descriptions)
- [x] `it sends the trigger request with http_errors disabled`
- [x] `it puts the api status and response body in the exception when the api rejects the event`
- [x] `it does not put the signed query string in the exception when the api rejects the event`
- [x] `it redacts the signed query string from transport failure messages`
- [x] `it does not chain the transport exception that carries the signed url`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Every non-2xx response, including 3xx, goes to `BroadcastException::rejected(self::DRIVER, $channel->name, $response->statusCode(), $response->bodyExcerpt())`.
- Transport failure: `publishFailed(self::DRIVER, $channel->name, $redacted, null)`. To build `$redacted`, replace the exact signed query string (`http_build_query($query)`) with `[redacted]` in `$e->getMessage()`, then also replace any remaining occurrence of the raw `auth_signature` value with `[redacted]`. This survives Guzzle wording and encoding differences.
- Tests: use a `ConnectionException` whose message embeds the full signed URL (build it from the recorded request URL or reconstruct it). Assert the message/context contain neither `auth_signature=` nor the signature value, and that `getPrevious()` is null.
- Existing tests to rewrite: "api answers with an error status" asserts `status code 413` and `getPrevious()` is `HttpException`, and neither holds after this change. "api answers with a non-success status" asserts `server responded with HTTP 304`. Move both to the context format pinned in task 002.
