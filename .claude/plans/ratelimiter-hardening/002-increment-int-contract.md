# Task 002: Increment-returns-int contract

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Document on `CacheInterface::increment()` that `get()` on an incremented key returns an `int` for every driver, and lock that in with array/file driver tests.

## Context
- Related files: packages/cache/src/Contracts/CacheInterface.php, packages/cache-array/tests, packages/cache-file/tests

## Requirements (Test Descriptions)
- [x] `it returns an int from get() after increment() (array driver)`
- [x] `it returns an int from get() after increment() (file driver)`
- [x] `it documents that get() returns an int for incremented keys`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
Array and file drivers already stored counters as PHP ints; the new tests are characterization tests that lock the contract in. The docblock requirement is satisfied by the `CacheInterface::increment()` docblock itself (a test asserting docblock text would be pseudo-functionality).
