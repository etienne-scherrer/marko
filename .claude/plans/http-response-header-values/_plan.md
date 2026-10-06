# Plan: HTTP Response Header Values

## Created
2026-10-05

## Status
completed

## Objective
Give `HttpResponse` a lossless, case-insensitive multi-value header API (`header()` and `headerValues()`) so repeated headers such as `Set-Cookie` survive intact, without changing `headers()`.

## Related Issues
Closes #220

## Discovery Notes
- `Marko\Http\HttpResponse` (`packages/http/src/HttpResponse.php`) is a `readonly` value object holding `array<string, string>` headers.
- `GuzzleHttpClient::flattenHeaders()` joins Guzzle's `array<string, list<string>>` with `", "` in two places (success response and the response attached to `RequestException`).
- `FakeHttpClient` (`packages/testing`) takes `HttpResponse` instances, so a new constructor parameter is enough; only a test and docs are needed there. #227 (TestClient) runs in parallel in `packages/testing`, so testing-package edits stay limited to one test.
- Docs: `http.md` HttpResponse section, `http-guzzle.md` "Response Headers" (lossy-note), `testing.md` FakeHttpClient section.

## Scope

### In Scope
- `HttpResponse` constructor parameter `headerValues` (`array<string, list<string>>`), derived from `headers` when empty
- `HttpResponse::header(string $name): ?string` and `HttpResponse::headerValues(string $name): list<string>`, case-insensitive
- `GuzzleHttpClient` passes raw multi-value headers
- `FakeHttpClient` test for repeated headers
- Docs for http, http-guzzle, testing

### Out of Scope
- Changing `headers()` signature or behaviour
- Cookie parsing helpers

## Success Criteria
- [x] Two comma-containing `Set-Cookie` values come back intact and in order from `headerValues('set-cookie')`
- [x] `header()`/`headerValues()` are case-insensitive and return `null`/`[]` when missing
- [x] Built without `headerValues`, the response derives them from `headers`
- [x] Guzzle passes raw multi-value headers; the comma-join test still passes
- [x] FakeHttpClient faked response exposes repeated headers
- [x] Docs updated
- [x] All tests passing
- [x] Code follows project standards

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | HttpResponse header() and headerValues() | - | completed |
| 002 | GuzzleHttpClient passes raw multi-value headers | 001 | completed |
| 003 | FakeHttpClient repeated-header test | 001 | completed |
| 004 | Docs: http, http-guzzle, testing | 001, 002, 003 | completed |

## Architecture Notes
- Additive only: `headers()` keeps `array<string, string>`.
- `headerValues` stays keyed by the original header names; lookups compare names with `strcasecmp`. When two keys differ only in case, values are merged in order (HTTP header names are case-insensitive).
- Derivation happens at lookup time: if `headerValues` is empty, `headers` are treated as one-element lists. This keeps the promoted readonly constructor intact.
- `header()` joins `headerValues($name)` with `", "` and returns `null` when that list is empty.

## Risks & Mitigations
- Inconsistent `headers` and `headerValues` passed by a driver: `headerValues` is authoritative for the new accessors whenever it is non-empty; documented.
- `headers()` is not derived from `headerValues`, so a response built only with `headerValues:` returns `[]` from `headers()`. The docs must say so, and examples pass both (task 004).
- PHPStan level 6: Guzzle's `getHeaders()` is `string[][]`, so the driver normalises the values to lists before passing them in (task 002).
- `testing.md` is also edited by #227, so task 004 keeps its edits inside the FakeHttpClient section.
