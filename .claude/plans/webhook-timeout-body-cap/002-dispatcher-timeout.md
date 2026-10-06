# Task 002: Pass webhook.timeout to the HTTP client in WebhookDispatcher

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Inject `WebhookConfig` into `WebhookDispatcher` and send `RequestOptions::TIMEOUT => $config->timeout` on the POST, so a hung receiver produces a `ConnectionException` instead of blocking the queue worker forever.

## Context
- Related files: packages/webhook/src/Sending/WebhookDispatcher.php, packages/webhook/tests/Sending/WebhookDispatcherTest.php, packages/webhook/tests/Jobs/*.php (constructor call sites)
- Patterns to follow: `FakeHttpClient` recorded requests (`$httpClient->requests[0]->options`)

## Requirements (Test Descriptions)
- [x] `it sends the configured webhook.timeout as the request timeout`
- [x] `it records a timed-out request as a failure and retries it` (DispatchWebhookJobResponseTest, `ConnectionException('cURL error 28: Operation timed out after 30001 milliseconds with 0 bytes received')`; assert `errorMessage` equals that message, a retry is queued, AND `$http->requests[0]->options[RequestOptions::TIMEOUT]` equals the configured timeout)
- [x] Existing dispatcher and job tests pass with the new constructor

## Details
- Constructor order: `HttpClientInterface $httpClient, ClockInterface $clock, WebhookConfig $config` (all promoted, class stays `readonly`, not final).
- `WebhookConfig` reads all four keys eagerly. Build the dispatcher's `WebhookConfig` from its own full config (`webhook.timeout`, `webhook.max_retries`, `webhook.retry_delay`, `webhook.timestamp_tolerance`), NOT from the job's `$config` array in `webhookJob()`: one test passes `config: []` to simulate missing retry config and must keep passing.
- Call sites to update: WebhookDispatcherTest.php (5), DispatchWebhookJobTest.php:101, DispatchWebhookJobRetryTest.php:102 and :248, DispatchWebhookJobResponseTest.php:69.
- Pest helper functions are global across the suite. Do not declare the same helper name in more than one file; construct `WebhookConfig` inline or use a file-unique helper name.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
