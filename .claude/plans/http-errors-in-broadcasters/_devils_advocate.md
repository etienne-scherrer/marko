# Devil's Advocate Review: http-errors-in-broadcasters

## Critical (Must fix before building)

1. **Tasks 002/003/004: `rejected()` output format is undefined, and existing tests will break.**
   `PusherBroadcasterTest` ("api answers with an error status") asserts `getContext()` contains `status code 413` and `getPrevious()` is `HttpException`. Both stop being true once `http_errors => false` is sent. Both broadcaster tests also have a 304 case that asserts `hub responded with HTTP 304` / `server responded with HTTP 304`. Workers 003 and 004 will write assertions against a format that worker 002 invents in parallel. `rejected()` also needs a fallback suggestion for non-4xx/5xx statuses such as 304, because every non-2xx response now goes through it.
   *Fix:* pin the exact message/context format and a fallback suggestion in 002. Tell 003/004 to rewrite those existing tests.

## Important (Should fix before building)

2. **Task 006: the cap function is on the wrong type.** `bodyExcerpt()` lives on `HttpResponse`, but the job and the delivery service only have a `WebhookResponse`. Without a pinned contract, workers will improvise by adding a method to `WebhookResponse`, duplicating the cap, or changing the dispatcher.
   *Fix:* pin `recordRejection(WebhookPayload, WebhookResponse, int $attempt)`. Inside it, cap with `new HttpResponse($response->statusCode, $response->body)->bodyExcerpt()`, and set `errorMessage` to `Webhook receiver responded with HTTP {status}.`

3. **Task 001: the excerpt format is unspecified.** Downstream tests in 002/003/006 assert on it. Open points: trim before or after the cut, the exact truncation-note text, whether the note counts toward `$maxBytes`, and what an empty body returns.
   *Fix:* pin all four in 001.

4. **Tasks 005/006: the tests can pass without the fix.** The existing webhook tests use hand-rolled anonymous `HttpClientInterface`s that ignore `http_errors`. A 4xx test written that way passes even if the option is never sent. `FakeHttpClient` honours `http_errors` (it throws unless `false` is set).
   *Fix:* require `FakeHttpClient` for the new tests and list the real test files, which the plan omits.

5. **Task 006: the job's control flow is underspecified.** Today `recordSuccess()` sits inside `try { ... } catch (Throwable)`. If the repository save fails after a successful delivery, the job records a failure and re-sends the webhook. Restructuring the job without guidance will probably keep or spread this pattern to `recordRejection()`. Two cases are also unspecified: what happens when `webhook.max_retries`/`retry_delay` config is missing for a rejection, and whether a retryable status stops re-queuing at the limit.
   *Fix:* only `dispatch()` goes inside the try. Recording happens outside it. Spell out the missing-config and max-attempt behaviour, and add tests for both.

6. **Task 004: the redaction approach is vague.** "Redact the signed query string" needs a concrete rule so the worker doesn't depend on Guzzle's message format.
   *Fix:* replace the exact signed query string and the raw `auth_signature` value, with `previous: null`. Cover both with a test that uses a `ConnectionException` whose message embeds the full signed URL.

## Minor (Nice to address)

- `WebhookDispatcherInterface::dispatch()` has no `@throws` docblock. After this change it only throws on transport failure, which is worth stating.
- Guzzle follows redirects by default, so in practice a 3xx only reaches the senders when redirects are exhausted or disabled. Treating 3xx as a final, non-retryable webhook failure is fine but rarely exercised.
- `WebhookDispatcher` passes `json_encode()` output unchecked (false on failure). This is a pre-existing issue and out of scope.

## Questions for the Team

- Should successful webhook responses (`recordSuccess`) also store a capped body for consistency, or stay full-body as today? The plan leaves them full-body.
