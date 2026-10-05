# Plan: HTTP Client Options and Fake

## Created
2026-10-05

## Status
completed

## Objective
Make `HttpClientInterface` options portable, validated and complete (unknown keys fail loudly, per-request `http_errors`), and add a driver-agnostic `FakeHttpClient` to `marko/testing`.

## Related Issues
Closes #175

## Discovery Notes
- `GuzzleHttpClient::buildOptions()` copies only `headers`, `body`, `json`, `query`, `timeout`; everything else is silently dropped. `http_errors` is hard-coded to `true`.
- `HttpResponse::isClientError()` / `isServerError()` are unreachable on returned responses today.
- `marko/testing` has ten fakes (FakeMailer, FakeQueue, ...) with `assertXxx()` methods throwing `AssertionFailedException`, plus Pest expectations in `src/Pest/Expectations.php`. None implement `HttpClientInterface`.
- Existing Guzzle tests already inject a `MockHandler` + history middleware through the protected `createClient()` seam.
- `marko/http` already requires `marko/core`, so `InvalidRequestOptionException` can extend `MarkoException`.
- Ticket is concrete (issue #175 has proposed approach and exit criteria), so no clarification round was needed.

## Scope

### In Scope
- `Marko\Http\RequestOptions` constants + shared `validate()` / `throwsOnHttpError()` helpers
- `Marko\Http\Exceptions\InvalidRequestOptionException` (unknown key, conflicting body options, malformed `auth`, non-bool `http_errors`, non-array `guzzle`)
- `GuzzleHttpClient`: forward every portable option, map `auth` (basic + bearer) and `allow_redirects` (int max), honour `http_errors`, `guzzle` escape-hatch key
- `FakeHttpClient` + `RecordedRequest` in `marko/testing`, with exact/wildcard stubs, sequential queue, stray-request prevention, throwing stubs, and `assertSent` / `assertNotSent` / `assertSentCount` / `assertNothingSent`
- `toHaveSentRequest` Pest expectation
- Docs: `http.md`, `http-guzzle.md`, `testing.md`; READMEs for `marko/http`, `marko/http-guzzle`, `marko/testing`
- Document the lossy `Set-Cookie` header flattening

### Out of Scope
- `config/http.php` `http.throw_on_error` default (optional in the ticket; per-request control is the requirement)
- Changing `HttpResponse::headers()` to multi-value lists (BC break; documented instead)
- Retries / middleware

## Success Criteria
- [x] Unknown option key throws `InvalidRequestOptionException` naming the key and listing supported keys
- [x] All portable options reach Guzzle (verified via history middleware)
- [x] `guzzle` escape hatch merged verbatim
- [x] Two body options throw
- [x] `http_errors => false` returns 4xx/5xx responses; default still throws with response attached
- [x] `FakeHttpClient` feature set with passing and failing assertion tests
- [x] `toHaveSentRequest` tested
- [x] Docs + READMEs updated
- [x] `composer ci` green

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | RequestOptions and InvalidRequestOptionException | - | completed |
| 002 | GuzzleHttpClient option forwarding, validation, http_errors, escape hatch | 001 | completed |
| 003 | RecordedRequest, stray-request exception, testing composer dependency | 001 | completed |
| 004 | FakeHttpClient | 001, 003 | completed |
| 005 | toHaveSentRequest Pest expectation | 004 | completed |
| 006 | Docs and READMEs | 002, 004, 005 | completed |

## Architecture Notes
- Validation lives in `marko/http` (`RequestOptions::validate()`) so the fake and every driver share one rule set and cannot drift. Drivers pass their own extra keys (e.g. `['guzzle']`).
- No Guzzle types in `marko/http` or `marko/testing`.
- Fake stub resolution order: first matching stub (registration order) → next queued response → stray handling (throw by default, or empty 200 when stray prevention is off).
- Fake throws `HttpException` with the response attached for 4xx/5xx when `http_errors` is true, matching the real driver.

## Risks & Mitigations
- Parallel tickets (#179, #182) edit `packages/testing/composer.json`, `Expectations.php`, `README.md`, `testing.md`: keep edits additive and rebase on `origin/develop` before opening the PR.
- Existing callers passing unsupported keys will now get an exception: intentional (loud errors); call out in PR.
