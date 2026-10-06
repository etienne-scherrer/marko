# Task 004: RefreshDatabase

**Status**: completed
**Depends on**: 001, 003
**Retry count**: 0

## Description
Per-test transaction on the shared TransactionInterface: beginTransaction() before the test, rollback() after (rolling back any levels code under test left open). runAfterCommitCallbacks() runs the queued after-commit callbacks.

## Requirements (Test Descriptions)
- [ ] `it throws when a test transaction is already open`
- [ ] `it rolls back every open level`
- [ ] `it throws when runAfterCommitCallbacks is called without a test transaction`
- [ ] `it throws when the connection cannot run pending after-commit callbacks`
- [ ] `it resets the connection and rethrows when the rollback fails so the next test can begin`
- [ ] `it runs after-rollback callbacks registered by code under test when the test transaction rolls back`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- API (task 007 and the docs build against this): `new RefreshDatabase(TestDatabase $database)`, `begin(): void`, `rollback(): void`, `runAfterCommitCallbacks(): void`. Usable from `setUp()`/`tearDown()` or Pest `beforeEach`/`afterEach`.
- A failed teardown rollback (dropped connection, server error) would otherwise leave level > 0, and every later test would fail with "a test transaction is already open". On failure, call `reset()` on the connection when it is `ResettableInterface`, then rethrow.
