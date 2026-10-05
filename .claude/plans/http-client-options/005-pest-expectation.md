# Task 005: toHaveSentRequest Pest expectation

**Status**: completed
**Depends on**: 004
**Retry count**: 0

## Description
Add a `toHaveSentRequest(?callable $callback = null)` expectation for `FakeHttpClient`, consistent with `toHaveSent`.

## Context
- Related files: packages/testing/src/Pest/Expectations.php, packages/testing/tests/Unit/Pest/ExpectationsTest.php
- #179 changes how Expectations.php is registered; keep the edit additive (one new `expect()->extend` block)

## Requirements (Test Descriptions)
- [x] `it registers toHaveSentRequest expectation for FakeHttpClient`
- [x] `it matches toHaveSentRequest against a callback`
- [x] `it fails toHaveSentRequest when no request matches`
- [x] `it throws clear error when toHaveSentRequest is used on wrong type`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
