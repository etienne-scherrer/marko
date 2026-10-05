# Task 005: TestClient request building

**Status**: completed
**Depends on**: 001, 003, 004
**Retry count**: 0

## Description
`Marko\Testing\Http\TestClient` with `boot()`/`forApplication()`, verb helpers, `*Json` helpers, header to `$server` mapping, query strings, server variables, `withFile()`, and request-state reset before each request.

## Requirements (Test Descriptions)
- [x] every verb helper sends its method (get, post, put, patch, delete, options, head)
- [x] `*Json` helpers send a JSON body with Content-Type and Accept headers
- [x] headers map to HTTP_* plus CONTENT_TYPE/CONTENT_LENGTH
- [x] query string parsed from the URI, and data used as the query for GET
- [x] REMOTE_ADDR defaults to 127.0.0.1 and is overridable via withServerVariables
- [x] withFile sends an UploadedFile
- [x] request-scoped state is reset between two requests on one client
- [x] exceptions from controllers propagate
- [x] form data for post/put/patch/delete reaches `Request::post()` (matching `Request::fromGlobals()`), JSON helpers put data only in the body
- [x] REQUEST_URI includes the query string and QUERY_STRING is set
- [x] two TestClient instances boot separate Applications (no static/shared app cache)

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Lifecycle: the Application is booted lazily, once per TestClient instance, and never cached statically. Task 007 mutates the container (`useGuard()`, `GuardInterface` instance), so a shared app would leak auth state between tests. `forApplication(Application)` wraps a caller-owned app.
- Reset uses `Marko\Core\RequestStateResetter` (task 001) before every request.
- Publish the public signatures (verbs, `*Json`, withHeaders/withServerVariables/withFile) in the class docblocks. Tasks 006, 007 and 009 build on them.
- head()/options() are verified against the fixture's custom HEAD/OPTIONS routes (task 004).
