# Task 010: Flip the #171 integration todos into real tests

**Status**: completed
**Depends on**: 005, 007
**Retry count**: 0

## Description
Turn the two #171 todos in the integration suite into real tests against the booted fixture app, per the KnownGapsTest hand-off convention.

## Context
- Related files: tests/Integration/App/KnownGapsTest.php (lines 154-159), tests/Integration/App/Helpers.php (`INTEGRATION_MODULES`), tests/Integration/App/Fixture/config/, tests/Integration/App/Fixture/app/integration/src/Http/IntegrationController.php (`#[Get('/health')]`), tests/Integration/App/HarnessTest.php
- Add `'cors'` to `INTEGRATION_MODULES` and a fixture `config/cors.php` allowing a test origin (e.g. `https://app.example.test`) with `paths => ['*']`.
- Move the tests into a file using `setUpIntegrationTest($this)` / `tearDownIntegrationTest($this)` (see .claude/testing.md "Flipping a todo"), group `integration-services`, and tag each with `->issue(171)` so HarnessTest's "lists a todo naming each owning ticket" still finds 171.
- The old todo note says `Allow: GET, HEAD`; the plan's contract is `GET, HEAD, OPTIONS`. Assert the plan's value.
- Remove the #171 todos from KnownGapsTest.php. Search Fixture/ for "171" workarounds and remove any.

## Requirements (Test Descriptions)
- [x] `it answers an OPTIONS preflight through the CORS middleware` (OPTIONS /health with Origin + Access-Control-Request-Method → 204 with Access-Control-Allow-Origin)
- [x] `it answers a wrong method with 405 and an Allow header` (POST /health → 405, `Allow: GET, HEAD, OPTIONS`)

## Acceptance Criteria
- All requirements have passing tests (`composer test:integration` with services up)
- HarnessTest still passes

## Implementation Notes
