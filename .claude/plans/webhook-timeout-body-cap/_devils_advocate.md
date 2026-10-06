# Devil's Advocate Review: webhook-timeout-body-cap

## Critical (Must fix before building)
None. Targeted classes, methods, and options exist with the expected signatures (`RequestOptions::TIMEOUT`, `HttpResponse::bodyExcerpt(int $maxBytes = 500)`, `RecordedRequest::$options`, `ConfigException(message, context, suggestion)`).

## Important (Should fix before building)
1. **Task 002: `webhookJob()` helper in DispatchWebhookJobResponseTest.php passes `config: []`** for the "retry config is missing" test. If the worker builds `WebhookConfig` from that same `$config` array, `new WebhookConfig(...)` throws `ConfigNotFoundException` (it reads all four keys eagerly), breaking that test and every call that only passes `max_retries`/`retry_delay`. The dispatcher's `WebhookConfig` must come from its own full four-key config (`timeout`, `max_retries`, `retry_delay`, `timestamp_tolerance`), separate from the job's retry config. Same applies to DispatchWebhookJobTest.php:101, DispatchWebhookJobRetryTest.php:102/248, and WebhookDispatcherTest.php (5 call sites).
2. **Task 002: Pest helpers are global functions.** `webhookJob()` and `webhookDispatcherPayload()` already exist; adding a shared `webhookConfig()` helper in more than one test file causes "Cannot redeclare function" in a full run. Use one uniquely named helper per file or construct inline.
3. **Task 002: timeout test should also prove the timeout reached the client.** The job-level test only proves a thrown `ConnectionException` is retried, which is already true today. Assert `$http->requests[0]->options[RequestOptions::TIMEOUT]` equals the configured value in the same test, and use a realistic Guzzle message (`cURL error 28: Operation timed out after 30001 milliseconds ...`) asserted in `errorMessage`.
4. **Task 001: exception content and docblock.** The requirements don't specify message/context/suggestion. Spell them out (key name, `Got N`, suggestion) and update the constructor `@throws` to `ConfigException|ConfigNotFoundException` so PHPStan and callers see it. Note `WebhookReceiver` also constructs `WebhookConfig`, so receive-only apps now fail loudly on a bad timeout too; acceptable but must be documented (task 004).
5. **Task 003: `bodyExcerpt()` trims the body.** Successful bodies are now stored trimmed (`" OK\n"` becomes `"OK"`), a small behavior change for `recordSuccess()`. Add a test that pins it and mention it in the docs (task 004).

## Minor (Nice to address)
- Bad `webhook.timeout` now surfaces when `DispatchWebhookJob::handle()` resolves the dispatcher from the container (outside its try/catch), so the job throws instead of recording an attempt. This is loud and correct, but the queue worker's failed-job handling is what reports it.
- `excerpt()` builds a throwaway `HttpResponse` per call; fine, but a one-line comment about why (reuse the UTF-8-safe cut) helps.
- Guzzle maps cURL errno 28 to `ConnectException` (and so `ConnectionException`), but other handlers might surface a timeout as `HttpException` without a response. `DispatchWebhookJob` catches `Throwable`, so both get retried. No change needed.

## Questions for the Team
- Should `WebhookConfig` also validate `max_retries`, `retry_delay`, `timestamp_tolerance` while touching it, or leave that to #289? (Plan leaves it, which is consistent with the issue scope.)
