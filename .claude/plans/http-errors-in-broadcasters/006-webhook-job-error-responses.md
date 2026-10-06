# Task 006: DispatchWebhookJob records and retries error responses

**Status**: completed
**Depends on**: 001, 005
**Retry count**: 0

## Description
Add `WebhookDeliveryService::recordRejection()` (status, capped body, error message). `DispatchWebhookJob` records success only for 2xx; records rejections otherwise and re-queues with the existing backoff for transport failures and retryable statuses.

## Context
- Related files: packages/webhook/src/Jobs/DispatchWebhookJob.php, packages/webhook/src/Sending/WebhookDeliveryService.php, packages/webhook/tests/Jobs/DispatchWebhookJobTest.php, packages/webhook/tests/Jobs/DispatchWebhookJobRetryTest.php, packages/webhook/tests/Sending/WebhookDeliveryServiceTest.php

## Requirements (Test Descriptions)
- [x] `it records the status and capped body of a rejected delivery`
- [x] `it records a 500 response as a failure and schedules a retry`
- [x] `it records a 4xx response as a final failure without retrying`
- [x] `it never records a non-2xx response as a success`
- [x] `it retries transport failures with exponential backoff`
- [x] `it stops retrying a retryable status once max retries is reached`
- [x] `it records a rejection without retrying when retry config is missing`
- [x] `it does not resend the webhook when recording a successful delivery fails`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Contract: `WebhookDeliveryService::recordRejection(WebhookPayload $payload, WebhookResponse $response, int $attempt): void`. It sets `statusCode`, sets `responseBody` to `new HttpResponse($response->statusCode, $response->body)->bodyExcerpt()` (`WebhookResponse` has no excerpt method, so do not add a second cap implementation), sets `errorMessage` to `Webhook receiver responded with HTTP {status}.`, and sets `attemptedAt`.
- Job flow: only `$dispatcher->dispatch()` goes inside `try { } catch (Throwable $e)`. A `Throwable` is a transport failure: `recordFailure` + retry. Recording happens outside the try, so a repository failure after a delivered webhook propagates loudly instead of re-sending it. 2xx → `recordSuccess`. Otherwise → `recordRejection`, then retry only if `$response->isRetryable()`.
- Share the retry branch between transport failures and retryable statuses: read config (missing config → no retry), then `attemptNumber < max_retries` → `queue->later(retry_delay * 2 ** attemptNumber, new self(..., attemptNumber + 1))`.
- Drive the job tests with `FakeHttpClient` behind a real `WebhookDispatcher` so the `http_errors => false` path is exercised end to end. The existing retry tests (anonymous client throwing `RuntimeException`) must keep passing.
