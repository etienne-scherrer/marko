# Task 001: Backoff on Job/JobInterface, queue.backoff config, QueueConfig::backoff()

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Add a per-job `backoff` property and a global `queue.backoff` config key so retry delays can be tuned.

## Context
- Related files: packages/queue/src/Job.php, JobInterface.php, QueueConfig.php, config/queue.php, Exceptions/QueueException.php
- Patterns to follow: `$maxAttempts` property hook on `JobInterface`; `QueueConfig` getters

## Requirements (Test Descriptions)
- [x] `it defaults backoff to null so the queue.backoff config applies`
- [x] `it lets a job subclass declare an int or list backoff`
- [x] `it declares a backoff get hook on JobInterface`
- [x] `it returns the configured int or list backoff`
- [x] `it returns null for backoff when the key is null or absent`
- [x] `it throws QueueException when queue.backoff is not an int, list or null`
- [x] `it ships a null backoff in config/queue.php`

## Contract (fixed by devil's advocate)
- `Job` must declare exactly `public protected(set) array|int|null $backoff = null;` — `tests/JobTest.php` fixtures already redeclare this type and class properties are invariant.
- `JobInterface`: `/** @var int|list<int>|null */ public array|int|null $backoff { get; }`
- `QueueConfig::backoff(): array|int|null` — return null when `!$this->config->has('queue.backoff')` (ConfigMerger unsets null overrides; `get()` throws on missing keys). Otherwise `get()` and throw `QueueException` if the value is not int, array or null. Type check only; value-shape validation (negative, empty, non-int entries, non-list) lives in `Worker::backoffFor()` (task 002) so job and config values share one validator.

## Acceptance Criteria
- All requirements have passing tests
- Code follows code standards

## Implementation Notes
(Left blank - filled in by programmer during implementation)
