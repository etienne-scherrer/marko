# Task 003: MercureBroadcaster reads error responses

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Send `http_errors => false`, throw `BroadcastException::rejected()` with the capped body for non-2xx responses, keep `publishFailed` for transport failures.

## Context
- Related files: packages/broadcasting-mercure/src/Driver/MercureBroadcaster.php, packages/broadcasting-mercure/tests/Unit/Driver/MercureBroadcasterTest.php

## Requirements (Test Descriptions)
- [x] `it sends the publish request with http_errors disabled`
- [x] `it puts the hub status and response body in the exception when the hub rejects the update`
- [x] `it caps a large hub error body in the exception`
- [x] `it throws BroadcastException when the hub cannot be reached`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Every non-2xx response, including 3xx, goes to `BroadcastException::rejected(self::DRIVER, $channel->name, $response->statusCode(), $response->bodyExcerpt())`.
- Use `FakeHttpClient` (already used in this test file). It throws `HttpException` for 4xx/5xx unless `http_errors => false` is sent, so the rejection tests prove the option is set.
- Update the existing tests "hub answers with a non-success status" (it asserts `hub responded with HTTP 304`) and "hub answers with an error status" to the context format pinned in task 002.
