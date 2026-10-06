# Task 002: queue: Worker records failedAt from the clock

**Status**: completed
**Depends on**: none
**Retry count**: 0

## Description
`Worker` takes a required `ClockInterface` and stamps `FailedJob::failedAt` from it.

## Context
- Related files: packages/queue/src/Worker.php, packages/queue/composer.json, all `new Worker(` call sites (queue, notification, webhook tests)

## Requirements (Test Descriptions)
- [ ] `it records the failed job with the time from the injected clock`
- [ ] existing Worker tests pass with a FakeClock injected

## Acceptance Criteria
- All requirements have passing tests

## Implementation Notes
- Place `ClockInterface $clock` after `ContainerInterface $container` and BEFORE `BackoffValidator $backoffValidator = new BackoffValidator()` (required-after-optional is deprecated).
- `module.php` binds `WorkerInterface => Worker::class` (autowired), so no binding change is needed.
- Call sites in other groups' packages (`notification/tests/Unit/SerializableNotificationJobTest.php`, `webhook/tests/Jobs/SerializableWebhookJobTest.php`): add only a named `clock: new FakeClock()` argument; touch nothing else, so merge conflicts with the parallel notification group stay small.
