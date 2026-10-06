# Task 003: RecordedRequest, stray-request exception, testing composer dependency

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
Add `marko/http` to `marko/testing`'s `require`, create the readonly `RecordedRequest` value object, and add an `AssertionFailedException::strayRequest()` factory.

## Context
- Related files: packages/testing/composer.json, packages/testing/src/Fake/Http/RecordedRequest.php (new), packages/testing/src/Exceptions/AssertionFailedException.php, packages/testing/tests/PackageStructureTest.php
- Keep composer.json edit to a single added line (parallel tickets #179/#182 touch the same file)

## Requirements (Test Descriptions)
- [x] `it requires marko/http as a dependency`
- [x] `it exposes method, url and options`
- [x] `it returns a header value case-insensitively or null when absent`
- [x] `it returns the json option from json()`
- [x] `it returns the raw body, encoded json, or encoded form params from body()`
- [x] `it creates a stray request exception naming the method and url`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
