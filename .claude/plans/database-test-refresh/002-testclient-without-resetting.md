# Task 002: TestClient can leave services unreset

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
TestClient resets every resolved ResettableInterface before each request, and the database connections roll back open transactions on reset. Let callers exclude specific instances so a RefreshDatabase test transaction survives HTTP requests.

## Context
- Related files: packages/core/src/RequestStateResetter.php, packages/testing/src/Http/TestClient.php

## Requirements (Test Descriptions)
- [ ] `it skips the instances passed as exceptions`
- [ ] `it does not reset services passed to withoutResetting between requests`
- [ ] `it still resets every other resettable service`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
