# Task 003: Harness reset delegates to RequestStateResetter

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`InProcessRequestHarness::reset()` runs its own `resolvedInstances()` loop, and that loop has already drifted from the worker's `RequestStateResetter`: it does no `ksort()`. Delegate to `RequestStateResetter` so the harness resets exactly the way the worker does. Keep the opt-in, call-it-yourself semantics.

## Context
- Related files: `packages/roadrunner/tests/Support/InProcessRequestHarness.php`, `packages/roadrunner/tests/Support/InProcessRequestHarnessTest.php`, `packages/core/src/RequestStateResetter.php`, `packages/docs-markdown/docs/packages/roadrunner-state-leaks.md`
- Patterns to follow: `WorkerRequestHandler` uses `RequestStateResetter`

## Requirements (Test Descriptions)
- [x] `it resets in the same sorted binding order as the worker's RequestStateResetter`
- [x] `it exposes a reset hook that runs between requests` (existing, still passes)
- [x] Harness `reset()` no longer iterates `resolvedInstances()` itself
- [x] `roadrunner-state-leaks.md` says the harness resets through `RequestStateResetter`

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
The test registers probe resettables in reverse alphabetical order under unique ids and calls `harness->reset()`. It then asserts that the probes ran in ascending id order and that a direct `RequestStateResetter` run produces the identical sequence. Before the change the test failed, with the probes resetting in insertion order.
