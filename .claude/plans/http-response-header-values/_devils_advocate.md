# Devil's Advocate Review: http-response-header-values

## Critical (Must fix before building)

None. The plan is small and its targets exist as described: `HttpResponse` is a promoted `readonly` value object, `GuzzleHttpClient` builds `HttpResponse` in exactly two places (success and `RequestException`), and `FakeHttpClient::request()` returns or wraps the caller's own `HttpResponse` instance (it never rebuilds it), so `headerValues` survives the fake with no source change.

## Important (Should fix before building)

1. **Task 001: the requirements don't list the case-merge behaviour.** `_plan.md` says that values whose keys differ only in case are merged in order, and `packages/http/tests/Unit/HttpResponseTest.php` already has `it merges values whose header names differ only in case, in order`. The task's requirement list leaves it out, so the worker or reviewer could treat that test as out of scope. Fix: add the requirement.

2. **Task 002: PHPStan level 6 and `list<string>`.** Guzzle's `MessageInterface::getHeaders()` is declared as `string[][]`, not `array<string, list<string>>`. If the new `HttpResponse` parameter is documented as `array<string, list<string>>`, passing `getHeaders()` straight in could make `composer phpstan` (a CI gate) report an argument type mismatch. Fix: in the driver, map the values through `array_values()` with a small private helper, or a typed loop, so the type is a list. Run `composer phpstan` as part of acceptance.

3. **Task 002: a stale docblock.** `flattenHeaders()` still says "This is lossy for headers such as Set-Cookie". Once raw values are passed through, that comment is misleading. Fix: update it to point readers to `HttpResponse::headerValues()`.

4. **Task 004: the `http.md` API Reference block also needs updating.** It lists `HttpResponse` methods in a code block (lines 152-167), and the "Inspecting Responses" prose only mentions status and body. The task names `http.md` but not the API Reference block. Fix: add `header()` and `headerValues()` (and the `headerValues:` constructor argument) to the API Reference, and give a short `Set-Cookie` example in the usage section.

5. **Task 004: `headers()` is not derived from `headerValues`.** The "keeps headers unchanged" test pins this down, so a fake built only with `headerValues:` returns `[]` from `headers()`. The `testing.md` example must not mislead here. Fix: the docs should either pass both arguments in the example or say plainly that `headers()` only reflects the `headers` argument.

6. **Task 004: `testing.md` is shared with #227.** #227 (TestClient) runs in parallel in `packages/testing` and is likely to edit `testing.md`. Fix: limit the edit to the FakeHttpClient section, with no restructuring, reformatting or API-reference reshuffling outside it, to keep the merge clean.

## Minor (Nice to address)

- **Task 003: overlapping tests.** `FakeHttpClient` returns the exact instance it was given, so the "stubbed" and "queued" tests check the same pass-through. One test would be enough, which fits the "lean" goal and touches less of the shared testing package.
- **Task 001: input shape isn't validated.** A caller passing `['Set-Cookie' => 'a=1']` (a string instead of a list) would hit a generic `TypeError` deep inside the lookup or merge. Under the "loud errors" principle, the constructor body could check the shape and throw a clear exception. It's optional because PHP already fails loudly, just less helpfully.
- **Task 002: the `http_errors => false` path isn't tested separately.** It runs through the same success branch, so no separate test is needed, but you could add one assertion to the existing test.

## Questions for the Team

- When only `headerValues` is given, should `headers()` fall back to a `", "`-joined view of it? Callers that don't pass `headerValues` would see no change, and it would remove the inconsistency in Important #5. The current plan explicitly leaves `headers()` untouched.
