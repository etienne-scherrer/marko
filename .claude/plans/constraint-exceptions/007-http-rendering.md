# Task 007: HTTP 409 rendering and app integration suite

**Status**: completed
**Depends on**: 001, 004
**Retry count**: 0

## Description
Prove an uncaught unique violation in a controller renders 409 through the #169 pipeline, both with a hand-built router and end-to-end in the #187 app suite (flip the #177 todo).

## Context
- Related files: packages/database/tests/ (marko/routing is already require-dev; see EntityNotFoundExceptionTest for the pattern), tests/Integration/App/KnownGapsTest.php, tests/Integration/App/HarnessTest.php, tests/Integration/App/ServicesTest.php, Fixture/app/integration/src/Http/
- **Flipping the todo** (.claude/testing.md "Flipping a todo"):
  - Delete the `#177` `->todo(...)` block at the end of `KnownGapsTest.php`.
  - Put the real tests in a `tests/Integration/App/*Test.php` file (e.g. `ConstraintViolationTest.php`) with `pest()->group('integration-services')` and `setUpIntegrationTest`/`tearDownIntegrationTest`.
  - Tag each test `->issue(177)`. `HarnessTest` regex-scans `App/*Test.php` for `issue(177)` and fails without it.
- **Do not add a fixture migration.** The fixture has no unique column, and `ServicesTest` asserts `Applied 5 schema migration(s).` and an exact table list. Create the unique index inside the test with `ConnectionInterface::execute('CREATE UNIQUE INDEX authors_name_unique ON authors (name)')`. If you choose to add a migration instead, update `ServicesTest` in this task.
- **Route placement:** add a new fixture controller (e.g. `ConstraintController`) rather than editing `IntegrationController`, which is a hotspot with #171/#173. Inject `AuthorRepository`, and use a `#[Get]` route so the CSRF middleware is not involved. The route saves an Author whose name already exists.

## Requirements (Test Descriptions)
- [x] `it renders an uncaught unique violation from a controller as 409`
- [x] `it does not leak the constraint name or SQL into the 409 body`
- [x] `it throws a typed exception for a unique constraint violation` (app suite, real Postgres, `->issue(177)`)
- [x] `it answers 409 when a route saves a duplicate unique value` (app suite, `->issue(177)`)

## Acceptance Criteria
- All requirements have passing tests; HarnessTest still finds #177; ServicesTest unchanged and passing; no `#177` todo remains in KnownGapsTest

## Implementation Notes
