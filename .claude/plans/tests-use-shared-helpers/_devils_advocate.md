# Devil's Advocate Review: tests-use-shared-helpers

## Critical (Must fix before building)
None.

## Important (Should fix before building)

### 1. Neither driver sends `http_errors => false`. The "error status" tests change branch, and the Pusher assertion breaks (tasks 001, 002)
`_plan.md` Architecture Notes say error statuses "pass through because the drivers send `http_errors => false`". That is false. `MercureBroadcaster::sendRequest()` (line 89) and `PusherBroadcaster::sendRequest()` (line 84) only pass `headers`, `body` and `timeout`. `RequestOptions::throwsOnHttpError()` defaults to `true`, so for a 4xx/5xx response `FakeHttpClient::request()` throws `HttpException("HTTP request returned status code 413: POST <url>")`. The drivers' `catch (HttpException)` branch handles that exception. The `!$response->isSuccessful()` branch never runs.

Consequences:
- Pusher `it throws BroadcastException when the api answers with an error status` currently asserts `getContext()` contains `'HTTP 413: Payload too large'`. Under `FakeHttpClient` the context becomes `status code 413: POST http://soketi.test:6001/...`, so a mechanical port fails.
- Both drivers' `!isSuccessful()` branch loses test coverage. A 4xx/5xx can no longer reach it.

Fix applied: the plan notes now state the real behaviour. Tasks 001 and 002 now require each "error status" test to assert the actual `HttpException` path, with the status code in the context. Each task also adds one test that queues a non-2xx response that is not an error (for example `HttpResponse(304, '')`), so the `!isSuccessful()` branch keeps its coverage.

## Minor (Nice to address)
- Task 003 is already in place in `InProcessRequestHarness.php`. Only the ordering test and the docs sentence still need verifying.

## Questions for the Team
- With the real HTTP client, a hub/API error body (for example Pusher's "Payload too large") never reaches the `BroadcastException` message, because the client throws before `isSuccessful()` is checked. Should the drivers pass `http_errors => false` so the body appears in the error (a `src/` change, outside this plan's scope)? Otherwise, the `!isSuccessful()` branch is effectively dead for 4xx/5xx.
