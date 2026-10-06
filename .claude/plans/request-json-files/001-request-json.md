# Task 001: Request JSON support

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add `isJson()`, `wantsJson()`, `json()` and `input()` to `Request`, decoding JSON bodies once and throwing a dedicated `MalformedJsonException` when a malformed payload is accessed.

## Context
- Related files: `packages/routing/src/Http/Request.php`, `packages/routing/src/Exceptions/MalformedJsonException.php` (new), `packages/routing/tests/Http/RequestTest.php`
- Patterns to follow: `MarkoException` named constructors (`InvalidRouteParameterException`)

## Requirements (Test Descriptions)
- [x] `it detects application/json content type as json`
- [x] `it detects structured +json content types such as application/vnd.api+json as json`
- [x] `it does not treat form content types as json`
- [x] `it reports wantsJson when the Accept header asks for json`
- [x] `it returns the decoded json body`
- [x] `it reads nested json values with dot-notation keys`
- [x] `it returns the default for a missing json key`
- [x] `it returns an empty array from json() for an empty json body`
- [x] `it does not decode the body of a non-json request`
- [x] `it throws MalformedJsonException when a malformed json body is accessed`
- [x] `it includes the body length and decoder error in MalformedJsonException`
- [x] `it reads input from the json body for json requests`
- [x] `it reads input from post data for form requests`
- [x] `it falls back to the query string from input()`
- [x] `it merges query and body from input() with no key, body winning`

## Acceptance Criteria
- All requirements have passing tests
- Request stays a readonly class

## Implementation Notes
Implemented directly by the ticket agent with TDD (nested subagents were unavailable, so the devils-advocate post-plan review did not run). See the PR description for design notes.
