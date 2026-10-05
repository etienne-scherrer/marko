# Task 002: Interface-aware NoDriverException + container call site

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`NoDriverException::noDriverInstalled(?string $interface = null)` keeps the driver-install message for `QueueInterface`/`FailedJobRepositoryInterface` (and null), but reports "No implementation is bound for {interface}" with a module.php binding suggestion for other queue interfaces. The container passes `$id` only when the factory declares a parameter, staying compatible with the other packages' zero-arg factories.

## Context
- Related files: packages/queue/src/Exceptions/NoDriverException.php, packages/core/src/Container/Container.php, packages/queue/tests/NoDriverExceptionTest.php, packages/core/tests (container tests)
- Core must not import Marko\Queue.

## Requirements (Test Descriptions)
- [x] `it keeps the driver install message when called without an interface`
- [x] `it keeps the driver install message for QueueInterface and FailedJobRepositoryInterface`
- [x] `it names the unbound interface when it is not a driver contract`
- [x] `it suggests binding the interface in module.php when it is not a driver contract`
- [x] `it passes the requested interface to noDriverInstalled factories that accept a parameter`
- [x] `it still calls zero-argument noDriverInstalled factories`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
