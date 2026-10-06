# Task 001: BackoffValidator and full queue.backoff validation in QueueConfig

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Extract the backoff shape validation from `Worker::resolveBackoff()` into a `BackoffValidator` class, and make `QueueConfig::backoff()` use it so an invalid `queue.backoff` is rejected by the config accessor itself (the hook `queue:work` uses to fail fast).

## Context
- Related files: packages/queue/src/Worker.php, packages/queue/src/QueueConfig.php, packages/queue/src/Exceptions/QueueException.php
- Patterns to follow: existing `QueueException::invalidBackoff($source, $reason)` messages

## Requirements (Test Descriptions)
- [x] `it accepts a non-negative int, a non-empty list of non-negative ints`
- [x] `it rejects a negative int, an empty list, a keyed array, and non-int or negative list entries`
- [x] `it rejects a value that is not an int or array`
- [x] `it throws QueueException for a queue.backoff list with a non-int entry` (QueueConfig)
- [x] `it throws QueueException for a negative queue.backoff` (QueueConfig)

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
`BackoffValidator::validate(mixed $backoff, string $source): array|int` returns the typed value. `QueueConfig` takes it as a defaulted constructor param.
