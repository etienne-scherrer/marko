# Task 007: Enable the failOn* Flags and Document the Rule

**Status**: completed
**Depends on**: 001, 002, 003, 004, 005, 006
**Retry count**: 0

## Description
Set `failOnDeprecation`, `failOnNotice`, `failOnPhpunitDeprecation`, `failOnPhpunitNotice`, `failOnRisky` and `failOnWarning` in `phpunit.xml`, guard them with a test, and note the rule in `.claude/testing.md`.

## Context
- Leave `failOnSkipped` off.
- Guard test location: `tests/PhpunitConfigTest.php` (Monorepo suite). Load the root `phpunit.xml` with SimpleXML and assert each of the six attributes is `"true"`, and that `failOnSkipped` is absent or `"false"`.
- Run the full `composer test` (not one package at a time) after enabling the flags. Fix any notice/deprecation/risky/warning that 003-006 missed at its source, not with `#[AllowMockObjectsWithoutExpectations]`.
- Manually verify (not committed) that a risky test and an `E_USER_DEPRECATED` make `composer test` exit non-zero.

## Requirements (Test Descriptions)
- [x] `it fails the run on deprecations, notices, phpunit deprecations, phpunit notices, risky tests and warnings`
- [x] `it does not fail the run on skipped tests`
- [x] `composer test` summary shows 0 notices, 0 deprecations, 0 risky, 0 warnings

## Acceptance Criteria
- `.claude/testing.md` states: stubs via `createStub()`, every test asserts, the suite fails on notices/deprecations/risky/warnings

## Implementation Notes
Added the six failOn* attributes to phpunit.xml and tests/PhpunitConfigTest.php (red before, green after). Full `composer test`: 0 notices, 0 deprecations, 0 risky, 0 warnings, exit 0. Manually checked (not committed): a test with no assertion and a test calling `trigger_error(..., E_USER_DEPRECATED)` each made the parallel run exit 1. `.claude/testing.md` gained principle 6 (clean runs); docs pages for testing (KnownDriversValidator counts assertions) and core (notWritable reason) updated.
