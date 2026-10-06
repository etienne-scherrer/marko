# Task 005: WebhookDispatcher returns error responses; WebhookResponse::isRetryable

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`WebhookDispatcher::dispatch()` sends `http_errors => false` so every HTTP response becomes a `WebhookResponse`; only transport failures throw. Add `WebhookResponse::isRetryable()` (408, 429, 5xx).

## Context
- Related files: packages/webhook/src/Sending/WebhookDispatcher.php, packages/webhook/src/Value/WebhookResponse.php, packages/webhook/tests/Sending/WebhookDispatcherTest.php, packages/webhook/tests/Value/WebhookResponseTest.php (new)

## Requirements (Test Descriptions)
- [x] `it sends the webhook with http_errors disabled`
- [x] `it returns an unsuccessful response when the receiver answers with a 4xx or 5xx status`
- [x] `it treats 408, 429 and 5xx responses as retryable`
- [x] `it treats successful, 3xx and other 4xx responses as not retryable`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- The new dispatcher tests must use `Marko\Testing\Fake\FakeHttpClient` (marko/testing is already in require-dev). The existing test's hand-rolled anonymous client ignores `http_errors`, so a 4xx test built on it would pass without the fix.
- `WebhookResponse` keeps the full body (`recordSuccess` is unchanged). Capping happens in task 006.
- `isRetryable()`: `statusCode === 408 || statusCode === 429 || statusCode >= 500`.
