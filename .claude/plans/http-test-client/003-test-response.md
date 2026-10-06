# Task 003: TestResponse

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`Marko\Testing\Http\TestResponse` wraps a `Response` and offers assertions that throw `AssertionFailedException` (message includes status and a body excerpt) plus accessors.

## Context
- Related files: packages/routing/src/Http/Response.php, packages/routing/src/Http/Cookie.php, packages/testing/src/Exceptions/AssertionFailedException.php, packages/testing/composer.json, packages/testing/tests/PackageStructureTest.php
- `Cookie` currently exposes only name()/path()/domain()/toSetCookieString(); value and expiry are unreadable. This task adds accessors (task 006 depends on them).
- This task owns the `marko/routing` require in `marko/testing`'s composer.json (task 004 must not touch composer.json).

## Requirements (Test Descriptions)
- [x] routing: `Cookie` exposes value(), expires(), secure(), httpOnly(), sameSite() accessors (tests in packages/routing)
- [x] `marko/testing` requires `marko/routing` (PackageStructureTest assertion)
- [x] status assertions pass and fail: assertStatus, assertOk, assertCreated, assertNoContent, assertNotFound, assertForbidden, assertUnauthorized, assertUnprocessable
- [x] `assertRedirect` with and without a target
- [x] header and cookie assertions: assertHeader, assertHeaderMissing, assertCookie, assertCookieMissing
- [x] body assertions: assertSee, assertDontSee
- [x] JSON assertions: assertJson, assertExactJson, assertJsonPath, assertJsonCount, assertJsonMissingPath
- [x] accessors: json, body, status, response
- [x] `it includes the status and a body excerpt in failure messages`

## Acceptance Criteria
- All requirements have passing tests (passing and failing case each)

## Implementation Notes
- Header lookups (assertHeader, assertHeaderMissing, assertRedirect's Location) are case-insensitive; `Response::headers()` keeps the casing used when set.
- assertCookie($name, ?string $value = null) compares against `Cookie::value()`.
