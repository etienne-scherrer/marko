# Task 001: Migrate Mercure broadcaster tests to FakeHttpClient

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Replace the private `RecordingHttpClient` double in `marko/broadcasting-mercure` tests with the shipped `Marko\Testing\Fake\FakeHttpClient`, then delete the double.

## Context
- Related files: `packages/broadcasting-mercure/tests/Unit/Driver/MercureBroadcasterTest.php`, `packages/broadcasting-mercure/tests/Support/RecordingHttpClient.php` (delete)
- Patterns to follow: `packages/testing/src/Fake/FakeHttpClient.php`, `RecordedRequest::body()`/`header()`

## Requirements (Test Descriptions)
Existing test names are kept and now run against `FakeHttpClient`:
- [x] `it posts topic, data, type and id form fields to the hub url`
- [x] `it sends a bearer publisher jwt with a publish claim`
- [x] `it throws BroadcastException when the hub request fails`
- [x] `it throws BroadcastException when the hub answers with an error status` (queued 401: `FakeHttpClient` throws `HttpException` because the driver does not send `http_errors => false`, and that exception is wrapped as `BroadcastException`)
- [x] `it throws BroadcastException when the hub answers with a non-success status` (queue `new HttpResponse(304, '')`, assert the context contains `'hub responded with HTTP 304'`, which covers the `!isSuccessful()` branch)
- [x] `it broadcasts once per channel when dispatching a broadcastable`
- [x] No `RecordingHttpClient` file or reference remains in the package

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
Every test queues the responses it expects. A `ConnectionException` is queued to simulate a transport failure. Form fields are read through `RecordedRequest::body()` and headers through `header()`.
