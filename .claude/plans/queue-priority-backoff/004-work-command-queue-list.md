# Task 004: queue:work parses comma-separated queue list

**Status**: completed
**Depends on**: 003
**Retry count**: 0

## Description
`queue:work --queue=high,default,low` passes `['high', 'default', 'low']` to the worker; no `--queue` passes null.

## Context
- Related files: packages/queue/src/Command/WorkCommand.php, tests/Command/WorkCommandTest.php
- Patterns to follow: existing `Input::getOption()` usage

## Requirements (Test Descriptions)
- [x] `it parses --queue=a,b --sleep=1 into a queue list and sleep`
- [x] `it passes a single queue as a one-item list`
- [x] `it trims whitespace around queue names`
- [x] `it passes null when no queue option is given`
- [x] `it drops empty segments such as --queue=high,,low`
- [x] `it fails loudly when --queue contains no queue names`

## Notes (devil's advocate)
- Task 003 already switched `WorkCommand` to `queues: [$queue]` and updated the test stubs; this task replaces that with split/trim/filter (`array_values(array_filter(array_map('trim', explode(',', $queue)), fn ($q) => $q !== ''))`).
- `--queue=` / `--queue=,` produce an empty list: write an error and return non-zero (or let the worker's `QueueException` surface) — never silently fall back to the default queue.

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
(Left blank - filled in by programmer during implementation)
