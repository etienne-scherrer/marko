# Task 010: Stateless Session Routes

**Status**: completed
**Depends on**: 005
**Retry count**: 0

## Description
Prove the session use case end to end: a route with `#[WithoutMiddleware(SessionMiddleware::class)]` sends no `Set-Cookie` and never touches the session handler, while code that needs the session fails loudly.

## Context
- Related files: packages/session/tests, tests/Integration/App/Fixture/app/integration/src/Http, tests/Integration/App/ServicesTest.php
- Unit tests: construct `Router` directly with `[SessionMiddleware::class]` as global middleware (no bootstrapper needed).
- Integration: add a stateless route (e.g. `GET /stateless`) with `#[WithoutMiddleware(SessionMiddleware::class)]` to the fixture `IntegrationController`. Boot validation passes only because the fixture's `config/session.php` uses the `database` driver (session-database registers `SessionMiddleware` globally). Model the test on `it routes a request through the global session middleware and persists the session row` in `ServicesTest.php`. It runs in the `integration-services` group (Postgres + Redis), so `composer test` alone won't exercise it; run that group explicitly.

## Requirements (Test Descriptions)
- [x] `it sends no session cookie from a route without the session middleware`
- [x] `it does not touch the session handler on a route without the session middleware`
- [x] `it throws SessionNotStartedException when a stateless route reads the session`
- [x] `it serves a stateless route without creating a session row` (integration)

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
