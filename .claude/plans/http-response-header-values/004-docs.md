# Task 004: Docs for header() and headerValues()

**Status**: completed
**Depends on**: 001, 002, 003
**Retry count**: 0

## Description
Document the new accessors in `http.md`, replace the lossy/middleware note in `http-guzzle.md` with a pointer to `headerValues()`, and show faking repeated headers in `testing.md`. READMEs are slim pointers and need no change unless they mention the lossy behaviour.

## Context
- Related files: packages/docs-markdown/docs/packages/http.md, http-guzzle.md, testing.md; docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `http.md documents header() and headerValues()`
- [x] `http-guzzle.md points to headerValues() instead of a middleware workaround`
- [x] `testing.md shows faking repeated headers`

## Acceptance Criteria
- Docs follow DOCS-STANDARDS
- `http.md`: the "Inspecting Responses" usage section gets a short `Set-Cookie` / `headerValues()` example. The "API Reference > HttpResponse" code block lists `header(string $name): ?string` and `headerValues(string $name): array`, and mentions the optional `headerValues:` constructor argument.
- The docs state that `headers()` reflects only the `headers` argument and is not derived from `headerValues`. Any example that builds an `HttpResponse` with repeated headers passes both arguments, or explicitly notes that `headers()` stays empty.
- `testing.md` edits stay inside the FakeHttpClient section (no reformatting or restructuring elsewhere), because #227 edits the same file in parallel.

## Implementation Notes
Implemented with TDD; see the PR for #220.
