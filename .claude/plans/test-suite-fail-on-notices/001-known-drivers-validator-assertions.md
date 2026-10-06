# Task 001: KnownDriversValidator Counts Its Assertions

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Rewrite `KnownDriversValidator` to check through `PHPUnit\Framework\Assert` instead of hand-thrown `AssertionFailedError`, so the 38 `KnownDriversValidationTest` cases count assertions and stop being risky, while failing with the same messages.

## Context
- Related files: `packages/testing/src/KnownDrivers/KnownDriversValidator.php`, `packages/testing/tests/KnownDrivers/KnownDriversValidatorTest.php`, `packages/*/tests/KnownDriversValidationTest.php`
- Keep the `SkippedWithMessageException` paths and the `InvalidArgumentException` for a missing `known-drivers.php`.

## Requirements (Test Descriptions)
- [x] `it counts an assertion for every driver it checks against the prefix pattern`
- [x] `it counts assertions for every driver it checks against the skeleton suggest block`
- [x] `it fails skeleton assertion when skeleton has a suggest key but is missing a known driver entry` (same message)
- [x] `it fails skeleton assertion when skeleton has a suggest entry but description does not match` (same message)
- [x] `it asserts every known driver follows marko slash prefix pattern` (same message)

## Acceptance Criteria
- All requirements have passing tests
- The 38 `KnownDriversValidationTest` cases are no longer risky

## Implementation Notes
Validator now uses Assert::assertStringStartsWith/assertArrayHasKey/assertSame with the original messages; @throws updated to ExpectationFailedException. Added two counting tests. Cache KnownDriversValidationTest + testing tests: 14 passed, no risky.
