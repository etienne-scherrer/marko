# Task 001: Add AppEnvironment::isTesting()

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add a `TESTING_NAMES` constant (`testing`, `test`) and `isTesting()` to core's `AppEnvironment`, so destructive database commands can treat test suites as disposable.

## Context
- Related files: packages/core/src/Environment/AppEnvironment.php, packages/core/tests/Unit/Environment/AppEnvironmentTest.php
- Patterns to follow: `isDevelopment()` / `DEVELOPMENT_NAMES`

## Requirements (Test Descriptions)
- [x] `it treats testing and test as testing case-insensitively`
- [x] `it does not treat production, development or staging names as testing`
- [x] `it does not treat an unset environment as testing`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
