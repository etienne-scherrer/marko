# Task 003: Docs update

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Describe the new 401/403 responses in the admin-auth docs page and link to the routing docs' exception-rendering section.

## Context
- Related files: packages/docs-markdown/docs/packages/admin-auth.md (lines ~36 and ~40 describe the old `{"error":"Forbidden"}` / 401 JSON behaviour), packages/docs-markdown/docs/packages/admin-api.md (line ~6 claims all responses use the `{errors}` envelope; line ~20 says unauthenticated requests receive a 401 JSON response)
- Patterns to follow: packages/docs-markdown/docs/packages/authentication.md (Failure responses), docs/DOCS-STANDARDS.md

## Requirements (Test Descriptions)
- [x] `docs describe the redirect for browser requests and the 401 for JSON requests`
- [x] `docs describe the 403 rendered by ExceptionRenderer with the {"message":"Forbidden."} JSON body`
- [x] `docs link to /docs/packages/routing/#errors-and-http-exceptions and #custom-error-pages`
- [x] `admin-api docs state that middleware 401/403 denials use the {"message": ...} body (not the {errors} envelope) and that the 401 applies to requests whose Accept header asks for JSON, others are redirected to the admin login`

## Acceptance Criteria
- Docs accurate against the code

## Implementation Notes
(Left blank - filled in by programmer during implementation)
