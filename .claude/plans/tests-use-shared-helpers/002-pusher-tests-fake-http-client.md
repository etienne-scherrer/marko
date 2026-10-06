# Task 002: Migrate Pusher broadcaster tests to FakeHttpClient

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Replace the private `RecordingHttpClient` double in `marko/broadcasting-pusher` tests with `FakeHttpClient`, then delete the double. Presence-channel tests (#228) are then written against the shared fake from the start.

## Context
- Related files: `packages/broadcasting-pusher/tests/Unit/Driver/PusherBroadcasterTest.php`, `packages/broadcasting-pusher/tests/Support/RecordingHttpClient.php` (delete)
- Patterns to follow: `FakeHttpClient::stub()`/`queue()`, `assertNothingSent()`

## Requirements (Test Descriptions)
Existing test names are kept and now run against `FakeHttpClient`:
- [x] `it posts name, channels and json data to the events endpoint`
- [x] `it signs the request with auth query parameters`
- [x] `it rejects channel names with characters Pusher does not allow` (now via `assertNothingSent()`)
- [x] `it throws BroadcastException when the api request fails`
- [x] `it throws BroadcastException when the api answers with an error status`: the driver does not send `http_errors => false`, so `FakeHttpClient` throws `HttpException` for the 413. Change the context assertion from `'HTTP 413: Payload too large'` to `'status code 413'`. The response body does not reach the message on this path.
- [x] `it throws BroadcastException when the api answers with a non-success status` (stub `new HttpResponse(304, '')`, assert the context contains `'server responded with HTTP 304'`, which covers the `!isSuccessful()` branch)
- [x] No `RecordingHttpClient` file or reference remains in the package

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
A stub on `http://soketi.test:6001/apps/3/events*` answers the signed events endpoint whatever its query string is. Failures are simulated by stubbing a `ConnectionException` or a 413 response.
