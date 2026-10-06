# Task 004: queue:retry round trip with a fresh container

**Status**: completed
**Depends on**: 001, 002
**Retry count**: 0

## Description
Prove the end-to-end path: a container-aware job fails for the last time under one container, `queue:retry` unserializes the stored payload and pushes it back, and a worker with a fresh container runs it successfully.

## Context
- Related files: packages/queue/tests/Command/RetryCommandTest.php, packages/queue/src/Command/RetryCommand.php
- Depends on 002 because both edit RetryCommandTest.php and 002 changes RetryCommand.php.
- The job must be a NAMED container-aware class (anonymous classes cannot be serialized; the 002 fallback would store a placeholder and retry would refuse it). Assert the stored payload unserializes to that class.
- Use two distinct container instances (fail under the first, succeed under the second) and assert the retried run resolved from the second.

## Requirements (Test Descriptions)
- [ ] `it retries a stored failed container-aware job and runs it with a fresh container`
- [ ] `it stores the failed container-aware job payload without the container`
- [ ] `it resets the retried container-aware job attempts before it runs again`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
