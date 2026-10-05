# Task 003: Nullable Job maxAttempts, Worker config fallback, bind WorkerInterface

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
Bind `WorkerInterface => Worker` in the queue module. Make `Job::$maxAttempts` a `?int` defaulting to null (JobInterface `?int $maxAttempts { get; }`); the worker uses `$job->maxAttempts ?? $config->maxAttempts()`.

## Context
- Related files: packages/queue/module.php, packages/queue/src/Job.php, JobInterface.php, Worker.php, tests (JobTest, JobInterfaceTest, WorkerTest, PackageScaffoldingTest), packages/queue-rabbitmq/tests/RabbitmqQueueTest.php (uses $job->maxAttempts)

## Requirements (Test Descriptions)
- [x] `it binds WorkerInterface to Worker in module.php`
- [x] `it defaults Job maxAttempts to null so the config default applies`
- [x] `it releases a failing job while attempts are below queue.max_attempts when the job sets no maxAttempts`
- [x] `it fails a job once attempts reach queue.max_attempts when the job sets no maxAttempts`
- [x] `it prefers the job maxAttempts over queue.max_attempts`

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
