# Plan: Test Suite Fails on Notices, Deprecations, Risky Tests and Warnings

## Created
2026-10-06

## Status
completed

## Objective
Clear every notice, risky test and warning from `composer test` (and the integration suites), then make `phpunit.xml` fail the run on any new one.

## Related Issues
Closes #356

## Discovery Notes
- Baseline on current `develop`: `1 warning, 38 risky, 170 notices, 318 skipped` (the ticket counted 166 notices; a few more tests landed since).
- All notices are the PHPUnit 12 "No expectations were configured for the mock object" notice from `createMock()` doubles that never get `expects()`. The repo already uses `createStub()` in many places.
- All 38 risky tests come from `KnownDriversValidator` throwing `AssertionFailedError` by hand, so a passing check performs zero assertions.
- The one warning is the unguarded `file_put_contents()` in `DiscoveryCache::write()`; `DiscoveryCacheException::notWritable()` drops the OS reason.
- The `integration-services` CI jobs use the same `phpunit.xml`, so the integration suites must also be clean under the new flags.

## Scope

### In Scope
- `createMock()` → `createStub()` where no expectation is configured (no blanket `#[AllowMockObjectsWithoutExpectations]`)
- `KnownDriversValidator` rewritten on `PHPUnit\Framework\Assert`, same failure messages, skip paths kept
- `DiscoveryCache::write()` folds the OS reason into `DiscoveryCacheException::notWritable()` with no PHP warning
- Six `failOn*` attributes in `phpunit.xml`
- `.claude/testing.md` rule
- Local run of the integration-services group and MySQL/MariaDB/PgSQL driver suites with the new flags, fixing anything surfaced

### Out of Scope
- `failOnSkipped` (integration tests skip without services)
- Any change to production behaviour other than the discovery cache write error

## Success Criteria
- [ ] `composer test` summary shows 0 notices, 0 deprecations, 0 risky, 0 warnings
- [ ] Integration suites clean with the new flags
- [ ] A deliberately risky test or `E_USER_DEPRECATED` makes `composer test` exit non-zero (manual check)
- [ ] All tests passing
- [ ] Code follows project standards (`composer ci` green)

## Task Overview
| Task | Description | Depends On | Status |
|------|-------------|------------|--------|
| 001 | KnownDriversValidator counts its assertions | - | completed |
| 002 | DiscoveryCache write failure carries the OS reason | - | completed |
| 003 | Stubs instead of mocks: routing, core | - | completed |
| 004 | Stubs instead of mocks: database, database-mysql | - | completed |
| 005 | Stubs instead of mocks: notification, notification-database | - | completed |
| 006 | Stubs instead of mocks: view-latte, view-twig, mail, mail-log, queue-database, authentication, admin-auth | - | completed |
| 007 | Enable the failOn* flags and document the rule | 001, 002, 003, 004, 005, 006 | completed |
| 008 | Integration suites clean under the new flags | 007 | completed |

## Architecture Notes
- A `createStub()` double still supports `method()->willReturn()`; only `expects()` requires a mock. Helper signatures typed `MockObject&X` must become `Stub&X` (or plain `X`) when they return stubs.
- Where the test's intent is clearly to verify an interaction, add `expects()` instead of switching to a stub.
- Doubles created once in `beforeEach`/`setUp` that get `expects()` in only some tests: use a stub in the shared setup and create a mock locally in the tests that set expectations. Do not keep a shared mock, and do not add `#[AllowMockObjectsWithoutExpectations]`.
- Doubles are often built inside Pest helper functions via `test()->createMock()`. Change those helpers too, along with their return types.
- `failOn*` holds under `--parallel`: paratest computes its exit code through PHPUnit's `ShellExitCodeCalculator`.
- Nightly `composer test:all` (roadrunner E2E, mail-smtp, Redis, `IntegrationVerificationTest`) uses the same config. Task 008 covers all of them except the vendor-deleting `IntegrationVerificationTest`.
- `Assert::assertArrayHasKey()`/`assertSame()` accept a custom message; PHPUnit appends its own detail, so the existing message text is still contained in the failure.

## Risks & Mitigations
- Many test files touched → conflicts with open PRs: keep edits mechanical (`createMock` → `createStub`, type hints).
- Integration suites may surface new notices only visible with services: run them locally in docker under an isolated compose project and ports.
