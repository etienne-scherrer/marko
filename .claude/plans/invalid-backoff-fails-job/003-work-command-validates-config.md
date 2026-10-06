# Task 003: queue:work refuses to start with an invalid queue.backoff

**Status**: completed
**Depends on**: 001
**Retry count**: 0

## Description
`WorkCommand` reads `QueueConfig::backoff()` before starting the worker. An invalid value prints the `invalidBackoff` message, context and suggestion, and returns exit code 1 without calling the worker.

## Context
- Related files: packages/queue/src/Command/WorkCommand.php, packages/queue/tests/Command/WorkCommandTest.php
- Patterns to follow: existing `--queue` validation error in `WorkCommand::execute()`

## Requirements (Test Descriptions)
- [x] `it refuses to start with an invalid queue.backoff config`
- [x] `it does not start the worker when queue.backoff is invalid`
- [x] `it starts the worker when queue.backoff is valid`

## Acceptance Criteria
- All requirements have passing tests
- Existing WorkCommand tests updated for the new constructor parameter

## Implementation Notes
- Inject `QueueConfig` (concrete, readonly; autowired). In tests build it with `FakeConfigRepository` (`['queue.backoff' => ...]`), not a mock. All 12 existing `new WorkCommand(...)` calls in WorkCommandTest need it.
- Call `QueueConfig::backoff()` before writing "Processing jobs from queue...". Catch `QueueException` only (not `Throwable`) and print `getMessage()`, `getContext()` and `getSuggestion()` so the actual reason is shown.
