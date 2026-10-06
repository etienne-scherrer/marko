# Task 003: FakeSession supports the armed state

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`FakeSession` in marko/testing implements `arm()` and `isAvailable()` and starts lazily on first access when armed, so tests of middleware and guards can assert the lazy path.

## Context
- Related files: packages/testing/src/Fake/FakeSession.php, packages/testing/tests/Unit/Fake/FakeSessionTest.php

## Requirements (Test Descriptions)
- [x] `it is not available until armed or started`
- [x] `it records that it was armed`
- [x] `it starts lazily on first access once armed`
- [x] `it stays unstarted when accessed without being armed`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes (from review)
- Task 001 already added minimal compiling `arm()`/`isAvailable()` to FakeSession. This task adds the public `armed` state (e.g. `public private(set) bool $armed`), lazy start on accessors when armed, disarm in save/discard/destroy, and the tests.
- Keep FakeSession lenient: accessors on an unarmed, unstarted FakeSession still work without throwing (existing consumers rely on this), but must not flip `started`.
- Accessors that lazily start must set `startedData` like `start()` does, so `isModified()` stays correct.
