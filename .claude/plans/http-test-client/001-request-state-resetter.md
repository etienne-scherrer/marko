# Task 001: RequestStateResetter in core

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Extract the "reset every resolved ResettableInterface, sorted by binding id" loop from `WorkerRequestHandler` into `Marko\Core\RequestStateResetter` so the RoadRunner worker and the test client share one implementation.

## Context
- Related files: packages/roadrunner/src/Worker/WorkerRequestHandler.php, packages/core/src/Container/ContainerInterface.php
- Patterns to follow: existing WorkerRequestHandler reset tests

## Requirements (Test Descriptions)
- [x] `it resets every resolved ResettableInterface instance`
- [x] `it does not touch resolved instances that are not resettable`
- [x] `it resets in ascending binding id order`
- [x] `it lets a reset failure propagate`
- [x] WorkerRequestHandler reset tests still pass using the extracted class

## Acceptance Criteria
- All requirements have passing tests
- PHPStan level 6 clean (core and roadrunner are analysed)

## Implementation Notes
- Contract: `class RequestStateResetter { __construct(private ContainerInterface $container); public function reset(): void }` (not final, not readonly-blanket).
- Do NOT change `WorkerRequestHandler`'s constructor (worker.php and ~19 test call sites construct it). Build the resetter internally from the existing `$container`, e.g. `(new RequestStateResetter($this->container))->reset()`, keeping the reset-before-request placement and the catch-all behaviour.
