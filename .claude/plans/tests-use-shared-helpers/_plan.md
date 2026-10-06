# Plan: Tests Use Shared Helpers

## Created
2026-10-05

## Status
completed

## Objective
Remove the private test doubles that duplicate shipped Marko helpers: the broadcasting driver tests use `Marko\Testing\Fake\FakeHttpClient` instead of two copies of `RecordingHttpClient`, and the RoadRunner `InProcessRequestHarness` resets through `Marko\Core\RequestStateResetter`, exactly as the worker does.

## Related Issues
Closes #223

## Discovery Notes
- `packages/broadcasting-mercure/tests/Support/RecordingHttpClient.php` and `packages/broadcasting-pusher/tests/Support/RecordingHttpClient.php` are identical apart from the namespace. Only two test files use them (`MercureBroadcasterTest`, `PusherBroadcasterTest`), not four as the issue says.
- `FakeHttpClient` already covers every need: `queue()`/`stub()` for canned responses and `ConnectionException` failures, `requests` holding `RecordedRequest` objects with `method`, `url`, `options`, `header()` and `body()`, plus `assertSent()`/`assertSentCount()`/`assertNothingSent()`. No capability needs adding, so `testing.md` needs no change.
- `FakeHttpClient` prevents stray requests by default, so each test must queue or stub the responses it expects. That is stricter than the old double, which answered everything with a canned 200.
- `InProcessRequestHarness::reset()` loops `resolvedInstances(ResettableInterface::class)` without `ksort()`. `RequestStateResetter::reset()` sorts by binding id. The worker (`WorkerRequestHandler`) and `TestClient` both use `RequestStateResetter`.
- `roadrunner-state-leaks.md` does not describe the harness's reset loop. One sentence in the Method paragraph will say that `reset()` delegates to `RequestStateResetter`.

## Scope

### In Scope
- Migrate the Mercure and Pusher broadcaster tests to `FakeHttpClient`, then delete both `RecordingHttpClient` files
- `InProcessRequestHarness::reset()` delegates to `RequestStateResetter`
- A harness test proving the reset order is the sorted order the worker's resetter uses
- A one-line accuracy note in `roadrunner-state-leaks.md`

### Out of Scope
- Any `src/` behaviour change in broadcasting or roadrunner packages
- New `FakeHttpClient` capabilities (none needed)
- Pusher presence channels (#228)

## Success Criteria
- [x] No `RecordingHttpClient` under `packages/broadcasting-*/tests`
- [x] Mercure and Pusher driver tests use `FakeHttpClient` and pass
- [x] Harness `reset()` delegates to `RequestStateResetter`, with an ordering test
- [x] All tests passing, `composer ci` green
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | Migrate Mercure broadcaster tests to FakeHttpClient | - | completed |
| 002 | Migrate Pusher broadcaster tests to FakeHttpClient | - | completed |
| 003 | Harness reset delegates to RequestStateResetter | - | completed |

## Architecture Notes
- Use `RecordedRequest::body()` to read the raw form/JSON body and `RecordedRequest::header()` to read headers. Use `$request->options['timeout']` for the timeout.
- Use `queue(new ConnectionException(...))` to simulate transport failure.
- Neither driver sends `http_errors => false`, and `RequestOptions::throwsOnHttpError()` defaults to true. For a queued/stubbed 4xx/5xx `HttpResponse`, `FakeHttpClient` therefore throws `HttpException("HTTP request returned status code {N}: POST {url}")`. The driver's `catch (HttpException)` branch handles it, not the `!isSuccessful()` branch. "Error status" tests must assert that path (status code in the context, not the response body). Add one test per driver that queues a non-2xx, non-error response (e.g. `new HttpResponse(304, '')`) so the `!isSuccessful()` branch stays covered.

## Risks & Mitigations
- Confirmed: the drivers do not set `http_errors => false`, so `FakeHttpClient` throws `HttpException` for 4xx/5xx where the old double returned the response. Pusher's error-status test currently asserts `'HTTP 413: Payload too large'` in the context, and that will fail. Assert `'status code 413'` instead, and cover `!isSuccessful()` with a 304 response.
- Harness ordering test could collide with fixture resettables: use uniquely named probe bindings and record only their own calls.
